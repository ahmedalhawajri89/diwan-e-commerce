<?php

namespace App\Providers;

use App\Http\Controllers\RoomController;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Observers\CatalogueCacheObserver;
use App\Support\CurrentCart;
use App\View\Composers\AdminNavComposer;
use App\View\Composers\StorefrontNavComposer;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
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

        // The pager is the shop's own, in Tailwind, with its words in the
        // shop's own translations rather than Laravel's English defaults.
        Paginator::defaultView('vendor.pagination.diwan');
        Paginator::defaultSimpleView('vendor.pagination.diwan-simple');

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
        // The two account emails in the shop's own shell and the visitor's
        // language. They were Laravel's stock template: English, left to
        // right, signed "Regards, Laravel".
        ResetPassword::toMailUsing(fn ($user, string $token) => (new MailMessage)
            ->subject(__('diwan.mail.reset.subject'))
            ->view('emails.account-link', [
                'kind' => 'reset',
                'user' => $user,
                'url' => route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]),
            ]));

        VerifyEmail::toMailUsing(fn ($user, string $url) => (new MailMessage)
            ->subject(__('diwan.mail.verify.subject'))
            ->view('emails.account-link', ['kind' => 'verify', 'user' => $user, 'url' => $url]));

        // A visitor's bag becomes their account's the moment they sign in or
        // register (registering signs them in, so one event covers both).
        Event::listen(Login::class, function (Login $event) {
            CurrentCart::adopt($event->user, session(CurrentCart::SESSION_KEY));
            session()->forget(CurrentCart::SESSION_KEY);
        });

        Category::observe(CatalogueCacheObserver::class);
        Product::observe(CatalogueCacheObserver::class);
        // The browse panel shows prices and photographs: a change to either
        // must clear it too.
        ProductVariant::observe(CatalogueCacheObserver::class);
        ProductImage::observe(CatalogueCacheObserver::class);
    }
}
