<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * An address from before the pages carried their language ("/products",
 * "/product/oak-table", "/"): a permanent redirect to the same page under
 * the visitor's language, which SetLocale has already worked out (the
 * session's, or the shop's own, Arabic). The query string comes along, so a
 * shared link to a filtered catalogue still lands on it.
 */
class LocalizedRedirectController extends Controller
{
    public function __invoke(Request $request, ?string $path = null): RedirectResponse
    {
        $target = '/'.app()->getLocale().($path ? '/'.$path : '');
        $query = $request->getQueryString();

        return redirect($target.($query ? '?'.$query : ''), 301);
    }
}
