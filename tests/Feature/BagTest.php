<?php

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * The bag page: changing it, emptying it, and what it offers when empty.
 */
function bagPiece(string $name = 'Cozy Sofa', int $stock = 10, ?string $colour = 'Navy'): Product
{
    $category = Category::firstOrCreate(['slug' => 'living-room'], ['name' => 'Living Room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'name' => $name]);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 500, 'stock' => $stock, 'color' => $colour]);

    return $product;
}

it('offers to put a removed piece back, exactly as it was', function () {
    $product = bagPiece();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3]);
    $line = CartItem::sole();

    $this->delete(route('cart.remove', $line->id))->assertRedirect(route('cart.index'));

    $html = $this->get(route('cart.index'))->getContent();
    expect(CartItem::count())->toBe(0)
        ->and($html)->toContain(e(__('diwan.bag.removed', ['name' => 'Cozy Sofa'])))
        ->toContain(__('diwan.bag.undo'))
        ->toContain('name="quantity" value="3"');

    // Put it back: the same piece, colour and quantity.
    $this->post(route('cart.add'), [
        'product_id' => $product->id,
        'variant_id' => $line->product_variant_id,
        'quantity' => 3,
    ]);

    $back = CartItem::sole();
    expect($back->quantity)->toBe(3)->and($back->product_variant_id)->toBe($line->product_variant_id);
});

it('changes a quantity only when Update is pressed', function () {
    $this->post(route('cart.add'), ['product_id' => bagPiece()->id, 'quantity' => 1]);

    $html = $this->get(route('cart.index'))->getContent();

    expect($html)->not->toContain('data-autosubmit')
        ->toContain('x-data="stepper"')
        ->toContain(__('diwan.bag.update'));
});

it('names each line\'s controls by its piece', function () {
    $this->post(route('cart.add'), ['product_id' => bagPiece('Oak Bench')->id, 'quantity' => 1]);

    $this->get(route('cart.index'))
        ->assertSee(__('diwan.bag.remove_named', ['name' => 'Oak Bench']))
        ->assertSee(__('diwan.bag.quantity_of', ['name' => 'Oak Bench']));
});

it('warns on the line itself when stock has fallen below the quantity', function () {
    $product = bagPiece(stock: 5);
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 4]);
    $product->variants()->update(['stock' => 2]);   // sold elsewhere meanwhile

    $this->get(route('cart.index'))->assertSee(__('diwan.bag.over_stock', ['count' => 2]));
});

it('names the colour in the visitor\'s language', function () {
    $this->post(route('cart.add'), ['product_id' => bagPiece()->id, 'quantity' => 1]);

    $this->withSession(['locale' => 'ar'])->get(route('cart.index'))->assertSee('كحلي');
});

it('offers what the visitor looked at when the bag is empty', function () {
    $seen = bagPiece('Walnut Desk');
    $this->get(route('furnish.product.show', $seen->slug));

    $this->get(route('cart.index'))
        ->assertSee(__('diwan.bag.empty'))
        ->assertSee(__('diwan.product.recently_viewed'))
        ->assertSee('Walnut Desk');
});

it('says the total is before delivery', function () {
    $this->post(route('cart.add'), ['product_id' => bagPiece()->id, 'quantity' => 1]);

    $this->get(route('cart.index'))->assertSee(__('diwan.bag.grand'));
    expect(__('diwan.bag.grand'))->toContain('delivery');
});

it('answers the page\'s own Add to bag with what the drawer shows', function () {
    $product = bagPiece('Oak Bench');

    $this->postJson(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2])
        ->assertOk()
        ->assertJsonPath('count', 2)
        ->assertJsonPath('item.name', 'Oak Bench')
        ->assertJsonPath('item.quantity', 2)
        ->assertJsonPath('subtotal', '1,000.00');
});

it('refuses through the drawer in words, with nothing added', function () {
    $product = bagPiece(stock: 1);

    $this->postJson(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3])
        ->assertStatus(422)
        ->assertJsonPath('message', __('diwan.flash.stock_short', ['count' => 1]));

    expect(CartItem::count())->toBe(0);
});

it('still adds by a plain form post, for a page without its script', function () {
    $product = bagPiece();

    $this->from(route('furnish.product.show', $product->slug))
        ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertRedirect(route('furnish.product.show', $product->slug))
        ->assertSessionHas('success');
});

it('marks the product page\'s form for the drawer, and puts the drawer in the page', function () {
    $product = bagPiece();

    $this->get(route('furnish.product.show', $product->slug))
        ->assertSee('data-bag-form', false)
        ->assertSee('id="bagDrawer"', false);
});
