<?php

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

/**
 * The customer's order history. Before this existed, an order was visible only
 * on the thank-you page immediately after placing it.
 */
function shopper(): User
{
    return User::factory()->create();
}

function placedOrder(User $user, string $name = 'Walnut armchair'): Order
{
    $address = Address::create([
        'user_id' => $user->id,
        'country' => 'Jordan',
        'city' => 'Amman',
        'street' => 'Rainbow Street 12',
        'phone' => '0791234567',
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'shipping_address_id' => $address->id,
        'total_price' => 480.00,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 240]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name_snapshot' => $name,
        'quantity' => 2,
        'price' => 240.00,
    ]);

    return $order;
}

it('lists a customer\'s own orders', function () {
    $user = shopper();
    placedOrder($user);

    $this->actingAs($user)->get(route('orders.index'))->assertOk();
});

it('shows an order the customer placed', function () {
    $user = shopper();
    $order = placedOrder($user);

    $this->actingAs($user)->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Walnut armchair')
        ->assertSee('Rainbow Street 12');
});

it('will not show one customer another customer\'s order', function () {
    // An order id is a guessable integer, so ownership is checked before
    // anything is loaded. 404 rather than 403: a stranger should not learn
    // that the order exists.
    $owner = shopper();
    $stranger = shopper();
    $order = placedOrder($owner);

    $this->actingAs($stranger)->get(route('orders.show', $order))->assertNotFound();
});

it('turns a guest away from the history', function () {
    $this->get(route('orders.index'))->assertRedirect(route('login'));
});

it('keeps showing what was bought after the product is renamed', function () {
    $user = shopper();
    $order = placedOrder($user);

    $order->items->first()->product->update(['name' => 'Renamed after the sale']);

    $this->actingAs($user)->get(route('orders.show', $order))
        ->assertSee('Walnut armchair')
        ->assertDontSee('Renamed after the sale');
});

it('keeps showing what was bought after the product is deleted', function () {
    // The snapshot is the record; the live row is only a link. Deleting the
    // product must not blank out somebody's purchase history.
    $user = shopper();
    $order = placedOrder($user);

    $order->items->first()->product->delete();

    $this->actingAs($user)->get(route('orders.show', $order->fresh()))
        ->assertOk()
        ->assertSee('Walnut armchair');
});

it('says the order is empty rather than inventing one', function () {
    $user = shopper();

    $this->actingAs($user)->get(route('orders.index'))
        ->assertOk()
        ->assertSee(__('diwan.orders.empty'));
});
