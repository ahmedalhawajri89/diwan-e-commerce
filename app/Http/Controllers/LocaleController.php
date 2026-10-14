<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch language and return the visitor to where they were: the same
     * page in the other language when the page has one in its address
     * (/ar/products → /en/products), else back where they came from, with
     * the choice remembered for pages without one (the bag, the checkout).
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $supported = array_keys(config('localization.supported', []));

        if (! in_array($locale, $supported, true)) {
            abort(404);
        }

        $request->session()->put(config('localization.session_key', 'locale'), $locale);

        $previous = url()->previous();
        $path = parse_url($previous, PHP_URL_PATH) ?? '/';
        $origin = parse_url(config('app.url'), PHP_URL_HOST);
        $sameSite = in_array(parse_url($previous, PHP_URL_HOST), [$origin, $request->getHost()], true);

        // Only an address on this site, and only its language segment is swapped.
        if ($sameSite && preg_match('#^/('.implode('|', $supported).')(/.*)?$#', $path, $m)) {
            $query = parse_url($previous, PHP_URL_QUERY);

            return redirect('/'.$locale.($m[2] ?? '').($query ? '?'.$query : ''));
        }

        // back() falls back to the storefront when there is no referer —
        // never to a URL supplied by the request.
        return back(fallback: route('furnish.index', ['locale' => $locale]));
    }
}
