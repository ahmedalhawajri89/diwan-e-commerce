<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * The storefront header: the way in from every page.
 */
function headerHtml(string $locale = 'en'): string
{
    $category = Category::create(['name' => 'Office', 'name_ar' => 'المكتب', 'slug' => 'office']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    return test()->withSession(['locale' => $locale])->get(route('furnish.about'))->getContent();
}

it('puts the browse panel right after its trigger, so Tab goes into it', function () {
    $html = headerHtml();

    $trigger = strpos($html, 'id="megaTrigger"');
    $panel = strpos($html, 'id="megaPanel"');
    $nextLink = strpos($html, '>'.__('diwan.nav.catalogue').'</a>');

    expect($trigger)->toBeLessThan($panel)
        ->and($panel)->toBeLessThan($nextLink);
});

it('brings the header back when focus enters it, and closes the panel when focus leaves', function () {
    expect(headerHtml())->toContain('@focusin="reveal"')->toContain('@focusout="focusLeft"');
});

it('offers the price bands the catalogue actually uses', function () {
    $html = headerHtml();

    expect($html)->toContain('prices%5B0%5D=under_500')
        ->not->toContain('under_100');
});

it('names the language link with the words on screen', function () {
    // Speech input says what it sees: "العربية", so that word must be in the
    // link's name, not replaced by "Language: Arabic".
    $html = headerHtml();

    expect($html)->toMatch('/<span class="sr-only">'.preg_quote(__('diwan.nav.language'), '/').':<\/span>\s*<span lang="ar">العربية<\/span>/')
        ->not->toContain('aria-label="'.__('diwan.nav.language').': Arabic"');
});

it('lists the rooms in the phone menu too', function () {
    $html = headerHtml('ar');

    preg_match('/<dialog id="mobileMenu".*?<\/dialog>/s', $html, $m);

    expect($m[0])->toContain(route('furnish.category', 'office'))->toContain('المكتب');
});

it('never parks a focused element under the sticky header', function () {
    expect(file_get_contents(resource_path('css/app.css')))->toContain('scroll-padding-top');
});

it('gives screen readers a voice for search results and for toasts, before anything is said', function () {
    $html = headerHtml();

    expect($html)->toContain('id="searchStatus"')
        ->toMatch('/<div id="toasts" aria-live="polite"/');
});

it('hands the scripts their failure words in the visitor\'s language', function () {
    $html = headerHtml('ar');

    preg_match('/<script id="diwan-data"[^>]*>(.*?)<\/script>/s', $html, $m);
    $data = json_decode(html_entity_decode($m[1]), true);

    expect($data['i18n']['sessionExpired'])->toBe(__('diwan.common.session_expired', [], 'ar'))
        ->and($data['i18n']['loadFailed'])->toBe(__('diwan.wishlist.load_failed', [], 'ar'))
        ->and($data['i18n']['bagCount'])->toContain('__COUNT__');
});
