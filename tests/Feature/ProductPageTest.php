<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * The product page: the page that sells. Each test here pins one thing a
 * shopper relies on.
 */
function pagePiece(array $variants = [['color' => 'Grey', 'price' => 1499, 'stock' => 10]]): Product
{
    $category = Category::firstOrCreate(['slug' => 'living-room'], ['name' => 'Living Room', 'name_ar' => 'غرفة المعيشة']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'status' => 'active',
        'name' => 'Cozy Sofa',
        'name_ar' => 'كنبة مريحة',
    ]);

    foreach ($variants as $v) {
        ProductVariant::factory()->create(['product_id' => $product->id, 'width_cm' => 200, 'depth_cm' => 90, 'height_cm' => 80] + $v);
    }

    return $product;
}

function pageHtml(Product $product, string $query = '', string $locale = 'en'): string
{
    return test()->withSession(['locale' => $locale])
        ->get(route('furnish.product.show', $product->slug).$query)
        ->assertOk()
        ->getContent();
}

it('says where the visitor is, as a trail ending on the piece', function () {
    $html = pageHtml(pagePiece());

    preg_match('/<nav[^>]*aria-label="'.preg_quote(__('diwan.nav.breadcrumbs'), '/').'".*?<\/nav>/s', $html, $m);

    expect($m[0] ?? '')->toContain(route('furnish.index'))
        ->toContain(route('furnish.category', 'living-room'))
        ->toMatch('/aria-current="page"[^>]*>Cozy Sofa</');
});

it('draws the same trail it publishes to search engines', function () {
    $html = pageHtml(pagePiece());

    expect($html)->toContain('"@type":"BreadcrumbList"')
        ->toContain('"name":"Home"');
});

it('opens its outline with the piece, not the drawing', function () {
    $html = pageHtml(pagePiece());

    // Only the page's own content: the layout's header carries no headings.
    $main = substr($html, strpos($html, '<main'));

    expect(strpos($main, '<h1'))->toBeLessThan(strpos($main, '<h2'));
});

it('does not pass the address off as a product reference', function () {
    $product = pagePiece();

    expect(pageHtml($product))->not->toContain('>'.$product->slug.'</dd>');
});
