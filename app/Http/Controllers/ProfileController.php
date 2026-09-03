<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\ProfileUpdateRequest;
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
        if ($request->user()->roles()->whereIn('name', ['admin', 'Admin'])->exists()) {
            return view('profile.admin-edit', [
                'user' => $request->user(),
            ]);
        }

        $orders = Order::with('items')
            ->where('user_id', auth()->id())
            ->latest()
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
