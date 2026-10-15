<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\CompressResponse;
use App\Http\Middleware\ContentSecurityPolicy;
use App\Http\Middleware\PanelReadOnly;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\WesternDigits;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'admin', 'panel.read'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Outermost, so it compresses what every other layer has finished.
        $middleware->prepend(CompressResponse::class);

        // Behind a host's proxy (Vercel), the request reaches PHP as plain
        // http; trusting the proxy's forwarded headers keeps every URL https.
        // Only a deployment that names its proxies trusts any.
        if ($proxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : explode(',', $proxies));
        }

        // Runs on every web request so the locale is resolved before any
        // controller, view, or validator renders a translated string.
        //
        // ContentSecurityPolicy is prepended rather than appended: it mints
        // the nonce that the Vite helper and the views sign
        // their inline scripts with, so it has to run before anything renders.
        $middleware->web(
            prepend: [ContentSecurityPolicy::class],
            // Arabic-Indic digits typed into any field are read as digits.
            append: [SetLocale::class, WesternDigits::class],
        );

        $middleware->alias([
            'admin' => AdminAuth::class,
            'panel.read' => PanelReadOnly::class,
        ]);

        // The admin rail's folded state is written by the page's script and
        // read by the server, so the first paint is already the right width.
        // It holds one word and no secret; encrypting it would only stop the
        // script from writing it.
        $middleware->encryptCookies(except: ['admin_rail']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
