<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;

/**
 * Findability, and the honesty rules applied to it.
 *
 * The site shipped with one description — the brand tagline — on every page, no
 * canonical, no alternates, no Open Graph and no structured data. Every link
 * anyone shared arrived as a bare URL, and every filter permutation competed
 * with the catalogue it was a view of.
 *
 * The half that matters most is the negative half. Structured data is repeated
 * by a search engine to people who never visit the site, so a `Product` node
 * that claims a rating nobody left or a stock level nobody recorded is an
 * invented metric with a much longer reach than one on a page.
 */
function schemaOn(string $url): array
{
    $html = test()->get($url)->assertOk()->getContent();

    if (! preg_match('/application\/ld\+json[^>]*>(.*?)<\/script>/s', $html, $m)) {
        return [];
    }

    return json_decode(trim($m[1]), true) ?? [];
}

function measuredProduct(array $variant = []): Product
{
    // firstOrCreate, not create: a test that builds two products would
    // otherwise collide on the unique slug rather than on its assertion.
    $category = Category::firstOrCreate(['slug' => 'living-room'], ['name' => 'Living room']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'status' => 'active',
        'name' => 'Linen Sofa',
    ]);

    ProductVariant::factory()->create(array_merge([
        'product_id' => $product->id,
        'price' => 250,
        'width_cm' => 180,
        'depth_cm' => 90,
        'height_cm' => 75,
    ], $variant));

    return $product->fresh();
}

it('gives every page its own title, description and canonical', function () {
    $product = measuredProduct();

    foreach ([route('furnish.index'), route('furnish.products'), route('furnish.product.show', $product->slug)] as $url) {
        $html = $this->get($url)->assertOk()->getContent();

        expect($html)->toContain('<link rel="canonical" href="'.$url.'"');
        expect($html)->toContain('property="og:image"');
        expect($html)->toContain('hreflang="x-default"');
    }
});

it('publishes real measurements as machine-readable values', function () {
    // The whole reason structured data is worth doing here: almost no furniture
    // site can emit these because almost none record them.
    $product = measuredProduct();
    $schema = schemaOn(route('furnish.product.show', $product->slug));

    $node = collect($schema)->firstWhere('@type', 'Product');

    expect($node['width'])->toBe(['@type' => 'QuantitativeValue', 'value' => 180, 'unitCode' => 'CMT']);
    expect($node['depth']['value'])->toBe(90);
    expect($node['height']['value'])->toBe(75);
    expect($node['offers']['price'])->toBe('250.00');
});

it('omits dimensions it does not have rather than guessing them', function () {
    $product = measuredProduct(['width_cm' => null, 'depth_cm' => null, 'height_cm' => null]);
    $node = collect(schemaOn(route('furnish.product.show', $product->slug)))->firstWhere('@type', 'Product');

    expect($node)->not->toHaveKey('width');
    expect($node)->not->toHaveKey('depth');
    expect($node)->not->toHaveKey('height');
});

it('claims no rating when nobody has left one', function () {
    $product = measuredProduct();
    $node = collect(schemaOn(route('furnish.product.show', $product->slug)))->firstWhere('@type', 'Product');

    expect($node)->not->toHaveKey('aggregateRating');
});

it('counts only approved reviews in the rating it publishes', function () {
    $product = measuredProduct();

    foreach ([['approved', 5], ['approved', 3], ['pending', 1], ['rejected', 1]] as [$status, $rating]) {
        Review::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'rating' => $rating,
            'comment' => 'A comment.',
            'status' => $status,
        ]);
    }

    $node = collect(schemaOn(route('furnish.product.show', $product->slug)))->firstWhere('@type', 'Product');

    // A pending 1-star would drag the average to 2.25 and a rejected one to
    // 2.5. Only the two approved reviews count: (5 + 3) / 2.
    // json_encode writes 4.0 as `4`, so this comes back an int.
    expect((float) $node['aggregateRating']['ratingValue'])->toBe(4.0);
    expect($node['aggregateRating']['reviewCount'])->toBe(2);
});

it('reports availability from the real stock count', function () {
    // Written first as "omits availability when stock is not tracked", which
    // failed at the database rather than the assertion: `stock` is NOT NULL
    // DEFAULT 0, so there is no untracked state. 0 means none left, and saying
    // so is the honest answer rather than a cautious silence.
    $inStock = measuredProduct(['stock' => 4]);
    $node = collect(schemaOn(route('furnish.product.show', $inStock->slug)))->firstWhere('@type', 'Product');
    expect($node['offers']['availability'])->toBe('https://schema.org/InStock');

    $soldOut = measuredProduct(['stock' => 0]);
    $node = collect(schemaOn(route('furnish.product.show', $soldOut->slug)))->firstWhere('@type', 'Product');
    expect($node['offers']['availability'])->toBe('https://schema.org/OutOfStock');
});

it('serves a sitemap of pages that exist', function () {
    $product = measuredProduct();

    $xml = $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->getContent();

    expect($xml)->toContain(route('furnish.product.show', $product->slug));
    expect($xml)->toContain(route('furnish.category', 'living-room'));

    // A sitemap that lists a 404 is a sitemap a crawler stops trusting.
    preg_match_all('/<loc>(.*?)<\/loc>/', $xml, $m);

    foreach ($m[1] as $url) {
        $this->get($url)->assertOk();
    }
});

it('keeps delisted products out of the sitemap', function () {
    $product = measuredProduct();
    $product->update(['status' => 'inactive']);

    expect($this->get('/sitemap.xml')->getContent())
        ->not->toContain(route('furnish.product.show', $product->slug));
});

it('serves robots.txt from the application, pointing at the sitemap', function () {
    // A static public/robots.txt is served by the web server before Laravel
    // sees it, so it cannot know the sitemap's URL in any environment but the
    // one it was written in.
    $body = $this->get('/robots.txt')->assertOk()->getContent();

    expect($body)->toContain('Sitemap: '.route('sitemap'));
    expect($body)->toContain('Disallow: /admin');
    expect($body)->toContain('Disallow: /checkout');
});

it('never prints a translation key where a sentence belongs', function () {
    // Caught this twice by eye while wiring the descriptions up: `intro` was a
    // guess, and the real keys were `body`. A missing key renders as its own
    // name — "diwan.lookbook.intro" — which is what a search result would then
    // have shown, and no assertion about "does the page have a description"
    // would have noticed.
    $product = measuredProduct();

    $pages = [
        route('furnish.index'),
        route('furnish.products'),
        route('furnish.about'),
        route('furnish.lookbook'),
        route('furnish.contact'),
        route('furnish.testimonials'),
        route('furnish.product.show', $product->slug),
    ];

    foreach ($pages as $url) {
        foreach (['en', 'ar'] as $locale) {
            app()->setLocale($locale);
            $html = $this->get($url)->assertOk()->getContent();

            preg_match_all('/content="(diwan\.[a-z_.]+)"/', $html, $m);
            expect($m[1])->toBe([], "{$url} in {$locale} printed a raw key");

            preg_match('/<title>(.*?)<\/title>/s', $html, $t);
            expect($t[1] ?? '')->not->toContain('diwan.');
        }
    }
});

it('shows the shop mark in the tab, on a home screen and on the error pages', function () {
    foreach ([route('furnish.index'), route('login'), '/no-such-page'] as $url) {
        $html = $this->get($url)->getContent();

        expect($html)->toContain('rel="icon" href="'.asset('favicon.svg').'" type="image/svg+xml"')
            ->toContain('rel="apple-touch-icon"')
            ->toContain('rel="manifest"');
    }

    foreach (['favicon.ico', 'favicon.svg', 'apple-touch-icon.png', 'icon-192.png', 'icon-512.png', 'icon-maskable-512.png'] as $file) {
        expect(filesize(public_path($file)))->toBeGreaterThan(0, $file);
    }

    $manifest = json_decode(file_get_contents(public_path('site.webmanifest')), true);
    expect($manifest['theme_color'] ?? null)->not->toBeNull()
        ->and(collect($manifest['icons'])->pluck('src')->all())->toContain('/icon-maskable-512.png');
});
