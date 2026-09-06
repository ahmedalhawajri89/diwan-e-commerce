<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        if ($request->user()->isAdmin()) {
            return view('profile.admin-edit', [
                'user' => $request->user(),
            ]);
        }

        // Five, and without their items: the profile shows a short recent list
        // that prints each order's number, date and status, and links out to
        // the full history for the rest.
        //
        // This query used to be thrown away. The view ran its own `@php` block
        // that shadowed `$orders` with almost the same query, so every profile
        // page loaded every order the customer had ever placed, eager loaded
        // all of their line items, and then rendered a different result set.
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        return view('profile.edit', [
            'user' => $request->user(),
            'orders' => $orders,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // The admin panel refuses to delete a protected account, but this route
        // did not — so the protected system administrator could simply delete
        // themselves from their own profile page and leave the install with no
        // guaranteed way back in. The guard belongs on both doors.
        if (! $user->canBeDeleted()) {
            return Redirect::route('profile.edit')
                ->with('error', __('diwan.profile.protected'));
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
