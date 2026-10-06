<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * The catalogue: what it lists, in what order, and what it says when it
 * lists nothing.
 */
function cataloguePiece(string $name, int $price, ?array $dims = [100, 50, 80], ?string $created = null): Product
{
    $category = Category::firstOrCreate(['slug' => 'living-room'], ['name' => 'Living Room']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'status' => 'active',
        'name' => $name,
        'created_at' => $created ?? now(),
    ]);

    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => $price,
        'stock' => 5,
        'width_cm' => $dims[0] ?? null,
        'depth_cm' => $dims[1] ?? null,
        'height_cm' => $dims[2] ?? null,
    ]);

    return $product;
}

it('finds pieces that fit on every page, not only those that happen to be on page one', function () {
    // Fourteen pieces too big for the room, newest, so they fill page one…
    foreach (range(1, 14) as $n) {
        cataloguePiece("Wide Sofa {$n}", 1000, [400, 200, 80], now()->subMinutes($n)->toDateTimeString());
    }
    // …and one that fits, oldest, so it sorts onto page two.
    cataloguePiece('Small Stool', 90, [40, 40, 50], now()->subDays(3)->toDateTimeString());

    $response = $this->withSession(['diwan.room' => ['width' => 300, 'length' => 300]])
        ->get(route('furnish.products', ['fit_only' => 1]));

    $response->assertOk()->assertSee('Small Stool')->assertDontSee('Wide Sofa 1<', false);
    expect($response->viewData('products')->total())->toBe(1);
});

it('counts only the pieces that fit when the fit filter is on', function () {
    cataloguePiece('Fits', 100, [40, 40, 50]);
    cataloguePiece('Too Big', 100, [400, 200, 80]);

    $products = $this->withSession(['diwan.room' => ['width' => 300, 'length' => 300]])
        ->get(route('furnish.products', ['fit_only' => 1]))
        ->viewData('products');

    expect($products->total())->toBe(1)->and($products->first()->name)->toBe('Fits');
});

it('sorts by price, by each piece\'s cheapest colour', function () {
    $dear = cataloguePiece('Dear', 2000);
    $cheap = cataloguePiece('Cheap', 300);
    $middle = cataloguePiece('Middle', 900);
    // A second, cheaper colour makes "Dear" the cheapest of all.
    ProductVariant::factory()->create(['product_id' => $dear->id, 'price' => 100, 'stock' => 1]);

    $low = $this->get(route('furnish.products', ['sort' => 'price_low']))->viewData('products')->pluck('name')->all();
    $high = $this->get(route('furnish.products', ['sort' => 'price_high']))->viewData('products')->pluck('name')->all();

    expect($low)->toBe(['Dear', 'Cheap', 'Middle'])
        ->and($high)->toBe(['Middle', 'Cheap', 'Dear']);
});

it('says why nothing matched a search', function () {
    cataloguePiece('Sofa', 1000);

    $this->get(route('furnish.products', ['q' => 'wardrobe']))
        ->assertSee(__('diwan.catalogue.empty_query', ['query' => 'wardrobe']), false);
});

it('says when nothing fits the room, and names the room', function () {
    cataloguePiece('Too Big', 100, [400, 200, 80]);

    $this->withSession(['diwan.room' => ['width' => 300, 'length' => 300]])
        ->get(route('furnish.products', ['fit_only' => 1]))
        ->assertSee(__('diwan.catalogue.empty_fit', ['size' => '300×300 cm']), false);
});

it('offers the first page when the address is past the last one', function () {
    cataloguePiece('Sofa', 1000);

    $this->get(route('furnish.products', ['page' => 9]))
        ->assertSee(__('diwan.catalogue.empty_past_end'))
        ->assertSee(__('diwan.catalogue.first_page'));
});

it('still honours a price band from an older link', function () {
    cataloguePiece('Cheap', 50);
    cataloguePiece('Dear', 2000);

    $names = $this->get(route('furnish.products', ['prices' => ['under_100']]))->viewData('products')->pluck('name')->all();

    expect($names)->toBe(['Cheap']);
});
