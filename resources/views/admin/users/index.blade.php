<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
        <div class="card-modern border-0 overflow-hidden">
            <div class="p-0 overflow-x-auto">
                <table class="w-full whitespace-no-wrap border-collapse text-sm">
                    <thead>
                        <tr class="text-left font-bold text-muted bg-soft border-b border-soft tracking-wider uppercase text-xs">
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">User Details</th>
                            <th class="px-6 py-4">Role / Status</th>
                            <th class="px-6 py-4">Joined At</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft">
                        @forelse ($users as $user)
                            <tr class="hover-bg-soft transition-colors duration-200">
                                <td class="px-6 py-4 font-bold text-muted">#{{ $user->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full overflow-hidden bg-primary bg-opacity-10 flex items-center justify-center text-primary font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-main">{{ $user->name }}</div>
                                            <div class="text-xs text-muted">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->roles->whereIn('name', ['admin', 'Admin'])->count() > 0)
                                        <span class="px-3 py-1 text-[10px] uppercase tracking-wider font-bold rounded-pill bg-purple-100 text-purple-700">Admin</span>
                                    @else
                                        <span class="px-3 py-1 text-[10px] uppercase tracking-wider font-bold rounded-pill bg-blue-100 text-blue-700">Customer</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline delete-form" data-confirm-message="Are you sure you want to delete this user?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors {{ $user->id === auth()->id() ? 'opacity-50 cursor-not-allowed' : '' }}" title="Delete" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <i class="bi bi-trash fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-muted" colspan="5">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="bi bi-people fs-1 mb-3 opacity-50"></i>
                                        <p class="text-lg font-medium">No users found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-soft">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
