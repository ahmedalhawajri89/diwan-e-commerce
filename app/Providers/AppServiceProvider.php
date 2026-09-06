<?php

namespace App\Providers;

use App\Http\Controllers\RoomController;
use App\Models\Category;
use App\Models\Product;
use App\Observers\CatalogueCacheObserver;
use App\View\Composers\AdminNavComposer;
use App\View\Composers\StorefrontNavComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Laravel paginates with its Tailwind view by default. This project
        // dropped Tailwind when the admin layout moved onto the shop's bundle,
        // and nothing told the paginator — so every paginated page has been
        // rendering unstyled Tailwind classes, and the view's inline chevron
        // SVGs, sized only by `w-5 h-5`, were drawing at full page width.
        //
        // _hallmark.scss already styles `.page-link` and `.page-item`, which
        // is the Bootstrap 5 paginator's markup. It was written for it; it
        // just was never switched on.
        Paginator::useBootstrapFive();

        // The fit system has to be present on every surface a product appears
        // on, so the room is shared with every view rather than threaded
        // through each controller by hand. Session-backed, so this costs no
        // query however many components ask for it.
        View::composer('*', function ($view) {
            $view->with('room', RoomController::current());
        });

        // The two layouts used to open with a `@php` block of queries. They
        // are bound here instead: a template that queries is a template no
        // controller can see and no test can arrange.
        View::composer('layouts.furnish', StorefrontNavComposer::class);
        View::composer('layouts.app', AdminNavComposer::class);

        // The nav's cached category counts read a product's status and its
        // category, so both models have to invalidate it.
        Category::observe(CatalogueCacheObserver::class);
        Product::observe(CatalogueCacheObserver::class);
    }
}
