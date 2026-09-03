<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Resolve the request's locale from the session, falling back to the
     * app default. Anything not listed in config/localization.php is
     * ignored — a locale from user input is never passed to App::setLocale
     * unvalidated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('localization.supported', []));
        $sessionKey = config('localization.session_key', 'locale');

        $locale = $request->session()->get($sessionKey);

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        // Carbon drives translatedFormat() in the masthead's issue line.
        Carbon::setLocale($locale);

        return $next($request);
    }
}
