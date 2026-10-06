<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Support\CurrentCart;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Everything the storefront's mega-menu bar needs.
 *
 * This used to be a `@php` block at the top of layouts/furnish.blade.php,
 * which meant two or three queries ran on every storefront page from inside
 * the view layer — invisible to the controllers, unreachable from a test, and
 * impossible to cache, because nothing outside the template knew they existed.
 *
 * A composer is the same code in the place the framework provides for it.
 * The queries are now named, cached where caching is honest, and can be
 * asserted on.
 */
class StorefrontNavComposer
{
    /**
     * The category row and its counts change only when the catalogue does, and
     * they are identical for every visitor — so they are cached and cleared on
     * write rather than recomputed a few thousand times a day.
     */
    public const CATEGORIES_KEY = 'diwan.nav.categories';

    /**
     * Long, because correctness here does not depend on the clock: the cache
     * is invalidated by the model events in CatalogueCacheObserver, not by
     * expiry. The TTL is only a backstop for a write that somehow escapes an
     * observer — a raw SQL update, or a seeder run against a warm cache.
     */
    public const CATEGORIES_TTL = 3600;

    public function compose(View $view): void
    {
        $view->with([
            'navCategories' => self::categories(),
            'cartCount' => CurrentCart::count(),
        ]);
    }

    /**
     * The catalogue's categories with the number of live products in each.
     *
     * @return Collection<int, Category>
     */
    public static function categories()
    {
        return Cache::remember(self::CATEGORIES_KEY, self::CATEGORIES_TTL, fn () => Category::withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get()
        );
    }
}
