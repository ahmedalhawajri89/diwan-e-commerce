<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function destroy(User $user): RedirectResponse
    {
        // The seeded system administrator is flagged `is_protected`, and
        // User::canBeDeleted() has always said so — nothing was asking. One
        // admin deleting the protected account left the install with no
        // guaranteed way back in.
        if (! $user->canBeDeleted()) {
            flash()->error(__('That account is protected and cannot be deleted.'));

            return redirect()->route('admin.users.index');
        }

        if ($user->id === auth()->id()) {
            flash()->error(__('Cannot delete your own account.'));

            return redirect()->route('admin.users.index');
        }

        // Detach roles first so the pivot rows do not outlive the user.
        $user->roles()->detach();
        $user->delete();

        flash()->success(__('User deleted successfully.'));

        return redirect()->route('admin.users.index');
    }
}
