<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
                {{ __('Newsletter Subscribers') }}
            </h2>
            <button onclick="window.print()" class="btn-modern btn-outline-modern text-sm px-4 py-2">
                <i class="bi bi-printer me-2"></i> Print List
            </button>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
        <div class="card-modern border-0 overflow-hidden">
            <div class="p-0 overflow-x-auto">
                <table class="w-full whitespace-no-wrap border-collapse text-sm">
                    <thead>
                        <tr class="text-left font-bold text-muted bg-soft border-b border-soft tracking-wider uppercase text-xs">
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Email Address</th>
                            <th class="px-6 py-4">Subscribed At</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft">
                        @forelse ($subscribers as $subscriber)
                            <tr class="hover-bg-soft transition-colors duration-200">
                                <td class="px-6 py-4 font-bold text-muted">#{{ $subscriber->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xs">
                                            <i class="bi bi-envelope"></i>
                                        </div>
                                        <span class="font-bold text-main">{{ $subscriber->email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-muted">{{ $subscriber->created_at->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.newsletters.destroy', $subscriber->id) }}" method="POST" class="inline delete-form" data-confirm-message="Are you sure you want to remove this subscriber?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                            <i class="bi bi-trash fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-muted" colspan="4">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="bi bi-envelope-x fs-1 mb-3 opacity-50"></i>
                                        <p class="text-lg font-medium">No subscribers yet.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($subscribers->hasPages())
                <div class="px-6 py-4 border-t border-soft">
                    {{ $subscribers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
