<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Support\AdminAlerts;
use Illuminate\Support\Facades\Mail;

/**
 * The panel's notices: written once per administrator when something
 * happens, read in the reader's language, and only ever by their owner.
 */
function alertAdmin(string $name = 'Operator'): User
{
    $user = User::factory()->create(['name' => $name]);
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

function alertCustomer(): User
{
    $user = User::factory()->create(['name' => 'Rana']);
    $user->roles()->attach(Role::firstOrCreate(['name' => 'customer']));

    return $user;
}

/** A customer with one piece in the bag, at the stock given. */
function bagWith(User $customer, int $stock, int $quantity = 1): ProductVariant
{
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'status' => 'active', 'name' => 'Oak stool']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => $stock, 'price' => 100]);
    $cart = Cart::create(['user_id' => $customer->id]);
    $cart->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => $quantity, 'price' => 100]);

    return $variant;
}

function checkout(User $customer)
{
    return test()->actingAs($customer)->post(route('checkout.process'), [
        'country' => 'Jordan', 'city' => 'Amman', 'street' => 'Rainbow Street 12', 'phone' => '0791234567',
    ]);
}

it('tells every administrator, once each, when an order is placed', function () {
    Mail::fake();
    $a = alertAdmin('A');
    $b = alertAdmin('B');
    $customer = alertCustomer();
    bagWith($customer, stock: 20);

    checkout($customer);

    $order = Order::first();
    expect($order)->not->toBeNull();
    foreach ([$a, $b] as $admin) {
        $alerts = $admin->alerts()->get();
        expect($alerts)->toHaveCount(1)
            ->and($alerts[0]->type)->toBe('order_placed')
            ->and($alerts[0]->text())->toContain($order->number())->toContain('Rana')
            ->and($alerts[0]->url())->toBe(route('admin.orders.show', $order->id));
    }

    // The customer is not an administrator, and hears nothing.
    expect($customer->alerts()->count())->toBe(0);
});

it('says a colour is running low when a sale takes it to the line, and not again below it', function () {
    Mail::fake();
    $admin = alertAdmin();

    $first = alertCustomer();
    $variant = bagWith($first, stock: 5, quantity: 2);   // 5 -> 3: crosses the line
    checkout($first);

    expect($admin->alerts()->where('type', 'low_stock')->count())->toBe(1)
        ->and($variant->fresh()->stock)->toBe(3);

    $second = alertCustomer();
    $cart = Cart::create(['user_id' => $second->id]);
    $cart->items()->create(['product_id' => $variant->product_id, 'product_variant_id' => $variant->id, 'quantity' => 1, 'price' => 100]);
    checkout($second);                                     // 3 -> 2: already low

    expect($admin->alerts()->where('type', 'low_stock')->count())->toBe(1);
});

it('tells administrators of a review, a message and a new customer', function () {
    $admin = alertAdmin();
    $customer = alertCustomer();
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'status' => 'active', 'name' => 'Linen sofa']);

    $this->actingAs($customer)->post(route('reviews.store', $product), ['rating' => 4, 'comment' => 'Sturdy.']);
    auth()->logout();

    Mail::fake();
    $this->post(route('contact.store'), ['name' => 'Nour', 'email' => 'nour@example.test', 'subject' => 'Delivery to Irbid', 'message' => 'Do you deliver?']);

    $this->post(route('register'), ['name' => 'Omar Saleh', 'email' => 'omar@example.test', 'password' => 'password-123', 'password_confirmation' => 'password-123']);

    $texts = $admin->alerts()->get()->mapWithKeys(fn ($n) => [$n->type => $n->text()]);

    expect($texts['review_pending'])->toContain('Linen sofa')->toContain('4/5')
        ->and($texts['contact_received'])->toContain('Nour')->toContain('Delivery to Irbid')
        ->and($texts['customer_registered'])->toContain('Omar Saleh');
});

it('writes the sentence in the language of whoever reads it', function () {
    $admin = alertAdmin();
    AdminAlerts::notify('customer_registered', ['user_id' => 1, 'name' => 'Omar']);
    $alert = $admin->alerts()->first();

    app()->setLocale('ar');
    expect($alert->text())->toBe(__('admin.alerts.customer_registered', ['name' => 'Omar'], 'ar'));
    app()->setLocale('en');
    expect($alert->text())->toBe('Omar opened an account');
});

it('feeds the bell only what arrived since it last asked, with the true unread count', function () {
    $admin = alertAdmin();
    AdminAlerts::notify('customer_registered', ['user_id' => 1, 'name' => 'First']);
    $first = $admin->alerts()->first()->id;
    AdminAlerts::notify('customer_registered', ['user_id' => 2, 'name' => 'Second']);

    $all = $this->actingAs($admin)->getJson(route('admin.notifications.feed'))->assertOk()->json();
    expect($all['unread'])->toBe(2)->and($all['items'])->toHaveCount(2)->and($all['items'][0]['text'])->toContain('Second');

    $since = $this->actingAs($admin)->getJson(route('admin.notifications.feed', ['after' => $first]))->json();
    expect($since['items'])->toHaveCount(1)
        ->and($since['items'][0]['text'])->toContain('Second')
        ->and($since['latest'])->toBe($first + 1);
});

it('marks one read and goes to it, or marks them all', function () {
    $admin = alertAdmin();
    AdminAlerts::notify('contact_received', ['contact_id' => 1, 'name' => 'Nour', 'subject' => 'Hi']);
    AdminAlerts::notify('customer_registered', ['user_id' => 1, 'name' => 'Omar']);
    $one = $admin->alerts()->where('type', 'contact_received')->first();

    $this->actingAs($admin)->post(route('admin.notifications.read', $one))->assertRedirect(route('admin.contacts.index'));
    expect($one->fresh()->read_at)->not->toBeNull()
        ->and($admin->alerts()->whereNull('read_at')->count())->toBe(1);

    $this->actingAs($admin)->postJson(route('admin.notifications.read-all'))->assertJson(['unread' => 0]);
    expect($admin->alerts()->whereNull('read_at')->count())->toBe(0);
});

it('never lets one administrator read or see another\'s notices, nor a customer any', function () {
    $mine = alertAdmin('Mine');
    $theirs = alertAdmin('Theirs');
    AdminAlerts::notify('customer_registered', ['user_id' => 1, 'name' => 'Omar']);
    $theirAlert = $theirs->alerts()->first();

    $this->actingAs($mine)->post(route('admin.notifications.read', $theirAlert))->assertNotFound();
    expect($theirAlert->fresh()->read_at)->toBeNull();

    $this->actingAs($mine)->postJson(route('admin.notifications.read-all'));
    expect($theirs->alerts()->whereNull('read_at')->count())->toBe(1);

    $this->actingAs(alertCustomer())->getJson(route('admin.notifications.feed'))->assertStatus(302);
});

it('lists the notices on their own page, unread first to the eye', function () {
    $admin = alertAdmin();
    AdminAlerts::notify('customer_registered', ['user_id' => 1, 'name' => 'Omar']);

    $this->actingAs($admin)->get(route('admin.notifications.index'))
        ->assertOk()
        ->assertSee('Omar opened an account')
        ->assertSee(__('admin.alerts_page.mark_all'));

    $this->actingAs($admin)->get(route('admin.notifications.index', ['show' => 'unread']))->assertOk();
});
