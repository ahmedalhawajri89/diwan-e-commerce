<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * The way in travels with the order: kept as the customer gave it, shown to
 * whoever delivers it, and checked afterwards against what happened.
 */
function wayOperator(): User
{
    $user = User::factory()->create(['name' => 'Operator']);
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

function wayOrder(string $status = 'pending', ?array $route = null): Order
{
    $customer = User::factory()->create();
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Jordan', 'city' => 'Amman', 'street' => 'Rainbow Street 12', 'phone' => '0791234567']);
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'name' => 'Deep sofa']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 3, 'price' => 900,
        'width_cm' => 220, 'depth_cm' => 95, 'height_cm' => 85, 'arrives' => 'assembled']);

    $order = Order::create(['user_id' => $customer->id, 'shipping_address_id' => $address->id, 'total_price' => 900,
        'status' => $status, 'payment_status' => 'pending', 'route' => $route]);
    $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'product_name_snapshot' => 'Deep sofa', 'quantity' => 1, 'price' => 900]);

    return $order;
}

$liftRoute = ['access' => 'lift', 'door' => ['w' => 95, 'h' => 210],
    'lift' => ['door_w' => 80, 'door_h' => 200, 'w' => 110, 'd' => 140, 'h' => 220], 'stairs' => null, 'balcony' => ['w' => 120, 'h' => 210]];

it('keeps the way in with the order, as the customer gave it', function () {
    Mail::fake();
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 5, 'price' => 100]);
    $customer = User::factory()->create();
    Cart::create(['user_id' => $customer->id])->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 1, 'price' => 100]);

    $this->actingAs($customer)->post(route('room.route.store'), ['access' => 'stairs', 'door_w' => '90', 'door_h' => '205', 'stairs_w' => '100', 'stairs_landing' => '110']);
    $this->actingAs($customer)->post(route('checkout.process'), ['country' => 'Jordan', 'city' => 'Amman', 'street' => 'Rainbow Street 12', 'phone' => '0791234567'])
        ->assertRedirect();

    expect(Order::sole()->route)->toMatchArray(['access' => 'stairs', 'door' => ['w' => 90, 'h' => 205], 'stairs' => ['w' => 100, 'landing' => 110]]);
});

it('shows the way in and each piece\'s verdict on the order, and on its slip', function () use ($liftRoute) {
    $order = wayOrder(route: $liftRoute);
    $admin = wayOperator();

    $this->actingAs($admin)->get(route('admin.orders.show', $order->id))
        ->assertSee(__('admin.orders.way.title'))
        ->assertSee('80×200')
        ->assertSee(__('diwan.passage.hoist'));

    $this->actingAs($admin)->get(route('admin.orders.slip', $order->id))
        ->assertSee(__('admin.orders.way.title'))
        ->assertSee(__('diwan.passage.hoist'));
});

it('says plainly when the customer gave no way in', function () {
    $this->actingAs(wayOperator())->get(route('admin.orders.show', wayOrder()->id))
        ->assertSee(__('admin.orders.way.none'));
});

it('keeps why an order was cancelled, and shows it in its history', function () {
    Mail::fake();
    $order = wayOrder();

    $this->actingAs(wayOperator())->post(route('admin.orders.cancel', $order->id), ['reason' => 'route'])->assertRedirect();

    expect($order->fresh()->cancel_reason)->toBe('route')
        ->and(OrderEvent::where('to', 'cancelled')->sole()->body)->toBe('route');

    $this->get(route('admin.orders.show', $order->id))->assertSee(__('admin.orders.cancel_reasons.route'));

    // A reason it does not offer is refused; none at all is fine.
    $this->post(route('admin.orders.cancel', wayOrder()->id), ['reason' => 'gremlins'])->assertSessionHasErrors('reason');
    $this->post(route('admin.orders.cancel', $plain = wayOrder()->id))->assertRedirect();
    expect(Order::find($plain)->cancel_reason)->toBeNull();
});

it('asks the customer once, after delivery, whether it got in', function () {
    $order = wayOrder('delivered');
    $customer = $order->user;

    $this->actingAs($customer)->get(route('orders.show', $order))->assertSee(__('diwan.orders.feedback.question'));

    $this->actingAs($customer)->post(route('orders.feedback', $order), ['answer' => 'hard'])
        ->assertRedirect(route('orders.show', $order));

    expect($order->fresh()->delivery_feedback)->toBe('hard')
        ->and(OrderEvent::where('kind', 'feedback')->sole()->to)->toBe('hard');

    $this->actingAs($customer)->get(route('orders.show', $order))
        ->assertDontSee(__('diwan.orders.feedback.question'))
        ->assertSee(__('diwan.orders.feedback.thanks'));

    // Once only.
    $this->actingAs($customer)->post(route('orders.feedback', $order), ['answer' => 'easy'])->assertStatus(409);
    expect($order->fresh()->delivery_feedback)->toBe('hard');
});

it('does not ask before delivery, nor take an answer from anyone else', function () {
    $pending = wayOrder('shipped');
    $this->actingAs($pending->user)->get(route('orders.show', $pending))->assertDontSee(__('diwan.orders.feedback.question'));
    $this->actingAs($pending->user)->post(route('orders.feedback', $pending), ['answer' => 'easy'])->assertStatus(409);

    $delivered = wayOrder('delivered');
    $this->actingAs(User::factory()->create())->post(route('orders.feedback', $delivered), ['answer' => 'easy'])->assertNotFound();
    $this->actingAs($delivered->user)->post(route('orders.feedback', $delivered), ['answer' => 'maybe'])->assertSessionHasErrors('answer');

    expect($delivered->fresh()->delivery_feedback)->toBeNull();
});

it('counts the answers on the overview, and shows nothing invented before there are any', function () {
    $admin = wayOperator();

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertSee(__('admin.dashboard.way_in_none'));

    wayOrder('delivered')->update(['delivery_feedback' => 'easy']);
    wayOrder('delivered')->update(['delivery_feedback' => 'easy']);
    wayOrder('delivered')->update(['delivery_feedback' => 'failed']);
    wayOrder('cancelled')->update(['cancel_reason' => 'route']);

    $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(__('admin.dashboard.way_in_none'))->getContent();
    expect($html)->toMatch('/>2<\/span> '.preg_quote(__('admin.orders.feedback.easy'), '/').'/')
        ->toMatch('/>1<\/span> '.preg_quote(__('admin.orders.feedback.failed'), '/').'/')
        ->toMatch('/>1<\/span> '.preg_quote(__('admin.dashboard.way_in_cancelled'), '/').'/');
});
