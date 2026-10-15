<?php

use App\Models\Address;
use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Role;
use App\Models\ShopSetting;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * A customer asks to send a delivered order back, and says why; the shop
 * approves or declines, then marks the pieces received, back in stock. The
 * reasons are counted, so the shop sees whether returns for size are few.
 */
function returnAdmin(): User
{
    $admin = User::factory()->create(['name' => 'Operator']);
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $admin;
}

function deliveredOrder(int $stock = 2, ?Carbon $deliveredAt = null): Order
{
    $customer = User::factory()->create();
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Saudi Arabia', 'city' => 'Riyadh', 'street' => 'King Fahd Road 1', 'phone' => '0501234567']);
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'name' => 'Oak table']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => $stock, 'price' => 1000]);
    $order = Order::create(['user_id' => $customer->id, 'shipping_address_id' => $address->id, 'total_price' => 2000, 'status' => 'delivered', 'payment_status' => 'paid']);
    $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'product_name_snapshot' => 'Oak table', 'quantity' => 2, 'price' => 1000]);
    // Delivered when the test says: written directly, created_at is not fillable.
    $event = $order->events()->create(['kind' => 'status', 'from' => 'shipped', 'to' => 'delivered']);
    $event->forceFill(['created_at' => $deliveredAt ?? now()])->save();

    return $order;
}

it('sends a customer to contact the shop while it has set no return period', function () {
    $order = deliveredOrder();

    $this->actingAs($order->user)->get(route('orders.show', $order))
        ->assertSee(__('diwan.returns.contact'))
        ->assertDontSee(__('diwan.returns.ask'));

    $this->actingAs($order->user)->post(route('orders.return', $order), ['reason' => 'size'])->assertStatus(409);
    expect(ReturnRequest::count())->toBe(0);
});

it('lets the customer ask, with a reason, within the shop\'s period, and tells the panel', function () {
    ShopSetting::create(['return_days' => 14]);
    $admin = returnAdmin();
    $order = deliveredOrder();

    $this->actingAs($order->user)->get(route('orders.show', $order))->assertSee(__('diwan.returns.ask'));

    $this->actingAs($order->user)->post(route('orders.return', $order), ['reason' => 'size', 'note' => 'The door is narrower than I measured.'])
        ->assertRedirect(route('orders.show', $order));

    $return = ReturnRequest::sole();
    expect($return->only(['reason', 'status']))->toBe(['reason' => 'size', 'status' => 'requested'])
        ->and(Notification::where('type', 'return_requested')->where('notifiable_id', $admin->id)->exists())->toBeTrue();

    // Once only.
    $this->actingAs($order->user)->post(route('orders.return', $order), ['reason' => 'colour'])->assertStatus(409);

    $this->actingAs($order->user)->get(route('orders.show', $order))
        ->assertSee(__('diwan.returns.asked', ['reason' => __('diwan.returns.reasons.size')]))
        ->assertSee(__('diwan.returns.status.requested'));
});

it('closes the period when it has passed, and asks nothing before delivery', function () {
    ShopSetting::create(['return_days' => 7]);

    $late = deliveredOrder(deliveredAt: now()->subDays(10));
    $this->actingAs($late->user)->post(route('orders.return', $late), ['reason' => 'size'])->assertStatus(409);
    $this->actingAs($late->user)->get(route('orders.show', $late))->assertSee(__('diwan.returns.closed', ['date' => now()->subDays(3)->translatedFormat('j F')]));

    $shipped = deliveredOrder();
    $shipped->update(['status' => 'shipped']);
    $this->actingAs($shipped->user)->post(route('orders.return', $shipped), ['reason' => 'size'])->assertStatus(409);
});

it('takes a request only from the order\'s owner, and only a reason it offers', function () {
    ShopSetting::create(['return_days' => 14]);
    $order = deliveredOrder();

    $this->actingAs(User::factory()->create())->post(route('orders.return', $order), ['reason' => 'size'])->assertNotFound();
    $this->actingAs($order->user)->post(route('orders.return', $order), ['reason' => 'boredom'])->assertSessionHasErrors('reason');

    expect(ReturnRequest::count())->toBe(0);
});

it('approves, then receives the pieces back into stock, once, with the log saying so', function () {
    ShopSetting::create(['return_days' => 14]);
    $admin = returnAdmin();
    $order = deliveredOrder(stock: 2);
    $return = $order->returnRequest()->create(['user_id' => $order->user_id, 'reason' => 'size']);

    // Not received before it is approved.
    $this->actingAs($admin)->post(route('admin.returns.receive', $return))->assertStatus(409);

    $this->actingAs($admin)->post(route('admin.returns.approve', $return))->assertRedirect();
    $this->actingAs($admin)->post(route('admin.returns.receive', $return))->assertRedirect();

    $variant = $order->items->first()->variant->fresh();
    expect($return->fresh()->status)->toBe('received')
        ->and($variant->stock)->toBe(4)                                       // 2 on the shelf + 2 back
        ->and(InventoryLog::where('source_type', 'return')->sole()->stock_after)->toBe(4)
        ->and($order->events()->where('kind', 'return')->pluck('to')->all())->toBe(['approved', 'received']);

    // Never twice.
    $this->actingAs($admin)->post(route('admin.returns.receive', $return))->assertStatus(409);
    expect($variant->fresh()->stock)->toBe(4);
});

it('declines a request, and the customer sees it', function () {
    $admin = returnAdmin();
    $order = deliveredOrder();
    $return = $order->returnRequest()->create(['user_id' => $order->user_id, 'reason' => 'changed_mind']);

    $this->actingAs($admin)->post(route('admin.returns.reject', $return))->assertRedirect();

    expect($return->fresh()->status)->toBe('rejected');
    $this->actingAs($order->user)->get(route('orders.show', $order))->assertSee(__('diwan.returns.status.rejected'));
});

it('lists returns by state with real counts, and counts the reasons on the overview', function () {
    $admin = returnAdmin();
    deliveredOrder()->returnRequest()->create(['reason' => 'size']);
    deliveredOrder()->returnRequest()->create(['reason' => 'size', 'status' => 'approved']);
    deliveredOrder()->returnRequest()->create(['reason' => 'colour']);

    $this->actingAs($admin)->get(route('admin.returns.index', ['status' => 'requested']))
        ->assertOk()
        ->assertSee(__('diwan.returns.reasons.colour'))
        ->assertDontSee(__('admin.returns.receive'));

    $html = $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(__('admin.dashboard.returns_none'))->getContent();
    expect($html)->toMatch('/>2<\/span> '.preg_quote(e(__('diwan.returns.reasons.size')), '/').'/')
        ->toMatch('/>1<\/span> '.preg_quote(e(__('diwan.returns.reasons.colour')), '/').'/');
});

it('keeps customers out of the panel\'s returns', function () {
    $order = deliveredOrder();
    $return = $order->returnRequest()->create(['reason' => 'size']);

    $this->actingAs($order->user)->post(route('admin.returns.approve', $return))->assertRedirect();
    expect($return->fresh()->status)->toBe('requested');
});
