<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Support\CurrentCart;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function index()
    {
        $cart = CurrentCart::find()?->load(['items.product.images', 'items.product.category', 'items.variant']);

        return view('furnish.cart', compact('cart'));
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
            session()->flash('error', __('diwan.flash.no_price'));

            return redirect()->back();
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
            session()->flash('error', $variant->stock > 0
                ? __('diwan.flash.stock_short', ['count' => $variant->stock])
                : __('diwan.product.out_of_stock'));

            return redirect()->back()->withInput();
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

        session()->flash('success', __('diwan.flash.bag_added'));

        return redirect()->back();
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
        $cartItem = CurrentCart::item($id);

        $cartItem->delete();

        session()->flash('success', __('diwan.flash.bag_removed'));

        return redirect()->route('cart.index');
    }
}
