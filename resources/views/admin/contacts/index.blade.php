<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
            {{ __('Contact Messages') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
        <div class="card-modern border-0 overflow-hidden">
            <div class="p-0 overflow-x-auto">
                <table class="w-full whitespace-no-wrap border-collapse text-sm">
                    <thead>
                        <tr class="text-left font-bold text-muted bg-soft border-b border-soft tracking-wider uppercase text-xs">
                            <th class="px-6 py-4">Sender</th>
                            <th class="px-6 py-4">Subject</th>
                            <th class="px-6 py-4">Message</th>
                            <th class="px-6 py-4">Date</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft">
                        @forelse ($messages as $msg)
                            <tr class="hover-bg-soft transition-colors duration-200">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-main">{{ $msg->name }}</div>
                                    <a href="mailto:{{ $msg->email }}" class="text-xs text-primary hover:underline">{{ $msg->email }}</a>
                                </td>
                                <td class="px-6 py-4 text-muted font-medium">{{ $msg->subject ?? 'No Subject' }}</td>
                                <td class="px-6 py-4 text-muted max-w-xs truncate" title="{{ $msg->message }}">
                                    {{ \Illuminate\Support\Str::limit($msg->message, 50) }}
                                </td>
                                <td class="px-6 py-4 text-muted text-xs">{{ $msg->created_at->format('M d, Y h:i A') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.contacts.destroy', $msg->id) }}" method="POST" class="inline delete-form" data-confirm-message="Are you sure you want to delete this message?">
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
                                <td class="px-6 py-12 text-center text-muted" colspan="5">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="bi bi-envelope-open fs-1 mb-3 opacity-50"></i>
                                        <p class="text-lg font-medium">No messages found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($messages->hasPages())
                <div class="px-6 py-4 border-t border-soft">
                    {{ $messages->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
