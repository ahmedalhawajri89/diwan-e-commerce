<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Support\CurrentCart;
use Illuminate\Http\Request;

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
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::with('variants')->findOrFail($request->product_id);

        // The variant the product page shows, so the bag charges the price the
        // shopper read. A piece with no variant has no price, and a price the
        // shop does not know is not one it may invent: it cannot be bought.
        $variant = $product->primaryVariant();

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
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CurrentCart::item($id);

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
