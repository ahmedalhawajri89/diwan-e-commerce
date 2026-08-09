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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->roles()->whereIn('name', ['admin', 'Admin'])->exists()) {
            return $next($request);
        }

        // If not admin, redirect to home page, or show 403.
        return redirect()->route('furnish.index')->with('error', 'You do not have administrative privileges.');
    }
}
