<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\ContentSecurityPolicy;
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
            Route::middleware(['web', 'auth', 'admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
