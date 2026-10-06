<?php

use App\Mail\OrderStatusChanged;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Checkout used to make two claims it could not support: it wrote the phone
 * number into the street field, and it marked card orders `paid` with no
 * gateway behind the form. Both are pinned here.
 */
function buyer(): User
{
    return User::factory()->create();
}

function cartWithOnePiece(User $user): Cart
{
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 250.00,
    ]);

    $cart = Cart::create(['user_id' => $user->id]);
    $cart->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 2,
        'price' => 250.00,
    ]);

    return $cart->fresh('items');
}

$order = [
    'country' => 'Jordan',
    'city' => 'Amman',
    'street' => 'Rainbow Street 12',
    'phone' => '0791234567',
];

it('stores the phone in its own column, not appended to the street', function () use ($order) {
    // It used to write "Rainbow Street 12 (Phone: 0791234567)" into `street`,
    // with a comment admitting the reason was avoiding a migration.
    $user = buyer();
    cartWithOnePiece($user);

    $this->actingAs($user)->post(route('checkout.process'), $order)->assertRedirect();

    $address = $user->addresses()->latest('id')->first();

    expect($address->street)->toBe('Rainbow Street 12')
        ->and($address->street)->not->toContain('Phone')
        ->and($address->phone)->toBe('0791234567');
});

it('never marks a new order paid', function () use ($order) {
    // The old form validated a card number, an expiry and a CVC, discarded all
    // three, and set payment_status to `paid`. An operator then saw "paid" in
    // the panel and shipped goods nobody had paid for.
    $user = buyer();
    cartWithOnePiece($user);

    $this->actingAs($user)->post(route('checkout.process'), $order)->assertRedirect();

    $placed = $user->orders()->latest('id')->first();

    expect($placed->payment_status)->toBe('pending')
        ->and($placed->status)->toBe('pending');
});

it('refuses an order with no phone number', function () use ($order) {
    $user = buyer();
    cartWithOnePiece($user);

    $this->actingAs($user)
        ->post(route('checkout.process'), [...$order, 'phone' => ''])
        ->assertSessionHasErrors('phone');

    expect($user->orders()->count())->toBe(0);
});

it('totals the order from the cart and empties it', function () use ($order) {
    $user = buyer();
    cartWithOnePiece($user);

    $this->actingAs($user)->post(route('checkout.process'), $order)->assertRedirect();

    $placed = $user->orders()->latest('id')->first();

    expect((float) $placed->total_price)->toBe(500.00)
        ->and($placed->items)->toHaveCount(1)
        ->and($placed->items->first()->quantity)->toBe(2)
        ->and(Cart::where('user_id', $user->id)->exists())->toBeFalse();
});

it('keeps a name snapshot so a later rename cannot rewrite history', function () use ($order) {
    $user = buyer();
    $cart = cartWithOnePiece($user);
    $product = $cart->items->first()->product;

    $this->actingAs($user)->post(route('checkout.process'), $order)->assertRedirect();

    $product->update(['name' => 'Renamed after the sale']);

    $item = $user->orders()->latest('id')->first()->items->first();

    expect($item->product_name_snapshot)->not->toBe('Renamed after the sale');
});

it('turns a guest away from checkout', function () {
    $this->get(route('checkout'))->assertRedirect(route('login'));
});

it('checks out a product that has no variant', function () use ($order) {
    // CartController writes a null product_variant_id for any product without
    // one, while the column was NOT NULL — so buying such a piece returned a
    // 500 rather than an order.
    $user = buyer();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);

    $cart = Cart::create(['user_id' => $user->id]);
    $cart->items()->create([
        'product_id' => $product->id,
        'product_variant_id' => null,
        'quantity' => 1,
        'price' => 99.00,
    ]);

    $this->actingAs($user)->post(route('checkout.process'), $order)->assertRedirect();

    $placed = $user->orders()->latest('id')->first();

    expect($placed)->not->toBeNull()
        ->and($placed->items->first()->product_variant_id)->toBeNull();
});

it('keeps order lines when the product is deleted from the catalogue', function () use ($order) {
    // product_id was cascadeOnDelete, so retiring a discontinued piece silently
    // deleted it from every past order: the total stayed and the lines went.
    $user = buyer();
    $cart = cartWithOnePiece($user);
    $product = $cart->items->first()->product;

    $this->actingAs($user)->post(route('checkout.process'), $order)->assertRedirect();

    $placed = $user->orders()->latest('id')->first();
    $product->delete();

    expect($placed->fresh()->items)->toHaveCount(1)
        ->and($placed->fresh()->items->first()->product_id)->toBeNull()
        ->and($placed->fresh()->items->first()->product_name_snapshot)->not->toBeEmpty();
});

it('keeps the customer\'s note and language on the order', function () use ($order) {
    $user = buyer();
    cartWithOnePiece($user);

    $this->actingAs($user)
        ->withSession(['locale' => 'ar'])
        ->post(route('checkout.process'), $order + ['notes' => 'الطابق الثالث، لا يوجد مصعد.'])
        ->assertRedirect();

    $placed = Order::sole();
    expect($placed->notes)->toBe('الطابق الثالث، لا يوجد مصعد.')
        ->and($placed->locale)->toBe('ar');
});

it('writes a later status email in the language the customer shopped in', function () {
    Mail::fake();
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));
    $customer = buyer();
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Jordan', 'city' => 'Amman', 'street' => 'X', 'phone' => '1']);
    $placed = Order::create([
        'user_id' => $customer->id, 'shipping_address_id' => $address->id, 'total_price' => 10,
        'status' => 'processing', 'payment_status' => 'pending', 'locale' => 'ar',
    ]);

    // The operator is reading the panel in English.
    $this->actingAs($admin)->withSession(['locale' => 'en'])
        ->put(route('admin.orders.update', $placed->id), ['status' => 'shipped', 'payment_status' => 'pending']);

    Mail::assertQueued(OrderStatusChanged::class, fn ($mail) => $mail->locale === 'ar');
});

it('starts from the customer\'s last address', function () use ($order) {
    $user = buyer();
    Address::create(['user_id' => $user->id] + $order);
    cartWithOnePiece($user);

    $this->actingAs($user)->get(route('checkout'))
        ->assertSee('value="Rainbow Street 12"', false)
        ->assertSee(__('diwan.checkout.prefilled'));
});

it('shows where the customer is in the steps', function () {
    $user = buyer();
    cartWithOnePiece($user);

    $this->actingAs($user)->get(route('checkout'))
        ->assertSee('aria-current="step"', false)
        ->assertSee(route('cart.index'), false);
});

it('shows the operator the customer\'s note', function () use ($order) {
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));
    $user = buyer();
    cartWithOnePiece($user);
    $this->actingAs($user)->post(route('checkout.process'), $order + ['notes' => 'Call before coming.']);

    $this->actingAs($admin)->get(route('admin.orders.show', Order::sole()->id))
        ->assertSee('Call before coming.');
});
