<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Money;

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

    $html = $response->assertOk()->getContent();
    // The grid, not the header's browse panel, which lists the newest pieces.
    $main = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));

    expect($main)->toContain('Small Stool')->not->toContain('Wide Sofa 1<');
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

it('applies filters only when asked, never as a box is ticked', function () {
    cataloguePiece('Sofa', 1000);

    $html = $this->get(route('furnish.products'))->getContent();
    preg_match('/<form[^>]*id="filterForm".*?<\/form>/s', $html, $m);

    expect($m[0])->not->toContain('data-autosubmit')
        ->toContain(__('diwan.catalogue.apply'));
});

it('shows each applied filter as a chip that removes only itself', function () {
    $sofa = cataloguePiece('Sofa', 1000);
    $sofa->variants()->first()->update(['color' => 'Navy']);
    $category = Category::first();

    $response = $this->get(route('furnish.products', [
        'categories' => [$category->id],
        'colors' => ['Navy'],
        'prices' => ['500_1500'],
    ]));

    $chips = collect(preg_match_all('/aria-label="'.preg_quote(__('diwan.catalogue.remove_filter', ['filter' => '']), '/').'([^"]+)"/', $response->getContent(), $m) ? $m[1] : []);

    expect($chips->all())->toEqualCanonicalizing(['Living Room', 'Navy', __('diwan.catalogue.between', ['from' => Money::format(500), 'to' => Money::format(1500)])]);

    // The colour chip's address keeps the category and the price.
    preg_match('/href="([^"]+)"[^>]*aria-label="[^"]*Navy"/', $response->getContent(), $link);
    $query = urldecode(html_entity_decode($link[1]));
    expect($query)->toContain('categories[0]='.$category->id)
        ->toContain('prices[0]=500_1500')
        ->not->toContain('colors');
});

it('sorts through links, each with its own address', function () {
    cataloguePiece('Sofa', 1000);

    $html = $this->get(route('furnish.products', ['sort' => 'price_low']))->getContent();

    expect($html)->toContain('sort=price_high')
        ->toMatch('/aria-current="true"[^>]*>\s*'.preg_quote(__('diwan.catalogue.sorts.price_low'), '/').'/')
        ->not->toContain('id="sortSelect"');
});

it('names colours in words beside their swatches, in the visitor\'s language', function () {
    cataloguePiece('Sofa', 1000)->variants()->first()->update(['color' => 'Olive']);

    $this->get(route('furnish.products', ['locale' => 'ar']))->assertSee('زيتوني');
});

it('says on the card when a piece is nearly gone, or gone', function () {
    $few = cataloguePiece('Few', 100);
    $few->variants()->update(['stock' => 3]);
    $none = cataloguePiece('None', 100);
    $none->variants()->update(['stock' => 0]);

    $html = $this->get(route('furnish.products'))->getContent();

    expect($html)->toContain(__('diwan.product.last_n', ['count' => 3]))
        ->toContain(__('diwan.product.out_of_stock'));
});

it('counts a piece\'s colours on its card', function () {
    $sofa = cataloguePiece('Sofa', 1000);
    ProductVariant::factory()->create(['product_id' => $sofa->id, 'color' => 'Navy', 'price' => 1000, 'stock' => 2]);
    ProductVariant::factory()->create(['product_id' => $sofa->id, 'color' => 'Olive', 'price' => 1000, 'stock' => 2]);
    $sofa->variants()->oldest('id')->first()->update(['color' => 'Grey']);

    $this->get(route('furnish.products'))->assertSee(trans_choice('diwan.product.colour_count', 3));
});

it('loads the first row of photographs at once, and the rest as they come', function () {
    foreach (range(1, 4) as $n) {
        cataloguePiece("Piece {$n}", 100);
    }

    $html = $this->get(route('furnish.products'))->getContent();

    expect(substr_count($html, 'loading="eager"'))->toBe(2)
        ->and(substr_count($html, 'fetchpriority="high"'))->toBe(1);
});

it('keeps each measurement beside its own letter in Arabic', function () {
    cataloguePiece('Sofa', 1000, [195, 97, 89]);

    $html = $this->get(route('furnish.products', ['locale' => 'ar']))->getContent();

    // Letter, its full word for a screen reader, then the figure isolated
    // left-to-right — never one left-to-right run that reverses the letters.
    expect($html)->toMatch('/>ع<\/span>\s*<span class="sr-only">العرض<\/span>\s*<bdi dir="ltr"[^>]*>195<\/bdi>/')
        ->toMatch('/>م<\/span>\s*<span class="sr-only">العمق<\/span>\s*<bdi dir="ltr"[^>]*>97<\/bdi>/');
});
