<?php

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\ShopSetting;
use App\Models\User;
use App\Support\Money;
use App\Support\ZatcaQr;

/**
 * An order's paper: a simplified tax invoice with ZATCA's QR code only when
 * the shop has a VAT number and the sale has happened, a receipt that says
 * why otherwise, and an issued invoice that never changes afterwards.
 */
function invoicedOrder(string $status = 'delivered', string $payment = 'pending'): Order
{
    $customer = User::factory()->create(['name' => 'Rana']);
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Saudi Arabia', 'city' => 'Riyadh', 'street' => 'King Fahd Road 1', 'phone' => '0501234567']);
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'name' => 'Oak table']);
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1150, 'stock' => 3]);
    $order = Order::create(['user_id' => $customer->id, 'shipping_address_id' => $address->id, 'total_price' => 1150,
        'vat_rate' => 15, 'vat_amount' => 150, 'status' => $status, 'payment_status' => $payment]);
    $order->items()->create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'product_name_snapshot' => 'Oak table', 'quantity' => 1, 'price' => 1150]);

    return $order;
}

function registerForVat(string $name = 'Diwan Furniture Trading Est.'): void
{
    ShopSetting::create(['legal_name' => $name, 'vat_number' => '300000000000003']);
}

it('encodes the QR payload exactly as ZATCA\'s own sample', function () {
    // The worked example in ZATCA's e-invoicing guidance.
    $payload = ZatcaQr::payload('Bobs Records', '310122393500003', new DateTimeImmutable('2022-04-25T15:30:00Z'), 1000, 150);

    expect($payload)->toBe('AQxCb2JzIFJlY29yZHMCDzMxMDEyMjM5MzUwMDAwMwMUMjAyMi0wNC0yNVQxNTozMDowMFoEBzEwMDAuMDAFBjE1MC4wMA==');
});

it('counts an Arabic name\'s length in bytes, as the format does', function () {
    $bytes = base64_decode(ZatcaQr::payload('مؤسسة ديوان', '300000000000003', new DateTimeImmutable('2026-10-14T10:00:00Z'), 1150, 150));

    expect(ord($bytes[0]))->toBe(1)
        ->and(ord($bytes[1]))->toBe(strlen('مؤسسة ديوان'))
        ->and(substr($bytes, 2, ord($bytes[1])))->toBe('مؤسسة ديوان');
});

it('gives a receipt, not a tax invoice, while the shop has no VAT number', function () {
    $order = invoicedOrder();

    $this->actingAs($order->user)->get(route('orders.invoice', $order))
        ->assertOk()
        ->assertSee(__('diwan.invoice.receipt'))
        ->assertSee(__('diwan.invoice.why.no_vat_number'))
        ->assertDontSee(__('diwan.invoice.tax'))
        ->assertDontSee('<svg xmlns="http://www.w3.org/2000/svg" class="qr-svg', false);

    expect($order->fresh()->invoiced_at)->toBeNull();
});

it('waits for the sale to happen before issuing the tax invoice', function () {
    registerForVat();
    $order = invoicedOrder('processing');

    $this->actingAs($order->user)->get(route('orders.invoice', $order))
        ->assertSee(__('diwan.invoice.why.not_supplied'))
        ->assertDontSee(__('diwan.invoice.tax'));
});

it('issues a simplified tax invoice with its QR code once delivered, and freezes it', function () {
    registerForVat();
    $order = invoicedOrder('delivered');

    $this->actingAs($order->user)->get(route('orders.invoice', $order))
        ->assertOk()
        ->assertSee(__('diwan.invoice.tax'))
        ->assertSee('INV-'.$order->reference())
        ->assertSee('300000000000003')
        ->assertSee('<svg xmlns="http://www.w3.org/2000/svg" class="qr-svg', false)
        ->assertSee(Money::format(1000))     // before VAT
        ->assertSee(Money::format(150));     // the VAT

    $order->refresh();
    expect($order->invoiced_at)->not->toBeNull()
        ->and($order->invoice_seller)->toBe('Diwan Furniture Trading Est.');

    // The shop renames itself later: the invoice already issued does not change.
    ShopSetting::current()->update(['legal_name' => 'Another Name Ltd.']);
    $this->actingAs($order->user)->get(route('orders.invoice', $order))
        ->assertSee('Diwan Furniture Trading Est.')
        ->assertDontSee('Another Name Ltd.');
});

it('issues it once paid, even before delivery', function () {
    registerForVat();
    $order = invoicedOrder('shipped', 'paid');

    $this->actingAs($order->user)->get(route('orders.invoice', $order))->assertSee(__('diwan.invoice.tax'));
});

it('shows it only to its owner and the panel, and never for a cancelled order', function () {
    $order = invoicedOrder();
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    $this->actingAs(User::factory()->create())->get(route('orders.invoice', $order))->assertNotFound();
    $this->actingAs($admin)->get(route('admin.orders.invoice', $order->id))->assertOk()->assertSee(__('diwan.invoice.receipt'));

    $order->update(['status' => 'cancelled']);
    $this->actingAs($order->user)->get(route('orders.invoice', $order))->assertNotFound();
});

it('refuses a legal name too long for the QR code\'s one-byte length', function () {
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    $this->actingAs($admin)->put(route('admin.settings.update'), ['legal_name' => str_repeat('م', 130)])   // 130 letters, 260 bytes
        ->assertSessionHasErrors(['legal_name' => __('admin.settings.legal_name_too_long')]);
});
