<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the session locale and return the visitor to where they were.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $supported = array_keys(config('localization.supported', []));

        if (! in_array($locale, $supported, true)) {
            abort(404);
        }

        $request->session()->put(config('localization.session_key', 'locale'), $locale);

        // back() falls back to the storefront when there is no referer —
        // never to a URL supplied by the request.
        return back(fallback: route('furnish.index'));
    }
}
