<?php

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
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
        // The caller's values win: `+` keeps the left-hand keys.
        ProductVariant::factory()->create($v + ['product_id' => $product->id, 'width_cm' => 200, 'depth_cm' => 90, 'height_cm' => 80]);
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

it('offers each colour as a link, and shows the chosen one', function () {
    $product = pagePiece([
        ['color' => 'Grey', 'price' => 1499, 'stock' => 10],
        ['color' => 'Navy', 'price' => 1599, 'stock' => 0],
    ]);
    $navy = $product->variants()->where('color', 'Navy')->sole();

    $html = pageHtml($product, '?variant='.$navy->id);

    expect($html)->toContain('?variant='.$navy->id.'#buy')
        ->toMatch('/aria-current="true"[^>]*>\s*Navy/')
        ->toContain('1,599.00')
        ->toContain('name="variant_id" value="'.$navy->id.'"')
        // Sold out says so in words, not only with a strike-through.
        ->toContain(__('diwan.product.colour_sold_out', ['colour' => 'Navy']));
});

it('names colours in Arabic on the Arabic site', function () {
    $product = pagePiece([
        ['color' => 'Grey', 'price' => 1499, 'stock' => 10],
        ['color' => 'Navy', 'price' => 1499, 'stock' => 10],
    ]);

    expect(pageHtml($product, '', 'ar'))->toContain('رمادي')->toContain('كحلي');
});

it('falls back to the default colour for an id that is not this piece\'s', function () {
    $product = pagePiece();
    $other = pagePiece([['color' => 'Olive', 'price' => 99, 'stock' => 3]]);

    $html = pageHtml($product, '?variant='.$other->variants->first()->id);

    // The form carries this piece's own default colour, not the foreign id.
    expect($html)->toContain('name="variant_id" value="'.$product->variants->first()->id.'"')
        ->not->toContain('name="variant_id" value="'.$other->variants->first()->id.'"');
});

it('puts the chosen colour in the bag, at its own price', function () {
    $product = pagePiece([
        ['color' => 'Grey', 'price' => 1499, 'stock' => 10],
        ['color' => 'Navy', 'price' => 1599, 'stock' => 10],
    ]);
    $navy = $product->variants()->where('color', 'Navy')->sole();

    $this->post(route('cart.add'), ['product_id' => $product->id, 'variant_id' => $navy->id, 'quantity' => 1]);

    $line = CartItem::sole();
    expect($line->product_variant_id)->toBe($navy->id)
        ->and((float) $line->price)->toBe(1599.0);
});

it('refuses a colour that belongs to another piece', function () {
    $product = pagePiece();
    $other = pagePiece([['color' => 'Olive', 'price' => 99, 'stock' => 3]]);

    $this->post(route('cart.add'), [
        'product_id' => $product->id,
        'variant_id' => $other->variants->first()->id,
        'quantity' => 1,
    ])->assertSessionHasErrors('variant_id');

    expect(CartItem::count())->toBe(0);
});

it('says how many are left when the bag would hold more than there are', function () {
    $product = pagePiece([['color' => 'Grey', 'price' => 1499, 'stock' => 2]]);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2]);
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertSessionHas('error', __('diwan.flash.stock_short', ['count' => 2]));

    expect(CartItem::sole()->quantity)->toBe(2);
});

it('will not raise a bag line past the stock', function () {
    $product = pagePiece([['color' => 'Grey', 'price' => 1499, 'stock' => 3]]);
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
    $line = CartItem::sole();

    $this->put(route('cart.update', $line->id), ['quantity' => 9])
        ->assertSessionHas('error', __('diwan.flash.stock_short', ['count' => 3]));

    expect($line->fresh()->quantity)->toBe(1);
});

it('steps the quantity with buttons, up to the stock, without submitting', function () {
    $html = pageHtml(pagePiece([['color' => 'Grey', 'price' => 1499, 'stock' => 4]]));

    expect($html)->toContain('x-data="stepper"')
        ->toMatch('/name="quantity"[^>]*max="4"/')
        ->not->toContain('data-autosubmit');
});

it('lays every photograph on a swipeable track, each served responsively', function () {
    $product = pagePiece();
    foreach ([1, 2, 3] as $n) {
        ProductImage::create([
            'product_id' => $product->id,
            'image_url' => "assets/images/product-sofa-{$n}.jpg",
            'is_primary' => $n === 1,
        ]);
    }

    $html = pageHtml($product);

    expect($html)->toContain('x-data="gallery"')
        ->toContain('snap-x')
        // Three slides, three thumbnails, three in the viewer.
        ->and(substr_count($html, 'data-index='))->toBe(3)
        ->and($html)->toContain('id="lightbox"')
        ->toContain(__('diwan.gallery.enlarge'))
        ->toContain(__('diwan.gallery.photo_n', ['n' => 2, 'count' => 3]))
        // No plain <img id="heroImage">: every slide is a <picture>.
        ->not->toContain('heroImage');
});

it('shows one photograph without thumbnails or a counter', function () {
    $product = pagePiece();
    ProductImage::create(['product_id' => $product->id, 'image_url' => 'assets/images/product-sofa-1.jpg', 'is_primary' => true]);

    $html = pageHtml($product);

    expect(substr_count($html, 'data-index='))->toBe(0)
        ->and($html)->toContain('id="lightbox"');
});

it('keeps Add to bag within reach on a phone, through the same form', function () {
    $html = pageHtml(pagePiece());

    expect($html)->toContain('x-data="stickyBuy"')
        ->toContain('data-watch="addToBag"')
        ->toContain('form="buyForm"')
        ->toContain('id="buyForm"');
});

it('offers no sticky bar for a piece that cannot be bought', function () {
    $html = pageHtml(pagePiece([['color' => 'Grey', 'price' => 1499, 'stock' => 0]]));

    expect($html)->not->toContain('x-data="stickyBuy"');
});

it('says how delivery and payment work, and promises nothing it does not know', function () {
    $html = pageHtml(pagePiece([['color' => 'Grey', 'price' => 1499, 'stock' => 3, 'width_cm' => 220, 'depth_cm' => 95, 'height_cm' => 85]]));

    expect($html)->toContain(__('diwan.product.delivery_title'))
        ->toContain(__('diwan.checkout.cod'))
        ->toContain(__('diwan.bag.shipping_on_contact'))
        // Over 80 cm on every face: warned, with the reason.
        ->toContain(__('diwan.fit.note.doorway'));
});

it('writes the unit after the figures in Arabic, outside the left-to-right run', function () {
    $html = pageHtml(pagePiece(), '', 'ar');

    expect($html)->toMatch('/<bdi dir="ltr"[^>]*>200 &times; 90 &times; 80<\/bdi> سم/');
});
