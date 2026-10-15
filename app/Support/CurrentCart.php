<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The bag that belongs to whoever is looking.
 *
 * A signed-in shopper's bag is keyed on their account. A visitor's is keyed
 * on a random token held in their session, so they can fill a bag before
 * deciding whether to make an account — the decision is asked for at the
 * checkout, where it is needed, rather than at the first "Add to bag".
 */
final class CurrentCart
{
    public const SESSION_KEY = 'diwan.cart_token';

    /** The current bag, or null if this visitor has none yet. */
    public static function find(): ?Cart
    {
        if ($id = auth()->id()) {
            return Cart::where('user_id', $id)->first();
        }

        $token = session(self::SESSION_KEY);

        return $token ? Cart::where('token', $token)->whereNull('user_id')->first() : null;
    }

    /** The current bag, made on first use. */
    public static function findOrCreate(): Cart
    {
        if ($id = auth()->id()) {
            return Cart::firstOrCreate(['user_id' => $id]);
        }

        if ($cart = self::find()) {
            return $cart;
        }

        $token = Str::random(48);
        session([self::SESSION_KEY => $token]);

        return Cart::create(['token' => $token]);
    }

    /** An item in the current bag, or a 404 — never an item in someone else's. */
    public static function item(int|string $id): CartItem
    {
        $cart = self::find();

        abort_unless($cart, 404);

        return $cart->items()->findOrFail($id);
    }

    /**
     * Total pieces in the current bag, for the header's count.
     *
     * Deliberately never cached: a bag count is per-person and has to be right
     * the instant after it changes. One aggregate query, no cart row fetched.
     */
    public static function count(): int
    {
        $query = Cart::join('cart_items', 'cart_items.cart_id', '=', 'carts.id');

        if ($id = auth()->id()) {
            $query->where('carts.user_id', $id);
        } elseif ($token = session(self::SESSION_KEY)) {
            $query->where('carts.token', $token)->whereNull('carts.user_id');
        } else {
            return 0;
        }

        return (int) $query->sum('cart_items.quantity');
    }

    /**
     * On sign-in, the visitor's bag becomes the account's: lines for the same
     * piece in the same colour add their quantities, the rest move across.
     */
    public static function adopt(User $user, ?string $token): void
    {
        if (! $token) {
            return;
        }

        $guest = Cart::with('items')->where('token', $token)->whereNull('user_id')->first();

        if (! $guest) {
            return;
        }

        DB::transaction(function () use ($guest, $user) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guest->items as $item) {
                $line = $cart->items()
                    ->where('product_id', $item->product_id)
                    ->where('product_variant_id', $item->product_variant_id)
                    ->first();

                if ($line) {
                    $line->increment('quantity', $item->quantity);
                    $item->delete();
                } else {
                    $item->update(['cart_id' => $cart->id]);
                }
            }

            $guest->delete();
        });
    }
}
