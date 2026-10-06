<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;

/**
 * The home page: every number on it is read from the catalogue, and the
 * buyer's quote appears only when a real, approved review exists.
 */
function homeCatalogue(): void
{
    foreach (['Sofas' => 3, 'Tables' => 2, 'Lamps' => 1] as $name => $live) {
        $category = Category::factory()->create(['name' => $name, 'slug' => strtolower($name)]);

        Product::factory()->count($live)->create(['category_id' => $category->id, 'status' => 'active'])
            ->each(fn ($p) => ProductVariant::factory()->create(['product_id' => $p->id, 'price' => 150]));

        // A draft never counts towards a tile.
        Product::factory()->create(['category_id' => $category->id, 'status' => 'draft']);
    }

    // A category with nothing live is not a destination.
    Category::factory()->create(['name' => 'Empty shelf', 'slug' => 'empty-shelf']);
}

it('shows each category tile with its real count of live pieces', function () {
    homeCatalogue();

    $tiles = $this->get(route('furnish.index'))->assertOk()->viewData('categoryTiles');

    expect($tiles->pluck('products_count', 'name')->all())
        ->toBe(['Sofas' => 3, 'Tables' => 2, 'Lamps' => 1]);
});

it('leaves the quote out when there is no approved review', function () {
    homeCatalogue();

    Review::create([
        'product_id' => Product::first()->id,
        'user_id' => User::factory()->create()->id,
        'rating' => 2,
        'comment' => 'Still waiting on moderation.',
        'status' => 'pending',
    ]);

    $this->get(route('furnish.index'))
        ->assertOk()
        ->assertDontSee('Still waiting on moderation.')
        ->assertDontSee(__('diwan.home.quote_label'));
});

it('quotes the latest approved review', function () {
    homeCatalogue();

    Review::create([
        'product_id' => Product::first()->id,
        'user_id' => User::factory()->create(['name' => 'Rana'])->id,
        'rating' => 5,
        'comment' => 'It fits the alcove exactly as the drawing said it would.',
        'status' => 'approved',
    ]);

    $this->get(route('furnish.index'))
        ->assertOk()
        ->assertSee('It fits the alcove exactly as the drawing said it would.')
        ->assertSee('Rana');
});

it('renders in Arabic, right to left, with no raw translation keys', function () {
    homeCatalogue();

    $html = $this->withSession(['locale' => 'ar'])
        ->get(route('furnish.index'))
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee('تسوّق حسب القطعة')
        ->getContent();

    // Visible text only: a key like `diwan.home.x` left untranslated.
    $text = strip_tags(preg_replace('/<script\b.*?<\/script>/s', '', $html));

    expect($text)->not->toMatch('/\bdiwan\.[a-z_]+\.[a-z_]+/');
});

it('opens every modal with a native dialog, not Bootstrap', function () {
    $html = $this->get(route('furnish.index'))->assertOk()->getContent();

    foreach (['roomModal', 'searchModal', 'mobileMenu'] as $id) {
        expect($html)->toMatch('/<dialog id="'.$id.'"/');
        expect($html)->toContain('data-open-dialog="'.$id.'"');
    }

    expect($html)->not->toContain('data-bs-toggle');
});
