<?php

namespace App\Observers;

use App\Http\Controllers\FurnishController;
use App\View\Composers\StorefrontNavComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Clears the cached navigation and the home page's shelves whenever the
 * catalogue behind them changes.
 *
 * The nav caches categories *and the number of active products in each*, so
 * it goes stale on two different kinds of edit:
 *
 *   * a category is added, renamed or removed, and
 *   * a product's `status` or `category_id` changes, because that moves a
 *     product in or out of a count without touching a category row at all.
 *
 * The second is the one a TTL alone would get wrong in a way people notice:
 * an operator deactivates a product and the menu keeps advertising it for the
 * rest of the hour. Registered on both models for that reason.
 *
 * `deleted` covers a soft delete too, and `restored` puts the row back.
 */
class CatalogueCacheObserver
{
    public function saved(Model $model): void
    {
        $this->flush();
    }

    public function deleted(Model $model): void
    {
        $this->flush();
    }

    public function restored(Model $model): void
    {
        $this->flush();
    }

    private function flush(): void
    {
        Cache::forget(StorefrontNavComposer::CATEGORIES_KEY);
        Cache::forget(StorefrontNavComposer::PREVIEWS_KEY);
        Cache::forget(FurnishController::SHELVES_KEY);
    }
}
