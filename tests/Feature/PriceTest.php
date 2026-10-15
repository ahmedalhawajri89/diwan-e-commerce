<?php

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;

/**
 * The bag charges the price the shopper read, and nothing else.
 *
 * Two faults, both in the till:
 *   - a piece with no variant read "Price on request" on its page, and the bag
 *     then stored it at 50.00 — a number nobody had set;
 *   - the page showed the cheapest variant, and the bag charged whichever
 *     variant happened to be first by id.
 */
function piece(array $prices = []): Product
{
    $category = Category::firstOrCreate(['slug' => 'living-room'], ['name' => 'Living room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);

    foreach ($prices as $price) {
        ProductVariant::factory()->create(['product_id' => $product->id, 'price' => $price, 'stock' => 10]);
    }

    return $product;
}

it('will not put a piece with no price in the bag', function () {
    $product = piece();

    $this->actingAs(User::factory()->create())
        ->from(route('furnish.product.show', $product->slug))
        ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertRedirect()
        ->assertSessionHas('error', __('diwan.flash.no_price'));

    expect(CartItem::count())->toBe(0);
});

it('charges the price the product page shows', function () {
    // Created dearest first, so "first by id" and "cheapest" disagree.
    $product = piece([480, 320]);

    $this->actingAs(User::factory()->create())
        ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    expect((float) CartItem::sole()->price)->toBe(320.0);
});

it('keeps an unpriced piece saved rather than moving it to the bag', function () {
    $user = User::factory()->create();
    $product = piece();
    $wishlist = Wishlist::create(['user_id' => $user->id]);
    WishlistItem::create(['wishlist_id' => $wishlist->id, 'product_id' => $product->id]);

    $this->actingAs($user)
        ->postJson(route('wishlist.moveToCart'), ['product_id' => $product->id])
        ->assertStatus(422)
        ->assertJson(['success' => false, 'message' => __('diwan.flash.no_price')]);

    expect(CartItem::count())->toBe(0)
        ->and(WishlistItem::count())->toBe(1);
});

it('lists an unpriced saved piece without inventing a price', function () {
    $user = User::factory()->create();
    $product = piece();
    $wishlist = Wishlist::create(['user_id' => $user->id]);
    WishlistItem::create(['wishlist_id' => $wishlist->id, 'product_id' => $product->id]);

    $this->actingAs($user)
        ->getJson(route('wishlist.dropdown'))
        ->assertJsonPath('items.0.price', null);
});

it('disables add to bag when the piece has no price', function () {
    $product = piece();

    $html = $this->actingAs(User::factory()->create())
        ->get(route('furnish.product.show', $product->slug))
        ->assertSee(__('diwan.product.no_price'))
        ->getContent();

    preg_match('/<form[^>]*action="'.preg_quote(route('cart.add'), '/').'".*?<\/form>/s', $html, $m);

    // The attribute, not the `disabled:` utility class the button always carries.
    preg_match('/<button[^>]*type="submit"[^>]*>/', $m[0], $button);
    expect($button[0])->toMatch('/\sdisabled(?:[\s>=])/');
});

it('promises no delivery price the shop has not set', function () {
    $user = User::factory()->create();
    $product = piece([200]);

    $this->actingAs($user)->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    foreach (['en', 'ar'] as $locale) {
        $this->withSession(['locale' => $locale])
            ->get(route('cart.index'))
            ->assertSee(__('diwan.bag.shipping_on_contact', [], $locale))
            ->assertDontSee($locale === 'en' ? '>Free<' : '>مجاني<', false);
    }
});
