<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Support\CurrentCart;

/**
 * A visitor could not put anything in a bag: every bag route sat behind
 * `auth`, and the product page offered "Sign in first" in place of "Add to
 * bag". The account is now asked for at the checkout, where it is needed.
 */
function guestPiece(int $price = 300): Product
{
    $category = Category::firstOrCreate(['slug' => 'living-room'], ['name' => 'Living room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => $price, 'stock' => 10]);

    return $product;
}

it('lets a visitor fill a bag without an account', function () {
    $product = guestPiece();

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2])
        ->assertSessionHas('success');

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee($product->name)
        ->assertSee(__('diwan.bag.guest_note'));

    expect(Cart::sole()->user_id)->toBeNull();
});

it('offers a visitor Add to bag rather than a sign-in wall', function () {
    $product = guestPiece();

    $this->get(route('furnish.product.show', $product->slug))
        ->assertSee(route('cart.add'), false)
        ->assertSee(__('diwan.product.add_to_bag'));
});

it('counts the visitor\'s bag in the header', function () {
    $product = guestPiece();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3]);

    expect(CurrentCart::count())->toBe(3);
});

it('keeps one visitor\'s bag out of another\'s reach', function () {
    $product = guestPiece();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
    $item = CartItem::sole();

    // A second visitor: a fresh session, no token.
    $this->flushSession();

    $this->delete(route('cart.remove', $item->id))->assertNotFound();
    expect(CartItem::count())->toBe(1);
});

it('sends a visitor to sign in at the checkout, then back to it', function () {
    $this->get(route('checkout'))->assertRedirect(route('login'));
});

it('brings the visitor\'s bag into their account when they sign in', function () {
    $sofa = guestPiece(500);
    $lamp = guestPiece(80);

    $user = User::factory()->create(['password' => bcrypt('secret-pass')]);

    // Already in the account's bag: one sofa.
    $cart = Cart::create(['user_id' => $user->id]);
    $cart->items()->create([
        'product_id' => $sofa->id,
        'product_variant_id' => $sofa->primaryVariant()->id,
        'quantity' => 1,
        'price' => 500,
    ]);

    // As a visitor: two more sofas and a lamp.
    $this->post(route('cart.add'), ['product_id' => $sofa->id, 'quantity' => 2]);
    $this->post(route('cart.add'), ['product_id' => $lamp->id, 'quantity' => 1]);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'secret-pass']);

    expect(Cart::count())->toBe(1)
        ->and(Cart::sole()->user_id)->toBe($user->id)
        ->and(CartItem::where('product_id', $sofa->id)->sole()->quantity)->toBe(3)
        ->and(CartItem::where('product_id', $lamp->id)->sole()->quantity)->toBe(1);
});
