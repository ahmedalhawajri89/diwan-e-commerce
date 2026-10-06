<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * The pieces this visitor has opened, newest first.
 *
 * Kept in the session only: it never reaches an account and nothing else
 * reads it. The product page writes it; the product page and the empty bag
 * read it.
 */
final class RecentlyViewed
{
    public const SESSION_KEY = 'diwan.viewed';

    private const KEEP = 9;

    /** Record a visit: this piece moves to the front. */
    public static function push(int $productId): void
    {
        $ids = collect(session(self::SESSION_KEY, []))
            ->reject(fn ($id) => $id === $productId)
            ->prepend($productId)
            ->take(self::KEEP)
            ->values();

        session([self::SESSION_KEY => $ids->all()]);
    }

    /**
     * The remembered pieces that are still live, in the order they were seen,
     * leaving out any id in $except.
     *
     * @param  array<int>  $except
     * @return Collection<int, Product>
     */
    public static function products(array $except = [], int $limit = 8): Collection
    {
        $ids = collect(session(self::SESSION_KEY, []))->diff($except)->take($limit)->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Product::with(['category', 'images', 'variants'])
            ->where('status', 'active')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($product) => $ids->search($product->id))
            ->values();
    }
}
