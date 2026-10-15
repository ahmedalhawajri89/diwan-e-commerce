<?php

use App\Mail\OrderStatusChanged;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\Mail;

/**
 * An order as work: one step on at a time, paid, cancelled, noted, every
 * step in its history with who and when, and a slip to put in the box.
 */
function flowAdmin(): User
{
    $user = User::factory()->create(['name' => 'Operator']);
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

function flowOrder(string $status = 'pending', string $payment = 'pending', int $stock = 5): Order
{
    $customer = User::factory()->create(['name' => 'Rana Haddad']);
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Jordan', 'city' => 'Amman', 'street' => 'Rainbow Street 12', 'phone' => '0791234567']);
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'name' => 'Oak stool']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => $stock, 'price' => 150, 'color' => 'Walnut']);

    $order = Order::create(['user_id' => $customer->id, 'shipping_address_id' => $address->id, 'total_price' => 300, 'status' => $status, 'payment_status' => $payment, 'notes' => 'Ring twice.']);
    $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'product_name_snapshot' => 'Oak stool', 'quantity' => 2, 'price' => 150]);

    return $order;
}

it('starts an order\'s history when the customer places it', function () {
    Mail::fake();
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 5, 'price' => 100]);
    $customer = User::factory()->create();
    Cart::create(['user_id' => $customer->id])->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 1, 'price' => 100]);

    $this->actingAs($customer)->post(route('checkout.process'), ['country' => 'Jordan', 'city' => 'Amman', 'street' => 'Street 1', 'phone' => '0790000000']);

    expect(Order::firstOrFail()->events->pluck('kind')->all())->toBe(['placed']);
});

it('moves an order one stage at a time, saying who and when', function () {
    Mail::fake();
    $admin = flowAdmin();
    $order = flowOrder();

    foreach (['processing', 'shipped', 'delivered'] as $expected) {
        $this->actingAs($admin)->post(route('admin.orders.advance', $order))->assertRedirect();
        expect($order->fresh()->status)->toBe($expected);
    }

    $this->actingAs($admin)->post(route('admin.orders.advance', $order))->assertSessionHas('error');
    expect($order->fresh()->status)->toBe('delivered');

    $moves = $order->events()->where('kind', 'status')->get();
    expect($moves->map(fn ($e) => [$e->from, $e->to])->all())->toBe([['pending', 'processing'], ['processing', 'shipped'], ['shipped', 'delivered']])
        ->and($moves->pluck('user_id')->unique()->all())->toBe([$admin->id])
        ->and($order->fresh()->load('events')->stageDates()['shipped'])->not->toBeNull();

    // The customer is told when it ships and when it arrives.
    Mail::assertQueued(OrderStatusChanged::class, 2);
});

it('cancels an order once: stock back, the customer told, the history written', function () {
    Mail::fake();
    $admin = flowAdmin();
    $order = flowOrder(stock: 5);
    $variant = $order->items->first()->variant;

    $this->actingAs($admin)->post(route('admin.orders.cancel', $order));
    $this->actingAs($admin)->post(route('admin.orders.cancel', $order));

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($variant->fresh()->stock)->toBe(7)
        ->and($order->events()->where('kind', 'status')->where('to', 'cancelled')->count())->toBe(1);
    Mail::assertQueued(OrderStatusChanged::class, 1);

    // A cancelled order goes nowhere further.
    $this->actingAs($admin)->post(route('admin.orders.advance', $order))->assertSessionHas('error');
});

it('marks an order paid and writes it down', function () {
    $admin = flowAdmin();
    $order = flowOrder();

    $this->actingAs($admin)->post(route('admin.orders.paid', $order));

    expect($order->fresh()->payment_status)->toBe('paid')
        ->and(OrderEvent::where('kind', 'payment')->sole()->only(['from', 'to', 'user_id']))->toBe(['from' => 'pending', 'to' => 'paid', 'user_id' => $admin->id]);
});

it('keeps a note for the staff on the order, and wants words in it', function () {
    $admin = flowAdmin();
    $order = flowOrder();

    $this->actingAs($admin)->post(route('admin.orders.notes', $order), ['body' => ''])->assertSessionHasErrors('body');
    $this->actingAs($admin)->post(route('admin.orders.notes', $order), ['body' => "Called, no answer.\nTry at four."])->assertRedirect();

    $this->actingAs($admin)->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Called, no answer.')
        ->assertSee(__('admin.orders.history'));

    // The customer's own page never shows it.
    expect($order->events()->where('kind', 'note')->sole()->user_id)->toBe($admin->id);
});

it('logs the stage and payment changed through the edit form too', function () {
    Mail::fake();
    $admin = flowAdmin();
    $order = flowOrder();

    $this->actingAs($admin)->put(route('admin.orders.update', $order), ['status' => 'processing', 'payment_status' => 'paid']);
    $this->actingAs($admin)->put(route('admin.orders.update', $order), ['status' => 'processing', 'payment_status' => 'paid']);

    expect($order->events()->pluck('kind')->all())->toBe(['payment', 'status']);
});

it('shows the order as work: its stages, the next step, who to call', function () {
    $admin = flowAdmin();
    $order = flowOrder();

    $this->actingAs($admin)->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee(__('admin.orders.move_to', ['stage' => __('admin.orders.status_values.processing')]))
        ->assertSee('tel:0791234567', false)
        ->assertSee(route('admin.orders.slip', $order->id), false)
        ->assertSee(__('admin.orders.mark_paid'))
        ->assertSee('Walnut');
});

it('prints a slip with what to pack, where it goes and what to collect', function () {
    $admin = flowAdmin();
    $order = flowOrder();

    $this->actingAs($admin)->get(route('admin.orders.slip', $order))
        ->assertOk()
        ->assertSee($order->number())
        ->assertSee('Oak stool')
        ->assertSee('Rainbow Street 12')
        ->assertSee('0791234567')
        ->assertSee(__('admin.slip.collect'))
        ->assertSee(Money::format(300))
        ->assertSee('Ring twice.');

    $order->update(['payment_status' => 'paid']);
    $this->actingAs($admin)->get(route('admin.orders.slip', $order))->assertSee(__('admin.slip.paid'))->assertDontSee(__('admin.slip.collect'));
});

it('keeps customers away from every step', function () {
    $order = flowOrder();
    $customer = User::factory()->create();
    $customer->roles()->attach(Role::firstOrCreate(['name' => 'customer']));

    foreach (['advance', 'cancel', 'paid'] as $step) {
        $this->actingAs($customer)->post(route('admin.orders.'.$step, $order))->assertRedirect(route('furnish.index'));
    }
    $this->actingAs($customer)->get(route('admin.orders.slip', $order))->assertRedirect(route('furnish.index'));

    expect($order->fresh()->status)->toBe('pending')->and($order->events()->count())->toBe(0);
});
