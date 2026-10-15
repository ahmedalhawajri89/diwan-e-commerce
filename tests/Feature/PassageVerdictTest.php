<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

/**
 * The way-in verdict where a customer decides: the piece's page, its card,
 * the bag and the checkout. Always with its reason in the visitor's numbers,
 * and never a "will not get in" the shop cannot stand behind.
 */
function wayInPiece(string $name, array $variant): Product
{
    $category = Category::firstOrCreate(['slug' => 'living-room'], ['name' => 'Living Room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'name' => $name, 'slug' => str($name)->slug()]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 500, 'stock' => 5] + $variant);

    return $product;
}

// An ISO 4190-1 lift with an 80 cm door, a wide front door, and a balcony.
function liftHome(array $more = []): array
{
    return $more + [
        'access' => 'lift', 'door_w' => '95', 'door_h' => '210',
        'lift_door_w' => '80', 'lift_door_h' => '200', 'lift_w' => '110', 'lift_d' => '140', 'lift_h' => '220',
    ];
}

it('asks for the way in on a piece\'s page until it has one', function () {
    $sofa = wayInPiece('Deep sofa', ['width_cm' => 220, 'depth_cm' => 95, 'height_cm' => 85, 'arrives' => 'assembled']);

    $this->get(route('furnish.product.show', $sofa->slug))
        ->assertSee(__('diwan.passage.legend'))
        ->assertSee(__('diwan.passage.ask'));
});

it('says why a piece will not get in, in the visitor\'s own numbers', function () {
    $sofa = wayInPiece('Deep sofa', ['width_cm' => 220, 'depth_cm' => 95, 'height_cm' => 85, 'arrives' => 'assembled']);
    $this->post(route('room.route.store'), liftHome());

    $this->get(route('furnish.product.show', $sofa->slug))
        ->assertSee(__('diwan.passage.no'))
        ->assertSee(__('diwan.passage.explain.no', ['where' => __('diwan.passage.where.lift_door'), 'need' => 85, 'room' => 80, 'gap' => -5]))
        ->assertSee(__('diwan.passage.by_yours'));
});

it('offers the hoist when the balcony takes what the lift will not', function () {
    $sofa = wayInPiece('Deep sofa', ['width_cm' => 220, 'depth_cm' => 95, 'height_cm' => 85, 'arrives' => 'assembled']);
    $this->post(route('room.route.store'), liftHome(['balcony_w' => '120', 'balcony_h' => '210']));

    $this->get(route('furnish.product.show', $sofa->slug))->assertSee(__('diwan.passage.hoist'));
});

it('does not call a piece too big while it may come apart', function () {
    $sofa = wayInPiece('Deep sofa', ['width_cm' => 220, 'depth_cm' => 95, 'height_cm' => 85]);   // arrives: not said
    $this->post(route('room.route.store'), liftHome());

    $this->get(route('furnish.product.show', $sofa->slug))
        ->assertDontSee(__('diwan.passage.no'))
        ->assertSee(__('diwan.passage.unknown'))
        ->assertSee(__('diwan.passage.explain.may_disassemble', ['where' => __('diwan.passage.where.lift_door'), 'need' => 85, 'room' => 80, 'gap' => -5]));
});

it('walks a piece in by its largest part when it comes apart', function () {
    $bed = wayInPiece('Oak bed', [
        'width_cm' => 170, 'depth_cm' => 210, 'height_cm' => 110,
        'arrives' => 'flat', 'carry_w_cm' => 40, 'carry_d_cm' => 200, 'carry_h_cm' => 15,
    ]);
    $this->post(route('room.route.store'), liftHome());

    $this->get(route('furnish.product.show', $bed->slug))
        ->assertSee(__('diwan.passage.passes'))
        ->assertSee(__('diwan.passage.carried', ['size' => '40×200×15']));
});

it('names the pieces that may not get in, in the bag and at checkout', function () {
    $sofa = wayInPiece('Deep sofa', ['width_cm' => 220, 'depth_cm' => 95, 'height_cm' => 85, 'arrives' => 'assembled']);
    $stool = wayInPiece('Oak stool', ['width_cm' => 40, 'depth_cm' => 40, 'height_cm' => 45, 'arrives' => 'assembled']);

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.add'), ['product_id' => $sofa->id, 'quantity' => 1]);
    $this->post(route('cart.add'), ['product_id' => $stool->id, 'quantity' => 1]);

    // No way in yet: an offer to check it.
    $this->get(route('cart.index'))->assertSee(__('diwan.passage.bag_unset'))->assertDontSee(__('diwan.passage.bag_title'));

    $this->post(route('room.route.store'), liftHome());

    foreach ([route('cart.index'), route('checkout')] as $page) {
        $html = $this->get($page)->assertSee(__('diwan.passage.bag_title'))->getContent();
        $note = str($html)->after(__('diwan.passage.bag_title'))->before('</ul>');
        expect((string) $note)->toContain('Deep sofa')->not->toContain('Oak stool');
    }
});

it('shows a card\'s way-in badge only when there is something to warn about', function () {
    wayInPiece('Deep sofa', ['width_cm' => 220, 'depth_cm' => 95, 'height_cm' => 85, 'arrives' => 'assembled']);
    wayInPiece('Oak stool', ['width_cm' => 40, 'depth_cm' => 40, 'height_cm' => 45, 'arrives' => 'assembled']);

    // No way in: no prompt on every card.
    $this->get(route('furnish.products'))->assertDontSee(__('diwan.passage.ask'));

    $this->post(route('room.route.store'), liftHome());
    $html = $this->get(route('furnish.products'))->getContent();

    expect(substr_count($html, __('diwan.passage.no')))->toBe(1)
        ->and($html)->not->toContain(__('diwan.passage.passes'));
});
