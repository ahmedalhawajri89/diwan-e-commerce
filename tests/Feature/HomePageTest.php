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

it('asks for the room in the hero, then goes to the pieces that fit', function () {
    homeCatalogue();

    $this->get(route('furnish.index'))
        ->assertSee(__('diwan.home.room_card_cta'))
        ->assertSee('name="then" value="catalogue"', false);

    $this->post(route('room.store'), ['width' => 350, 'length' => 480, 'then' => 'catalogue'])
        ->assertRedirect(route('furnish.products', ['fit_only' => 1]));
});

it('goes nowhere it is not allowed to after saving the room', function () {
    $this->post(route('room.store'), ['width' => 350, 'length' => 480, 'then' => 'https://example.test'])
        ->assertSessionHasErrors('then');
});

it('counts in the hero how many pieces fit the visitor\'s room, by the fit rules', function () {
    $category = Category::factory()->create();
    foreach ([[40, 40, 50], [40, 45, 60], [900, 300, 80]] as [$w, $d, $h]) {
        $p = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
        ProductVariant::factory()->create(['product_id' => $p->id, 'width_cm' => $w, 'depth_cm' => $d, 'height_cm' => $h]);
    }

    $this->withSession(['diwan.room' => ['width' => 350, 'length' => 480]])
        ->get(route('furnish.index'))
        ->assertSee(trans_choice('diwan.home.fits_count', 2, ['size' => '350×480 cm']));
});

it('states what is true of every order, and nothing it has not set', function () {
    $html = $this->get(route('furnish.index'))->getContent();

    expect($html)->toContain(e(__('diwan.home.trust_pay.0')))
        ->toContain(e(__('diwan.home.trust_measure.0')))
        // No guarantee, delivery time or rating the shop has not set.
        ->not->toMatch('/\b\d+[- ]year|warranty|free delivery|★ ?4\.\d/i');
});

it('counts the pieces in each price band by their cheapest colour', function () {
    $category = Category::factory()->create();
    foreach ([[120], [800, 300], [2200]] as $prices) {
        $p = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
        foreach ($prices as $price) {
            ProductVariant::factory()->create(['product_id' => $p->id, 'price' => $price]);
        }
    }

    $bands = $this->get(route('furnish.index'))->viewData('bands');

    // 120 and the 300 colour of the second piece: two under 500; none between;
    // one above.
    expect($bands)->toBe(['under_500' => 2, '500_1500' => 0, 'above_1500' => 1]);
});

it('shows one room with its own pieces, and only real ones', function () {
    homeCatalogue();

    $response = $this->get(route('furnish.index'));
    $look = $response->viewData('look');

    expect($response->viewData('lookPieces')->pluck('category_id')->unique()->all())->toBe([$look->id])
        ->and($response->getContent())->toContain(__('diwan.home.look_note'));
});

it('answers the questions people ask before they buy', function () {
    $html = $this->get(route('furnish.index'))->getContent();

    foreach (__('diwan.home.faq') as [$q]) {
        expect($html)->toContain(e($q));
    }
});

it('welcomes a returning visitor back to what they were looking at', function () {
    homeCatalogue();
    $seen = Product::first();

    $this->get(route('furnish.product.show', $seen->slug));

    $this->get(route('furnish.index'))->assertSee(__('diwan.home.recent_title'));
});
