<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * The request's language: the one in its address (/ar/…, /en/…) when it
     * has one, else the visitor's last choice, else the shop's own (Arabic).
     * An address in a language becomes the visitor's choice, so the bag and
     * the checkout, which keep plain addresses, follow the page they came
     * from. Anything not listed in config/localization.php is ignored: a
     * locale from user input is never passed to App::setLocale unvalidated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('localization.supported', []));
        $sessionKey = config('localization.session_key', 'locale');
        $route = $request->route();

        // Only a page whose address begins with its language. The switcher's
        // own {locale} (/locale/en) names where to go, not where it is.
        $prefixed = $route && str_starts_with($route->uri(), '{locale}');
        $fromUrl = $prefixed ? $route->parameter('locale') : null;
        $locale = in_array($fromUrl, $supported, true) ? $fromUrl : $request->session()->get($sessionKey);

        if (! in_array($locale, $supported, true)) {
            $locale = config('localization.default');
        }

        if ($fromUrl && $request->session()->get($sessionKey) !== $locale) {
            $request->session()->put($sessionKey, $locale);
        }

        app()->setLocale($locale);

        // Carbon drives translatedFormat() in the masthead's issue line.
        Carbon::setLocale($locale);

        // Every link to a page with a language stays in this one, without
        // each call to route() naming it.
        URL::defaults(['locale' => $locale]);

        // The controllers take the page's own parameters ($slug, $page); the
        // language has been read, and must not arrive in their place.
        if ($prefixed) {
            $route->forgetParameter('locale');
        }

        return $next($request);
    }
}
