<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * Each page worth finding has an address in its language (/ar/…, /en/…),
 * so an Arabic page can be indexed and shared as Arabic. The old addresses
 * still answer, with a permanent redirect; the bag, checkout and scripts
 * keep theirs.
 */
function localePiece(): Product
{
    $category = Category::create(['name' => 'Living Room', 'name_ar' => 'غرفة المعيشة', 'slug' => 'living-room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'name' => 'Cozy Sofa', 'name_ar' => 'كنبة مريحة', 'slug' => 'cozy-sofa']);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    return $product;
}

it('serves each page in the language its address names, whatever the session says', function () {
    localePiece();

    $this->withSession(['locale' => 'en'])->get('/ar/product/cozy-sofa')
        ->assertOk()->assertSee('dir="rtl"', false)->assertSee('كنبة مريحة');

    $this->withSession(['locale' => 'ar'])->get('/en/product/cozy-sofa')
        ->assertOk()->assertSee('Cozy Sofa')->assertDontSee('كنبة مريحة');
});

it('remembers the language of the last page, for the bag and checkout that have none', function () {
    $this->get('/ar/about')->assertSessionHas('locale', 'ar');

    $this->get(route('cart.index'))->assertSee('dir="rtl"', false);
});

it('sends an old address to the same page in the visitor\'s language, query and all', function () {
    localePiece();

    $this->get('/products?fit_only=1&sort=price_low')->assertStatus(301)->assertRedirect('/en/products?fit_only=1&sort=price_low');
    $this->get('/product/cozy-sofa')->assertStatus(301)->assertRedirect('/en/product/cozy-sofa');
    $this->withSession(['locale' => 'ar'])->get('/privacy')->assertRedirect('/ar/privacy');
    $this->withSession(['locale' => 'ar'])->get('/')->assertRedirect('/ar');
});

it('opens the shop in Arabic for a visitor who has chosen nothing', function () {
    config(['localization.default' => 'ar']);

    $this->get('/')->assertRedirect('/ar');
    $this->get('/about')->assertRedirect('/ar/about');
});

it('switches language and stays on the same page', function () {
    localePiece();

    $this->from('/ar/product/cozy-sofa?variant=1')->get(route('locale.switch', 'en'))
        ->assertRedirect('/en/product/cozy-sofa?variant=1');

    // A page without a language in its address: back where it was, the choice remembered.
    $this->from(route('cart.index'))->get(route('locale.switch', 'ar'))
        ->assertRedirect(route('cart.index'))->assertSessionHas('locale', 'ar');

    // Never to another site.
    $this->from('https://elsewhere.example/ar/x')->get(route('locale.switch', 'en'))
        ->assertRedirect('https://elsewhere.example/ar/x');
});

it('names each page\'s twin in the other language, and its own address as canonical', function () {
    localePiece();

    $this->get('/ar/product/cozy-sofa')
        ->assertSee('<link rel="canonical" href="'.url('/ar/product/cozy-sofa').'">', false)
        ->assertSee('hreflang="en" href="'.url('/en/product/cozy-sofa').'"', false)
        ->assertSee('hreflang="ar" href="'.url('/ar/product/cozy-sofa').'"', false)
        ->assertSee('<meta property="og:locale" content="ar_SA">', false);

    // x-default is the shop's own language, on a page in either.
    config(['localization.default' => 'ar']);
    $this->get('/en/product/cozy-sofa')->assertSee('hreflang="x-default" href="'.url('/ar/product/cozy-sofa').'"', false);
});

it('lists every page in both languages in the sitemap, each naming its twin', function () {
    localePiece();

    $xml = $this->get(route('sitemap'))->assertOk()->getContent();

    expect($xml)->toContain('<loc>'.url('/ar/product/cozy-sofa').'</loc>')
        ->toContain('<loc>'.url('/en/product/cozy-sofa').'</loc>')
        ->toContain('hreflang="x-default" href="'.url('/en/product/cozy-sofa').'"')
        ->not->toContain('/locale/');
});

it('keeps the addresses scripts and forms call', function () {
    expect(route('api.search'))->toEndWith('/api/search')
        ->and(route('cart.add'))->toEndWith('/cart/add')
        ->and(route('checkout'))->toEndWith('/checkout')
        ->and(route('room.store'))->toEndWith('/room');
});

it('refuses a language the shop does not speak', function () {
    $this->get('/fr/products')->assertNotFound();
});
