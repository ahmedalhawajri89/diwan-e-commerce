<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The demo account sees the whole panel and changes nothing. Any request
 * that would write (a POST, PUT, PATCH or DELETE) from a viewer is turned
 * back with a sentence saying so; an administrator is never stopped here.
 */
class PanelReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isViewer() && ! $user->isAdmin() && ! $request->isMethodSafe()) {
            $message = __('admin.demo.read_only');

            return $request->expectsJson()
                ? response()->json(['message' => $message], 403)
                : back(fallback: route('admin.dashboard'))->with('error', $message);
        }

        return $next($request);
    }
}
