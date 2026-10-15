<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // An administrator, or the demo viewer, whose every write PanelReadOnly turns back.
        if (auth()->check() && auth()->user()->seesPanel()) {
            return $next($request);
        }

        // If not admin, redirect to home page, or show 403.
        return redirect()->route('furnish.index')->with('error', __('diwan.flash.not_admin'));
    }
}
