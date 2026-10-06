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
        ->assertSee(__('diwan.home.tiles_title', [], 'ar'))
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

it('shows what is new without repeating what is most saved', function () {
    homeCatalogue();

    $response = $this->get(route('furnish.index'));
    $featured = $response->viewData('featuredProducts')->modelKeys();
    $newest = $response->viewData('newest')->modelKeys();

    expect(array_intersect($featured, $newest))->toBe([])
        ->and($response->getContent())->toContain(__('diwan.home.new_title'));
});

it('says how ordering works, in the steps the checkout actually takes', function () {
    $html = $this->get(route('furnish.index'))->getContent();

    foreach (__('diwan.home.order_steps') as [$title]) {
        expect($html)->toContain(e($title));
    }
});

it('shows up to three real reviews, and none it does not have', function () {
    homeCatalogue();

    expect($this->get(route('furnish.index'))->getContent())->not->toContain(__('diwan.home.reviews_title'));

    $product = Product::first();
    foreach (['One.', 'Two.', 'Three.', 'Four.'] as $comment) {
        Review::create(['product_id' => $product->id, 'user_id' => User::factory()->create()->id,
            'rating' => 5, 'comment' => $comment, 'status' => 'approved']);
    }
    Review::create(['product_id' => $product->id, 'user_id' => User::factory()->create()->id,
        'rating' => 1, 'comment' => 'Not read yet.', 'status' => 'pending']);

    $html = $this->get(route('furnish.index'))->getContent();

    expect(substr_count($html, '<blockquote'))->toBe(3)
        ->and($html)->toContain('Four.')->not->toContain('One.')->not->toContain('Not read yet.');
});

it('moves the new pieces on a measured rail, not a browser scrollbar', function () {
    homeCatalogue();

    $html = $this->get(route('furnish.index'))->getContent();

    preg_match('/<section x-data="rail".*?<\/section>/s', $html, $m);

    expect($m[0] ?? '')->toContain('x-ref="tape"')
        ->toContain('[scrollbar-width:none]')
        ->toContain(__('diwan.rail.previous'))
        ->toContain(__('diwan.rail.next'))
        ->toContain('aria-roledescription="'.__('diwan.rail.role').'"')
        ->not->toContain('[scrollbar-width:thin]');
});
