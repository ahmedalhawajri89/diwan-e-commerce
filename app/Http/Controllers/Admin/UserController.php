<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Support\AdminList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /** The ways the list can be ordered, first is the default. */
    public const SORTS = ['newest', 'name', 'most_orders'];

    public const ROLES = ['customer', 'admin'];

    public function index(Request $request): View
    {
        [$users, $filters, $chips] = $this->listing($request);

        $users = $users->paginate(15)->withQueryString();
        $sorts = AdminList::sorts(self::SORTS, $filters['sort']);

        return view('admin.users.index', ['users' => $users, 'chips' => $chips, 'sorts' => $sorts] + $filters);
    }

    /** The list as the address asks for it; the export reads the same. */
    private function listing(Request $request): array
    {
        $role = AdminList::choice($request, 'role', self::ROLES);
        $q = AdminList::term($request);
        $sort = AdminList::choice($request, 'sort', self::SORTS, self::SORTS[0]);

        $base = AdminList::search(User::query(), $q, ['name', 'email']);

        // A role is a pivot row, not a column, so its chips are counted here
        // rather than by AdminList::chips().
        $chips = [[
            'value' => null, 'label' => __('admin.list.all'), 'count' => (clone $base)->count(),
            'url' => $request->fullUrlWithQuery(['role' => null, 'page' => null]), 'active' => $role === null,
        ]];
        foreach (self::ROLES as $name) {
            $chips[] = [
                'value' => $name, 'label' => __('admin.users.role_values.'.$name),
                'count' => (clone $base)->whereHas('roles', fn ($r) => $r->where('name', $name))->count(),
                'url' => $request->fullUrlWithQuery(['role' => $name, 'page' => null]), 'active' => $role === $name,
            ];
        }

        $users = (clone $base)->with('roles')->withCount('orders')
            ->when($role, fn ($w) => $w->whereHas('roles', fn ($r) => $r->where('name', $role)))
            ->when($sort === 'newest', fn ($w) => $w->latest()->orderByDesc('id'))
            ->when($sort === 'name', fn ($w) => $w->orderBy('name'))
            ->when($sort === 'most_orders', fn ($w) => $w->orderByDesc('orders_count'));

        return [$users, compact('role', 'q', 'sort'), $chips];
    }

    public function show(User $user): View
    {
        $user->load('roles');

        $orders = Order::withCount('items')
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.show', compact('user', 'orders'));
    }

    public function edit(User $user): View
    {
        // A protected account's roles are the one thing that must not move: an
        // admin who demotes the protected administrator locks everyone out.
        abort_if($user->isProtected(), 403);

        return view('admin.users.edit', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isProtected(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        // An admin removing their own admin role would lock themselves out of
        // the panel one redirect later, so that specific change is refused.
        if ($user->id === auth()->id()) {
            $adminRoleIds = Role::whereIn('name', ['admin', 'Admin'])->pluck('id');
            $keepsAdmin = collect($validated['roles'] ?? [])->intersect($adminRoleIds)->isNotEmpty();

            if (! $keepsAdmin) {
                // Attached to the roles field rather than flashed loose, so the
                // message appears beside the control that caused it and the
                // form comes back with what was typed.
                return back()->withInput()->withErrors([
                    'roles' => __('admin.users.cannot_demote_self'),
                ]);
            }
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->roles()->sync($validated['roles'] ?? []);

        session()->flash('success', __('admin.users.saved'));

        return redirect()->route('admin.users.show', $user);
    }

    public function destroy(User $user): RedirectResponse
    {
        // The seeded system administrator is flagged `is_protected`, and
        // User::canBeDeleted() has always said so — nothing was asking. One
        // admin deleting the protected account left the install with no
        // guaranteed way back in.
        if (! $user->canBeDeleted()) {
            session()->flash('error', __('admin.users.protected_note'));

            return redirect()->route('admin.users.index');
        }

        if ($user->id === auth()->id()) {
            session()->flash('error', __('admin.users.cannot_delete_self'));

            return redirect()->route('admin.users.index');
        }

        // Detach roles first so the pivot rows do not outlive the user.
        $user->roles()->detach();
        $user->delete();

        session()->flash('success', __('admin.users.deleted'));

        return redirect()->route('admin.users.index');
    }
}
