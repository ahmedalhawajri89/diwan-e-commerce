<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-main leading-tight tracking-tight">
                {{ __('Product Reviews') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pb-12">
        <div class="card-modern border-0 overflow-hidden">
            <div class="p-0 overflow-x-auto">
                <table class="w-full whitespace-no-wrap border-collapse text-sm">
                    <thead>
                        <tr class="text-left font-bold text-muted bg-soft border-b border-soft tracking-wider uppercase text-xs">
                            <th class="px-6 py-4">ID</th>
                            <th class="px-6 py-4">Product</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4">Rating & Review</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-soft">
                        @forelse ($reviews as $review)
                            <tr class="hover-bg-soft transition-colors duration-200">
                                <td class="px-6 py-4 font-bold text-muted">#{{ $review->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-main">{{ $review->product->name ?? 'Deleted Product' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-main">{{ $review->user->name ?? 'Guest' }}</div>
                                    <div class="text-xs text-muted">{{ $review->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4" style="max-width: 300px;">
                                    <div class="text-accent mb-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="bi bi-star-fill"></i>
                                            @else
                                                <i class="bi bi-star text-muted opacity-25"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="text-muted text-xs truncate" title="{{ $review->comment }}">{{ Str::limit($review->comment, 50) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-[10px] uppercase tracking-wider font-bold rounded-pill 
                                        {{ $review->status == 'approved' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $review->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $review->status == 'rejected' ? 'bg-red-100 text-red-700' : '' }}">
                                        {{ $review->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2 items-center">
                                        @if($review->status !== 'approved')
                                        <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Approve">
                                                <i class="bi bi-check-circle fs-5"></i>
                                            </button>
                                        </form>
                                        @endif

                                        @if($review->status !== 'rejected')
                                        <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Reject">
                                                <i class="bi bi-x-circle fs-5"></i>
                                            </button>
                                        </form>
                                        @endif

                                        <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="inline delete-form" data-confirm-message="Are you sure you want to delete this review?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                                <i class="bi bi-trash fs-5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-muted" colspan="6">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="bi bi-star fs-1 mb-3 opacity-50"></i>
                                        <p class="text-lg font-medium">No reviews found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($reviews->hasPages())
                <div class="px-6 py-4 border-t border-soft">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
