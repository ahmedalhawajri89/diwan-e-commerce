<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\ShopSetting;
use App\Models\User;
use App\Support\DeliveryDates;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Delivery by city, with the fee the shop set (or "agreed on contact", never
 * free), and a day booked only on the days the shop delivers.
 */
function deliveryBuyer(float $price = 1000): User
{
    $customer = User::factory()->create();
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'status' => 'active']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock' => 5, 'price' => $price]);
    Cart::create(['user_id' => $customer->id])->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'quantity' => 1, 'price' => $price]);

    return $customer;
}

function deliveryForm(array $more = []): array
{
    return $more + ['country' => 'Saudi Arabia', 'street' => 'King Fahd Road 1', 'phone' => '0501234567'];
}

beforeEach(fn () => Mail::fake());

it('asks for the city as text and agrees the fee by phone while no cities are set', function () {
    $customer = deliveryBuyer();

    $this->actingAs($customer)->get(route('checkout'))
        ->assertSee('name="city"', false)
        ->assertDontSee('name="delivery_zone_id"', false)
        ->assertSee(__('diwan.checkout.call_to_book'));

    $this->actingAs($customer)->post(route('checkout.process'), deliveryForm(['city' => 'Riyadh']))->assertRedirect();
    expect(Order::sole()->only(['delivery_fee', 'delivery_date']))->toBe(['delivery_fee' => null, 'delivery_date' => null]);
});

it('adds the chosen city\'s fee to the total, with the VAT inside it', function () {
    $riyadh = DeliveryZone::create(['name' => 'Riyadh', 'name_ar' => 'الرياض', 'fee' => 150, 'active' => true]);
    DeliveryZone::create(['name' => 'Jeddah', 'fee' => 200, 'active' => false]);
    $customer = deliveryBuyer(1000);

    $this->actingAs($customer)->get(route('checkout'))
        ->assertSee('name="delivery_zone_id"', false)
        ->assertDontSee('name="city"', false)
        ->assertSee('Riyadh')
        ->assertDontSee('Jeddah')                                   // hidden cities are not offered
        ->assertSee('data-total="'.Money::format(1150).'"', false);

    $this->actingAs($customer)->post(route('checkout.process'), deliveryForm(['delivery_zone_id' => $riyadh->id]))->assertRedirect();

    $order = Order::sole();
    expect((float) $order->total_price)->toBe(1150.0)
        ->and((float) $order->delivery_fee)->toBe(150.0)
        ->and((float) $order->vat_amount)->toBe(150.0)               // 1150 × 15 ÷ 115
        ->and($order->address->city)->toBe('Riyadh');
});

it('never calls an unknown fee free', function () {
    $zone = DeliveryZone::create(['name' => 'Dammam', 'fee' => null, 'active' => true]);
    $customer = deliveryBuyer(1000);

    $this->actingAs($customer)->post(route('checkout.process'), deliveryForm(['delivery_zone_id' => $zone->id]));

    $order = Order::sole();
    expect($order->delivery_fee)->toBeNull()->and((float) $order->total_price)->toBe(1000.0);

    $this->actingAs($customer)->get(route('orders.show', $order))->assertSee(__('diwan.bag.shipping_on_contact'));
});

it('refuses a city that is hidden, or none at all when cities are set', function () {
    $hidden = DeliveryZone::create(['name' => 'Abha', 'fee' => 90, 'active' => false]);
    DeliveryZone::create(['name' => 'Riyadh', 'fee' => 150, 'active' => true]);
    $customer = deliveryBuyer();

    $this->actingAs($customer)->post(route('checkout.process'), deliveryForm(['delivery_zone_id' => $hidden->id]))->assertSessionHasErrors('delivery_zone_id');
    $this->actingAs($customer)->post(route('checkout.process'), deliveryForm(['city' => 'Riyadh']))->assertSessionHasErrors('delivery_zone_id');

    expect(Order::count())->toBe(0);
});

it('offers only the days the shop delivers, from its notice, two weeks ahead', function () {
    $settings = new ShopSetting(['delivery_lead_days' => 2, 'delivery_weekdays' => [0, 2]]);   // Sundays and Tuesdays
    $days = DeliveryDates::options($settings, Carbon::parse('2026-10-14'));                    // a Wednesday

    expect($days->map->toDateString()->all())->toBe(['2026-10-18', '2026-10-20', '2026-10-25', '2026-10-27']);
    expect(DeliveryDates::options(new ShopSetting))->toBeEmpty();
});

it('books a delivery day and part of the day, only one the shop offers', function () {
    Carbon::setTestNow('2026-10-14 09:00');
    ShopSetting::create(['delivery_lead_days' => 1, 'delivery_weekdays' => [4]]);   // Thursdays
    $customer = deliveryBuyer();

    $this->actingAs($customer)->get(route('checkout'))->assertSee('value="2026-10-15"', false)->assertDontSee('value="2026-10-16"', false);

    $this->actingAs($customer)->post(route('checkout.process'), deliveryForm(['city' => 'Riyadh', 'delivery_date' => '2026-10-16', 'delivery_window' => 'morning']))
        ->assertSessionHasErrors(['delivery_date' => __('diwan.checkout.date_unavailable')]);
    $this->actingAs($customer)->post(route('checkout.process'), deliveryForm(['city' => 'Riyadh', 'delivery_date' => '2026-10-15']))
        ->assertSessionHasErrors('delivery_window');

    $this->actingAs($customer)->post(route('checkout.process'), deliveryForm(['city' => 'Riyadh', 'delivery_date' => '2026-10-15', 'delivery_window' => 'evening']))
        ->assertRedirect();

    $order = Order::sole();
    expect($order->delivery_date->toDateString())->toBe('2026-10-15')->and($order->delivery_window)->toBe('evening');

    $this->actingAs($customer)->get(route('orders.show', $order))
        ->assertSee(__('diwan.delivery.booked', ['day' => $order->delivery_date->translatedFormat('l j F'), 'window' => __('diwan.checkout.windows.evening')]));

    Carbon::setTestNow();
});

it('lets the panel add, change and remove a city, and keeps the fee on old orders', function () {
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    $this->actingAs($admin)->post(route('admin.delivery-zones.store'), ['name' => 'Riyadh', 'name_ar' => 'الرياض', 'fee' => '150', 'active' => '1'])
        ->assertRedirect(route('admin.delivery-zones.index'));
    $zone = DeliveryZone::sole();

    $this->actingAs($admin)->put(route('admin.delivery-zones.update', $zone), ['name' => 'Riyadh', 'fee' => '', 'active' => '1']);
    expect($zone->fresh()->fee)->toBeNull();

    $this->actingAs($admin)->post(route('admin.delivery-zones.store'), ['name' => 'Bad', 'fee' => '-5'])->assertSessionHasErrors('fee');

    $customer = deliveryBuyer();
    $zone->update(['fee' => 120]);
    $this->actingAs($customer)->post(route('checkout.process'), deliveryForm(['delivery_zone_id' => $zone->id]));

    $this->actingAs($admin)->delete(route('admin.delivery-zones.destroy', $zone))->assertRedirect();
    expect((float) Order::sole()->delivery_fee)->toBe(120.0)->and(Order::sole()->delivery_zone_id)->toBeNull();
});
