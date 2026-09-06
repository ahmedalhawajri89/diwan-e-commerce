<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;

/**
 * Stock, which until now was a number nobody changed.
 *
 * `product_variants.stock` was set by hand in the admin panel and decremented
 * never. A variant read "69 in stock" after its thousandth sale, the product
 * page printed that, and the structured data published it to search engines —
 * so the shop's one dishonest number had the longest reach of anything on it.
 *
 * The tests that matter here are the ones about the edges: selling the last
 * piece twice, and cancelling an order without quietly destroying inventory.
 */
function stockedProduct(int $stock, string $name = 'Linen Sofa'): ProductVariant
{
    $category = Category::firstOrCreate(['slug' => 'living-room'], ['name' => 'Living room']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'status' => 'active',
        'name' => $name,
    ]);

    return ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100,
        'stock' => $stock,
        'width_cm' => 180,
        'depth_cm' => 90,
        'height_cm' => 75,
    ]);
}

function bagFor(User $user, ProductVariant $variant, int $quantity): void
{
    $cart = Cart::firstOrCreate(['user_id' => $user->id]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $variant->product_id,
        'product_variant_id' => $variant->id,
        'quantity' => $quantity,
        'price' => $variant->price,
    ]);
}

function address(): array
{
    return ['country' => 'Jordan', 'city' => 'Amman', 'street' => 'Rainbow Street 12', 'phone' => '0791234567'];
}

it('takes what was bought out of stock', function () {
    $variant = stockedProduct(10);
    $customer = User::factory()->create();
    bagFor($customer, $variant, 3);

    $this->actingAs($customer)->post(route('checkout.process'), address());

    expect($variant->fresh()->stock)->toBe(7);
});

it('refuses an order it cannot supply, and charges nothing', function () {
    $variant = stockedProduct(2);
    $customer = User::factory()->create();
    bagFor($customer, $variant, 5);

    $this->actingAs($customer)
        ->post(route('checkout.process'), address())
        ->assertRedirect(route('cart.index'));

    expect(Order::count())->toBe(0);

    // And the transaction rolled back cleanly — the shortfall must not have
    // eaten the stock of anything it managed to reserve first.
    expect($variant->fresh()->stock)->toBe(2);
});

it('does not sell the same last piece twice', function () {
    // The reason `lockForUpdate` is in the checkout at all. Both carts hold the
    // last unit; both read stock = 1 without the lock and both succeed.
    $variant = stockedProduct(1);

    [$first, $second] = User::factory()->count(2)->create();
    bagFor($first, $variant, 1);
    bagFor($second, $variant, 1);

    $this->actingAs($first)->post(route('checkout.process'), address());
    $this->actingAs($second)->post(route('checkout.process'), address());

    expect(Order::count())->toBe(1);
    expect($variant->fresh()->stock)->toBe(0);
});

it('leaves the earlier reservation alone when a later item falls short', function () {
    // A cart with two pieces, the second unavailable. The first must not be
    // silently taken out of stock on the way to a failure.
    $available = stockedProduct(10, 'Linen Sofa');
    $scarce = stockedProduct(1, 'Oak Stool');

    $customer = User::factory()->create();
    bagFor($customer, $available, 2);
    bagFor($customer, $scarce, 4);

    $this->actingAs($customer)->post(route('checkout.process'), address());

    expect(Order::count())->toBe(0);
    expect($available->fresh()->stock)->toBe(10);
    expect($scarce->fresh()->stock)->toBe(1);
});

it('puts stock back when an order is cancelled', function () {
    $variant = stockedProduct(10);
    $customer = User::factory()->create();
    bagFor($customer, $variant, 4);

    $this->actingAs($customer)->post(route('checkout.process'), address());
    expect($variant->fresh()->stock)->toBe(6);

    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    $this->actingAs($admin)->put(route('admin.orders.update', Order::first()), [
        'status' => 'cancelled',
        'payment_status' => 'pending',
    ]);

    expect($variant->fresh()->stock)->toBe(10);
});

it('does not credit the same cancellation twice', function () {
    // Re-saving an already-cancelled order used to be indistinguishable from
    // cancelling it, which would have invented stock on every save.
    $variant = stockedProduct(10);
    $customer = User::factory()->create();
    bagFor($customer, $variant, 4);

    $this->actingAs($customer)->post(route('checkout.process'), address());

    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));
    $order = Order::first();

    foreach ([1, 2] as $ignored) {
        $this->actingAs($admin)->put(route('admin.orders.update', $order), [
            'status' => 'cancelled',
            'payment_status' => 'pending',
        ]);
    }

    expect($variant->fresh()->stock)->toBe(10);
});

it('stops advertising a piece as available once it is gone', function () {
    // The structured data reads the same column, so this is also the fix for
    // the search-engine half of the problem.
    $variant = stockedProduct(1);
    $customer = User::factory()->create();
    bagFor($customer, $variant, 1);

    $this->actingAs($customer)->post(route('checkout.process'), address());

    $html = $this->get(route('furnish.product.show', $variant->product->slug))->getContent();

    expect($html)->toContain('https://schema.org/OutOfStock');
    expect($html)->not->toContain('https://schema.org/InStock');
});
