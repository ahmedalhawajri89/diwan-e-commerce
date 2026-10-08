<?php

namespace App\Providers;

use App\Http\Controllers\RoomController;
use App\Models\Category;
use App\Models\Contact;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Observers\ActivityObserver;
use App\Observers\CatalogueCacheObserver;
use App\Support\Activity;
use App\Support\AdminAlerts;
use App\Support\Ai\ClaudeDescriptionDrafter;
use App\Support\Ai\ClaudeShoppingAssistant;
use App\Support\Ai\DescriptionDrafter;
use App\Support\Ai\NullDescriptionDrafter;
use App\Support\Ai\NullShoppingAssistant;
use App\Support\Ai\ShoppingAssistant;
use App\Support\CurrentCart;
use App\View\Composers\AdminNavComposer;
use App\View\Composers\StorefrontNavComposer;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
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
        // Description drafts: Claude when a key is configured, otherwise a
        // drafter that says the feature is not set up (and the panel hides it).
        $this->app->bind(DescriptionDrafter::class, function () {
            $key = config('services.anthropic.key');

            return filled($key)
                ? new ClaudeDescriptionDrafter($key, (string) config('services.anthropic.model'))
                : new NullDescriptionDrafter;
        });

        // «Ask Diwan»: the same rule.
        $this->app->bind(ShoppingAssistant::class, function () {
            $key = config('services.anthropic.key');

            return filled($key)
                ? new ClaudeShoppingAssistant($key, (string) config('services.anthropic.model'))
                : new NullShoppingAssistant;
        });
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
            // The way into the home, for the passage check. Not `$route`,
            // which would read as the router's.
            $view->with('wayIn', RoomController::route());
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

        // An administrator signing in, and a failed attempt on an
        // administrator's address, are in the activity log with where they
        // came from. A customer's sign-in is not.
        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                Activity::record('signed_in', changes: ['agent' => mb_substr((string) request()->userAgent(), 0, 160)], actor: $event->user);
            }
        });
        Event::listen(Failed::class, function (Failed $event) {
            $target = $event->user instanceof User
                ? $event->user
                : User::where('email', $event->credentials['email'] ?? null)->first();

            if ($target) {
                Activity::record('sign_in_failed', actor: $target);
            }
        });

        // A new customer is news to the panel. Registration is the only way
        // in that is not an administrator's own doing.
        Event::listen(Registered::class, function (Registered $event) {
            AdminAlerts::notify('customer_registered', [
                'user_id' => $event->user->getKey(),
                'name' => $event->user->name,
            ]);
        });

        Category::observe(CatalogueCacheObserver::class);
        Product::observe(CatalogueCacheObserver::class);
        // The browse panel shows prices and photographs: a change to either
        // must clear it too.
        ProductVariant::observe(CatalogueCacheObserver::class);
        ProductImage::observe(CatalogueCacheObserver::class);

        // Every model the panel edits: what an administrator did to it goes
        // in the activity log (App\Support\Activity decides who counts).
        foreach ([Product::class, ProductVariant::class, ProductImage::class, Category::class, Order::class,
            Review::class, User::class, Contact::class, NewsletterSubscriber::class] as $model) {
            $model::observe(ActivityObserver::class);
        }
    }
}
