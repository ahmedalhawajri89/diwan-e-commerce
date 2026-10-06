<?php

use App\Mail\OrderPlaced;
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

function trackedOrder(string $status = 'pending', ?string $notes = null): Order
{
    $user = User::factory()->create();
    $address = Address::create(['user_id' => $user->id, 'country' => 'Jordan', 'city' => 'Amman', 'street' => 'Rainbow St', 'phone' => '0791234567']);

    return Order::create([
        'user_id' => $user->id, 'shipping_address_id' => $address->id, 'total_price' => 100,
        'status' => $status, 'payment_status' => 'pending', 'notes' => $notes,
    ]);
}

it('gives an order one number, on the site and in its email alike', function () {
    $order = trackedOrder();

    expect($order->number())->toBe('#'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT));

    $mail = (new OrderPlaced($order->load('items', 'address', 'user'), 'en'))->render();
    expect($mail)->toContain($order->number())
        ->and((new OrderPlaced($order, 'en'))->envelope()->subject)->toContain($order->reference());
});

it('translates the status on the confirmation, rather than printing the raw word', function () {
    $order = trackedOrder();

    $this->actingAs($order->user)->withSession(['locale' => 'ar'])
        ->get(route('checkout.thankyou', $order->id))
        ->assertSee(__('diwan.orders.status_values.pending', [], 'ar'))
        ->assertDontSee('>pending<', false);
});

it('says what happens next, with the number the shop will call', function () {
    $order = trackedOrder();

    $this->actingAs($order->user)->get(route('checkout.thankyou', $order->id))
        ->assertSee(__('diwan.thankyou.next_title'))
        ->assertSee('0791234567');
});

it('draws where the order is, marking the current stage, and dates only the one it knows', function () {
    $order = trackedOrder('shipped');

    $html = $this->actingAs($order->user)->get(route('orders.show', $order))->getContent();

    preg_match('/<ol[^>]*aria-label="'.preg_quote(__('diwan.orders.timeline'), '/').'".*?<\/ol>/s', $html, $m);

    expect($m[0])->toMatch('/aria-current="step".*?'.preg_quote(__('diwan.orders.status_values.shipped'), '/').'/s')
        ->and(substr_count($m[0], $order->created_at->translatedFormat('j F Y')))->toBe(1);
});

it('says plainly when an order was cancelled', function () {
    $order = trackedOrder('cancelled');

    $this->actingAs($order->user)->get(route('orders.show', $order))
        ->assertSee(__('diwan.orders.cancelled_note'));
});

it('shows the customer the note they left', function () {
    $order = trackedOrder(notes: 'Third floor, no lift.');

    $this->actingAs($order->user)->get(route('orders.show', $order))->assertSee('Third floor, no lift.');
});
