<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Stock: every movement in the log with what was left, set by hand from the
 * list without a reload, and each piece's page told how whole it is.
 */
function stockAdmin(): User
{
    $user = User::factory()->create(['name' => 'Operator']);
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

function stockPiece(int $stock = 10, array $product = []): ProductVariant
{
    $p = Product::factory()->create(array_merge(['category_id' => Category::factory()->create()->id, 'status' => 'active', 'name' => 'Oak stool'], $product));

    return ProductVariant::factory()->create(['product_id' => $p->id, 'stock' => $stock, 'price' => 100, 'width_cm' => 40, 'depth_cm' => 40, 'height_cm' => 45]);
}

it('logs what a checkout takes, against the order, with what was left', function () {
    Mail::fake();
    $variant = stockPiece(10);
    $customer = User::factory()->create();
    $cart = Cart::create(['user_id' => $customer->id]);
    $cart->items()->create(['product_id' => $variant->product_id, 'product_variant_id' => $variant->id, 'quantity' => 3, 'price' => 100]);

    $this->actingAs($customer)->post(route('checkout.process'), ['country' => 'Jordan', 'city' => 'Amman', 'street' => 'Street 1', 'phone' => '0790000000']);

    $order = Order::firstOrFail();
    $log = InventoryLog::sole();
    expect($log->source_type)->toBe('order')
        ->and($log->reference_id)->toBe($order->id)
        ->and($log->signed())->toBe(-3)
        ->and($log->stock_after)->toBe(7);
});

it('logs stock returned by a cancellation, once', function () {
    Mail::fake();
    $admin = stockAdmin();
    $variant = stockPiece(5);
    $customer = User::factory()->create();
    $address = Address::create(['user_id' => $customer->id, 'country' => 'Jordan', 'city' => 'Amman', 'street' => 'Street 1']);
    $order = Order::create(['user_id' => $customer->id, 'shipping_address_id' => $address->id, 'total_price' => 200, 'status' => 'pending', 'payment_status' => 'pending']);
    $order->items()->create(['product_id' => $variant->product_id, 'product_variant_id' => $variant->id, 'product_name_snapshot' => 'Oak stool', 'quantity' => 2, 'price' => 100]);

    foreach ([1, 2] as $_) {
        $this->actingAs($admin)->put(route('admin.orders.update', $order), ['status' => 'cancelled', 'payment_status' => 'pending']);
    }

    $logs = InventoryLog::where('source_type', 'cancel')->get();
    expect($logs)->toHaveCount(1)
        ->and($logs[0]->signed())->toBe(2)
        ->and($logs[0]->stock_after)->toBe(7)
        ->and($logs[0]->user_id)->toBe($admin->id);
});

it('sets a colour\'s stock from the list without a reload, and says how the piece now stands', function () {
    $admin = stockAdmin();
    $variant = stockPiece(10);
    ProductVariant::factory()->create(['product_id' => $variant->product_id, 'stock' => 0, 'price' => 120]);

    $response = $this->actingAs($admin)->postJson(route('admin.variants.stock', $variant), ['stock' => 2, 'note' => 'Counted the shelf'])
        ->assertOk()
        ->json();

    expect($response)->toBe(['stock' => 2, 'state' => 'low', 'total' => 2, 'totalState' => 'low'])
        ->and(InventoryLog::sole()->only(['source_type', 'change_amount', 'type', 'stock_after', 'note', 'user_id']))
        ->toBe(['source_type' => 'manual', 'change_amount' => 8, 'type' => 'decrease', 'stock_after' => 2, 'note' => 'Counted the shelf', 'user_id' => $admin->id]);

    // The same figure again changes nothing and logs nothing.
    $this->actingAs($admin)->postJson(route('admin.variants.stock', $variant), ['stock' => 2])->assertOk();
    expect(InventoryLog::count())->toBe(1);
});

it('refuses a stock figure below zero, and keeps customers out', function () {
    $admin = stockAdmin();
    $variant = stockPiece(4);

    $this->actingAs($admin)->postJson(route('admin.variants.stock', $variant), ['stock' => -1])->assertUnprocessable();

    $customer = User::factory()->create();
    $customer->roles()->attach(Role::firstOrCreate(['name' => 'customer']));
    $this->actingAs($customer)->post(route('admin.variants.stock', $variant), ['stock' => 99])->assertRedirect(route('furnish.index'));

    expect($variant->fresh()->stock)->toBe(4)->and(InventoryLog::count())->toBe(0);
});

it('logs a stock change made in the product form', function () {
    $admin = stockAdmin();
    $variant = stockPiece(10);
    $product = $variant->product;

    $this->actingAs($admin)->put(route('admin.products.update', $product), [
        'name' => $product->raw('name'), 'slug' => $product->slug, 'description' => 'A stool.',
        'status' => 'active', 'category_id' => $product->category_id, 'price' => 100, 'stock' => 14,
    ])->assertRedirect();

    $log = InventoryLog::sole();
    expect($log->source_type)->toBe('edit')->and($log->signed())->toBe(4)->and($log->stock_after)->toBe(14);
});

it('tells how whole a piece is, and names what it lacks', function () {
    $variant = stockPiece(5, ['name_ar' => 'مقعد', 'description_ar' => null, 'description' => 'A stool.']);
    $product = $variant->product->load(['images', 'variants']);

    expect($product->completeness())->toBe(['photo' => false, 'price' => true, 'measured' => true, 'arabic' => false, 'described' => true]);

    ProductImage::factory()->create(['product_id' => $product->id]);
    $product->update(['description_ar' => 'مقعد من خشب البلوط.']);
    expect(array_filter($product->fresh()->load(['images', 'variants'])->completeness()))->toHaveCount(5);
});

it('marks a sold-out piece in the list and opens its editor there', function () {
    $admin = stockAdmin();
    stockPiece(0, ['name' => 'Gone chair']);

    $this->actingAs($admin)->get(route('admin.products.index'))
        ->assertSee('x-data="stockEditor"', false)
        ->assertSee(__('admin.stock.out'))
        ->assertSee(__('admin.complete.title'));
});

it('shows each piece\'s stock movements on its page', function () {
    $admin = stockAdmin();
    $variant = stockPiece(10);
    $this->actingAs($admin)->postJson(route('admin.variants.stock', $variant), ['stock' => 6, 'note' => 'Two damaged in transit']);

    $this->actingAs($admin)->get(route('admin.products.show', $variant->product_id))
        ->assertOk()
        ->assertSee(__('admin.stock.history'))
        ->assertSee('Two damaged in transit')
        ->assertSee(__('admin.stock.source.manual'));
});
