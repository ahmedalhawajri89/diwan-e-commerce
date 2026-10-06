<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Support\CurrentCart;
use App\Support\RecentlyViewed;
use App\Support\Responsive;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function index()
    {
        $cart = CurrentCart::find()?->load(['items.product.images', 'items.product.category', 'items.variant']);

        // An empty bag offers the pieces this visitor already looked at,
        // rather than only a link back to the whole catalogue.
        $recent = ($cart?->items->isNotEmpty()) ? collect() : RecentlyViewed::products(limit: 4);

        return view('furnish.cart', compact('cart', 'recent'));
    }

    public function add(Request $request)
    {

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1|max:999',
            // A colour of THIS piece, or none, which means its default.
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where('product_id', $request->integer('product_id')),
            ],
        ]);

        $product = Product::with('variants')->findOrFail($request->product_id);

        // The variant the product page shows, so the bag charges the price the
        // shopper read. A piece with no variant has no price, and a price the
        // shop does not know is not one it may invent: it cannot be bought.
        $variant = $request->filled('variant_id')
            ? $product->variants->firstWhere('id', $request->integer('variant_id'))
            : $product->primaryVariant();

        if (! $variant) {
            return $this->refuse($request, __('diwan.flash.no_price'));
        }

        $price = $variant->price;
        $variantId = $variant->id;

        $cart = CurrentCart::findOrCreate();

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variantId)
            ->first();

        // Stock is checked here, not only at the checkout: a shopper should
        // learn a piece is nearly gone when they add it, not at the last step.
        $wanted = ($cartItem?->quantity ?? 0) + $request->integer('quantity');

        if ($variant->stock !== null && $wanted > $variant->stock) {
            return $this->refuse($request, $variant->stock > 0
                ? __('diwan.flash.stock_short', ['count' => $variant->stock])
                : __('diwan.product.out_of_stock'));
        }

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'quantity' => $request->quantity,
                'price' => $price,
            ]);
        }

        // Asked by the page's script: answer with what the bag drawer shows,
        // and no page reload. Asked by a plain form: redirect back, as ever.
        if ($request->wantsJson()) {
            $line = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variantId)
                ->first();
            $cart->load('items');

            return response()->json([
                'count' => CurrentCart::count(),
                'subtotal' => number_format($cart->items->sum(fn ($i) => $i->price * $i->quantity), 2),
                'item' => [
                    'name' => $product->name,
                    'colour' => $variant->colourLabel(),
                    // The smallest derived file, not the 1600px original: the
                    // drawer draws it 96px wide.
                    'image' => Responsive::smallest($product->imageUrl()),
                    'url' => route('furnish.product.show', $product->slug),
                    'price' => number_format((float) $price, 2),
                    'quantity' => $line->quantity,
                ],
            ]);
        }

        session()->flash('success', __('diwan.flash.bag_added'));

        return redirect()->back();
    }

    /** A refusal, in the form the asker can show: JSON for the drawer, a flash otherwise. */
    private function refuse(Request $request, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 422);
        }

        session()->flash('error', $message);

        return redirect()->back()->withInput();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:999',
        ]);

        $cartItem = CurrentCart::item($id);
        $stock = $cartItem->variant?->stock;

        if ($stock !== null && $request->integer('quantity') > $stock) {
            session()->flash('error', __('diwan.flash.stock_short', ['count' => $stock]));

            return redirect()->route('cart.index');
        }

        $cartItem->update(['quantity' => $request->quantity]);

        session()->flash('success', __('diwan.flash.bag_updated'));

        return redirect()->route('cart.index');
    }

    public function remove($id)
    {
        $cartItem = CurrentCart::item($id)->load('product');

        // Undo rather than "are you sure?": the bag offers to put the line
        // back exactly as it was, through the same Add to bag route.
        session()->flash('bag_undo', [
            'name' => $cartItem->product?->name,
            'product_id' => $cartItem->product_id,
            'variant_id' => $cartItem->product_variant_id,
            'quantity' => $cartItem->quantity,
        ]);

        $cartItem->delete();

        return redirect()->route('cart.index');
    }
}
