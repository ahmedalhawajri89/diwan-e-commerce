<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the users (Admin only).
     */
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user (Admin only).
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string',
        ]);

        $user = User::create($validated);

        return redirect()->route('users.show', $user)
                        ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // منع تعديل البيانات الأساسية للمشرف المحمي
        if ($user->isProtected()) {
            return redirect()->back()
                            ->with('error', 'لا يمكن تعديل بيانات المشرف النظام');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
        ]);

        $user->update($validated);

        return redirect()->route('users.show', $user)
                        ->with('success', 'تم تحديث البيانات بنجاح');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // منع حذف المستخدم المحمي
        if ($user->isProtected()) {
            return redirect()->back()
                            ->with('error', 'لا يمكن حذف المشرف النظام المحمي');
        }

        // منع حذف المستخدم الحالي
        if (auth()->id() === $user->id) {
            return redirect()->back()
                            ->with('error', 'لا يمكن حذف حسابك الخاص');
        }

        $user->roles()->detach(); // حذف الأدوار المرتبطة
        $user->delete();

        return redirect()->route('users.index')
                        ->with('success', 'تم حذف المستخدم بنجاح');
    }
}

