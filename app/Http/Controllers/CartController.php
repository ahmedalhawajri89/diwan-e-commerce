<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::with(['items.product.images', 'items.product.category', 'items.variant'])
            ->where('user_id', auth()->id())
            ->first();

        return view('furnish.cart', compact('cart'));
    }

    public function add(Request $request)
    {

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::with('variants')->findOrFail($request->product_id);

        $variant = $product->variants->first();
        $price = $variant ? $variant->price : 50.00;
        $variantId = $variant ? $variant->id : null;

        $cart = Cart::firstOrCreate(['user_id' => auth()->id()]);

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

        flash()->success('Product added to cart successfully!');

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::whereHas('cart', function ($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $cartItem->update(['quantity' => $request->quantity]);

        flash()->success('Cart updated successfully!');

        return redirect()->route('cart.index');
    }

    public function remove($id)
    {
        $cartItem = CartItem::whereHas('cart', function ($query) {
            $query->where('user_id', auth()->id());
        })->findOrFail($id);

        $cartItem->delete();

        flash()->success('Product removed from cart!');

        return redirect()->route('cart.index');
    }
}
