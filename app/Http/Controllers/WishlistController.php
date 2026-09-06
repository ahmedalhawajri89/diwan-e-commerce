<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    /**
     * Toggle a product in the wishlist (add/remove) via AJAX
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated', 'status' => 401], 401);
        }

        $user = Auth::user();
        $productId = $request->product_id;

        // Get or create wishlist for user
        $wishlist = Wishlist::firstOrCreate(['user_id' => $user->id]);

        // Check if item already exists in wishlist
        $existingItem = WishlistItem::where('wishlist_id', $wishlist->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            // Remove it
            $existingItem->delete();
            $action = 'removed';
            $message = 'Product removed from your wishlist.';
        } else {
            // Add it
            WishlistItem::create([
                'wishlist_id' => $wishlist->id,
                'product_id' => $productId,
            ]);
            $action = 'added';
            $message = 'Product added to your wishlist!';
        }

        $count = WishlistItem::where('wishlist_id', $wishlist->id)->count();

        return response()->json([
            'success' => true,
            'action' => $action,
            'message' => $message,
            'count' => $count,
        ]);
    }

    /**
     * Get the current wishlist items for the dropdown
     */
    public function getDropdownItems()
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'items' => [], 'count' => 0]);
        }

        $wishlist = Wishlist::where('user_id', Auth::id())->first();

        if (! $wishlist) {
            return response()->json(['success' => true, 'items' => [], 'count' => 0]);
        }

        $items = WishlistItem::with(['product' => function ($query) {
            $query->with(['images', 'variants']);
        }])
            ->where('wishlist_id', $wishlist->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedItems = $items->map(function ($item) {
            $product = $item->product;
            if (! $product) {
                return null;
            }

            $img = $product->images->first();
            $imgSrc = asset('assets/images/product-placeholder.jpg');
            if ($img) {
                if (str_starts_with($img->image_url, 'http')) {
                    $imgSrc = $img->image_url;
                } elseif (str_starts_with($img->image_url, 'assets/')) {
                    $imgSrc = asset($img->image_url);
                } else {
                    $imgSrc = asset('storage/'.$img->image_url);
                }
            }
            $price = $product->variants->first() ? $product->variants->first()->price : 50.00;

            return [
                'id' => $item->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $imgSrc,
                'price' => number_format($price, 2),
            ];
        })->filter();

        return response()->json([
            'success' => true,
            'items' => $formattedItems->values(),
            'count' => $items->count(),
        ]);
    }

    /**
     * Move an item from wishlist to cart
     */
    public function moveToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();
        $productId = $request->product_id;

        DB::beginTransaction();
        try {
            // Find the product in wishlist and remove it
            $wishlist = Wishlist::where('user_id', $user->id)->first();
            if ($wishlist) {
                WishlistItem::where('wishlist_id', $wishlist->id)
                    ->where('product_id', $productId)
                    ->delete();
            }

            // Get product variant and price
            $product = Product::with('variants')->findOrFail($productId);
            $variant = $product->variants->first();
            $variantId = $variant ? $variant->id : null;
            $price = $variant ? $variant->price : 50.00;

            // Add to Cart
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->where('product_variant_id', $variantId)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += 1;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'quantity' => 1,
                    'price' => $price,
                ]);
            }

            DB::commit();

            $cartCount = CartItem::where('cart_id', $cart->id)->sum('quantity');
            $wishlistCount = $wishlist ? WishlistItem::where('wishlist_id', $wishlist->id)->count() : 0;

            return response()->json([
                'success' => true,
                'message' => 'Moved to cart successfully!',
                'cart_count' => $cartCount,
                'wishlist_count' => $wishlistCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Move to cart failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while moving to cart.',
            ], 500);
        }
    }

    /**
     * Get the current wishlist items for a guest using an array of IDs
     */
    public function getGuestDropdownItems(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => true, 'items' => [], 'count' => 0]);
        }

        $products = Product::whereIn('id', $ids)->with(['images', 'variants'])->get();

        $formattedItems = $products->map(function ($product) {
            $img = $product->images->first();
            $imgSrc = asset('assets/images/product-placeholder.jpg');
            if ($img) {
                if (str_starts_with($img->image_url, 'http')) {
                    $imgSrc = $img->image_url;
                } elseif (str_starts_with($img->image_url, 'assets/')) {
                    $imgSrc = asset($img->image_url);
                } else {
                    $imgSrc = asset('storage/'.$img->image_url);
                }
            }
            $price = $product->variants->first() ? $product->variants->first()->price : 50.00;

            return [
                'id' => 'guest-'.$product->id, // dummy id
                'product_id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $imgSrc,
                'price' => number_format($price, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $formattedItems->values(),
            'count' => $products->count(),
        ]);
    }

    /**
     * Sync guest localStorage wishlist to database after login
     */
    public function sync(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false]);
        }

        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => true]);
        }

        $user = Auth::user();
        $wishlist = Wishlist::firstOrCreate(['user_id' => $user->id]);

        foreach ($ids as $productId) {
            // Check if valid product first (basic validation)
            if (Product::where('id', $productId)->exists()) {
                WishlistItem::firstOrCreate([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $productId,
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
