<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\Mail;

/**
 * Saudi VAT, 15%, inside every price: the shopper pays what they read, and
 * the tax is shown as part of the total, never added on top. An order keeps
 * the rate it was placed under.
 */
function vatPiece(float $price = 1150): Product
{
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'status' => 'active', 'name' => 'Oak table']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => $price, 'stock' => 5]);

    return $product;
}

it('finds the tax inside a price, to the halala, and adds nothing', function () {
    expect(Money::vatIn(1150))->toBe(150.0)
        ->and(Money::vatIn(1499))->toBe(195.52)     // 1499 × 15 ÷ 115
        ->and(Money::vatIn(100, 0))->toBe(0.0)
        ->and(Money::rate())->toBe('15')
        ->and(Money::rate('5.50'))->toBe('5.5');
});

it('says the price includes VAT, and shows the tax inside the bag\'s total', function () {
    $table = vatPiece(1150);

    $this->get(route('furnish.product.show', $table->slug))->assertSee(__('diwan.money.price_includes_vat'));

    $this->post(route('cart.add'), ['product_id' => $table->id, 'quantity' => 2]);
    $this->get(route('cart.index'))
        ->assertSee(__('diwan.money.vat_line', ['rate' => '15']))
        ->assertSee(Money::format(2300))     // the total is what was read, not more
        ->assertSee(Money::format(300));     // of which tax
});

it('keeps the rate and the tax an order was placed under', function () {
    Mail::fake();
    $table = vatPiece(1150);
    $customer = User::factory()->create();
    Cart::create(['user_id' => $customer->id])->items()->create(['product_id' => $table->id, 'product_variant_id' => $table->variants->first()->id, 'quantity' => 1, 'price' => 1150]);

    $this->actingAs($customer)->post(route('checkout.process'), ['country' => 'Saudi Arabia', 'city' => 'Riyadh', 'street' => 'King Fahd Road 1', 'phone' => '0501234567'])
        ->assertRedirect();

    $order = Order::sole();
    expect((float) $order->total_price)->toBe(1150.0)
        ->and((float) $order->vat_rate)->toBe(15.0)
        ->and((float) $order->vat_amount)->toBe(150.0);

    // A later change of rate does not rewrite it.
    config(['shop.vat_rate' => 20]);
    $this->actingAs($customer)->get(route('orders.show', $order))
        ->assertSee(__('diwan.money.vat_line', ['rate' => '15']))
        ->assertSee(Money::format(150));
});

it('shows no tax line on an order placed before the shop counted VAT', function () {
    $customer = User::factory()->create();
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Saudi Arabia', 'city' => 'Riyadh', 'street' => 'King Fahd Road 1', 'phone' => '0501234567']);
    $order = Order::create(['user_id' => $customer->id, 'shipping_address_id' => $address->id, 'total_price' => 500, 'status' => 'pending', 'payment_status' => 'pending']);

    $this->actingAs($customer)->get(route('orders.show', $order))
        ->assertDontSee(__('diwan.money.vat_line', ['rate' => '15']));
});
