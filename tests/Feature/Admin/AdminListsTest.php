<?php

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;

/**
 * The admin lists: search, a status chip with its real count, a sort from a
 * fixed list. Anything else in the address is no filter at all.
 */
function listAdmin(): User
{
    $user = User::factory()->create(['name' => 'Operator']);
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

function listOrder(string $name, float $total, string $status = 'pending', ?string $phone = null): Order
{
    $customer = User::factory()->create(['name' => $name]);
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Jordan', 'city' => 'Amman', 'street' => 'Street 1', 'phone' => $phone]);

    return Order::create(['user_id' => $customer->id, 'shipping_address_id' => $address->id, 'total_price' => $total, 'status' => $status, 'payment_status' => 'pending']);
}

it('finds an order by its number however it is typed, by customer, and by phone', function () {
    $admin = listAdmin();
    $rana = listOrder('Rana Haddad', 100, 'pending', '0791234567');
    $omar = listOrder('Omar Saleh', 200);

    foreach (['#'.str_pad($rana->id, 6, '0', STR_PAD_LEFT), (string) $rana->id, 'Rana', '0791234'] as $q) {
        $ids = $this->actingAs($admin)->get(route('admin.orders.index', ['q' => $q]))->viewData('orders')->pluck('id')->all();
        expect($ids)->toBe([$rana->id], "searching {$q}");
    }

    expect($this->actingAs($admin)->get(route('admin.orders.index', ['q' => 'Omar']))->viewData('orders')->pluck('id')->all())->toBe([$omar->id]);
});

it('counts each status under the current search, and filters to one', function () {
    $admin = listAdmin();
    listOrder('A', 10, 'pending');
    listOrder('B', 10, 'pending');
    listOrder('C', 10, 'shipped');

    $response = $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'shipped']));
    $chips = collect($response->viewData('chips'))->keyBy(fn ($c) => $c['value'] ?? 'all');

    expect($chips['all']['count'])->toBe(3)
        ->and($chips['pending']['count'])->toBe(2)
        ->and($chips['shipped']['active'])->toBeTrue()
        ->and($response->viewData('orders')->total())->toBe(1);
});

it('sorts orders by total, and ignores a sort or status it does not offer', function () {
    $admin = listAdmin();
    $small = listOrder('A', 10);
    $large = listOrder('B', 900);

    expect($this->actingAs($admin)->get(route('admin.orders.index', ['sort' => 'total_high']))->viewData('orders')->first()->id)->toBe($large->id)
        ->and($this->actingAs($admin)->get(route('admin.orders.index', ['sort' => 'total_low']))->viewData('orders')->first()->id)->toBe($small->id);

    $odd = $this->actingAs($admin)->get(route('admin.orders.index', ['sort' => 'id; drop table orders', 'status' => 'lost']))->assertOk();
    expect($odd->viewData('sort'))->toBe('newest')->and($odd->viewData('orders')->total())->toBe(2);
});

it('finds a product by its Arabic name, within a category, and sorts by price', function () {
    $admin = listAdmin();
    $rooms = Category::factory()->create();
    $other = Category::factory()->create();

    $sofa = Product::factory()->create(['category_id' => $rooms->id, 'name' => 'Linen sofa', 'name_ar' => 'كنبة كتان', 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $sofa->id, 'price' => 900]);
    $stool = Product::factory()->create(['category_id' => $rooms->id, 'name' => 'Stool', 'status' => 'draft']);
    ProductVariant::factory()->create(['product_id' => $stool->id, 'price' => 40]);
    $lamp = Product::factory()->create(['category_id' => $other->id, 'name' => 'Lamp', 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $lamp->id, 'price' => 70]);

    expect($this->actingAs($admin)->get(route('admin.products.index', ['q' => 'كتان']))->viewData('products')->pluck('id')->all())->toBe([$sofa->id]);

    $inRoom = $this->actingAs($admin)->get(route('admin.products.index', ['category' => $rooms->id, 'sort' => 'price_low']));
    expect($inRoom->viewData('products')->pluck('id')->all())->toBe([$stool->id, $sofa->id]);

    $drafts = $this->actingAs($admin)->get(route('admin.products.index', ['status' => 'draft']));
    expect($drafts->viewData('products')->pluck('id')->all())->toBe([$stool->id]);
});

it('filters people by role and finds them by e-mail', function () {
    $admin = listAdmin();
    $customer = User::factory()->create(['email' => 'nour@example.test']);
    $customer->roles()->attach(Role::firstOrCreate(['name' => 'customer']));

    $admins = $this->actingAs($admin)->get(route('admin.users.index', ['role' => 'admin']))->viewData('users');
    expect($admins->pluck('id')->all())->toBe([$admin->id]);

    expect($this->actingAs($admin)->get(route('admin.users.index', ['q' => 'nour@']))->viewData('users')->pluck('id')->all())->toBe([$customer->id]);
});

it('shows the reviews waiting, and finds one by its words', function () {
    $admin = listAdmin();
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id]);
    $waiting = Review::create(['product_id' => $product->id, 'user_id' => User::factory()->create()->id, 'rating' => 4, 'comment' => 'Sturdy and true to size.', 'status' => 'pending']);
    Review::create(['product_id' => $product->id, 'user_id' => User::factory()->create()->id, 'rating' => 5, 'comment' => 'Lovely.', 'status' => 'approved']);

    expect($this->actingAs($admin)->get(route('admin.reviews.index', ['status' => 'pending']))->viewData('reviews')->pluck('id')->all())->toBe([$waiting->id])
        ->and($this->actingAs($admin)->get(route('admin.reviews.index', ['q' => 'sturdy']))->viewData('reviews')->pluck('id')->all())->toBe([$waiting->id]);
});

it('says when nothing matches, rather than that the list is empty', function () {
    $admin = listAdmin();
    listOrder('A', 10);

    $this->actingAs($admin)->get(route('admin.orders.index', ['q' => 'nobody-by-this-name']))
        ->assertSee(__('admin.list.no_match'))
        ->assertDontSee(__('admin.orders.empty'));
});
