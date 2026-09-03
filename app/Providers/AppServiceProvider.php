<?php

namespace App\Providers;

use App\Http\Controllers\RoomController;
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

        // The fit system has to be present on every surface a product appears
        // on, so the room is shared with every view rather than threaded
        // through each controller by hand.
        View::composer('*', function ($view) {
            $view->with('room', RoomController::current());
        });
    }
}
