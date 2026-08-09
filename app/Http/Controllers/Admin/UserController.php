<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function destroy(User $user)
    {
        // Add protection logic if needed (e.g. don't delete admin yourself)
        if ($user->id === auth()->id()) {
            flash()->error('Cannot delete your own account.');
            return redirect()->route('admin.users.index');
        }

        $user->delete();
        flash()->success('User deleted successfully.');
        return redirect()->route('admin.users.index');
    }
}
