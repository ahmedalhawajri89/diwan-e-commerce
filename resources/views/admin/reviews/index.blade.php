{{--
  The moderation queue. Nothing reaches the shop until it is approved here,
  which is why the shop can promise it invents no testimonials.
--}}
@php
    $tone = fn ($s) => ['approved' => 'ok', 'rejected' => 'danger'][$s] ?? 'warn';
@endphp

<x-app-layout>
    <x-slot name="title">{{ __('admin.reviews.title') }}</x-slot>

    <x-admin.head :title="__('admin.reviews.title')" :intro="__('admin.reviews.intro')" />

    <x-admin.toolbar :action="route('admin.reviews.index')" :q="$q" :placeholder="__('admin.reviews.search')"
                     :chips="$chips" :sorts="$sorts" :keep="['status' => $status, 'sort' => $sort]" />

    @if($reviews->count() > 0)
        <x-admin.table>
            <thead>
                <tr>
                    <th scope="col">{{ __('admin.reviews.author') }}</th>
                    <th scope="col">{{ __('admin.reviews.product') }}</th>
                    <th scope="col">{{ __('admin.reviews.rating') }}</th>
                    <th scope="col">{{ __('admin.reviews.comment') }}</th>
                    <th scope="col">{{ __('admin.reviews.status') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('admin.action.edit') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $review)
                    <tr>
                        <td class="whitespace-nowrap">{{ $review->user?->name ?? '—' }}</td>
                        <td>
                            @if($review->product)
                                <a href="{{ route('furnish.product.show', $review->product->slug) }}" class="text-ink underline underline-offset-4">{{ $review->product->name }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><x-star-rating :rating="$review->rating" class="m-0 whitespace-nowrap" /></td>
                        <td class="min-w-64 max-w-md text-sm leading-body">{{ $review->comment }}</td>
                        <td><x-ui.chip :tone="$tone($review->status)">{{ __('admin.reviews.values.'.$review->status) }}</x-ui.chip></td>
                        <td>
                            {{-- Named for whose review it is; it asked to delete "Reviews". --}}
                            <x-admin.row-actions :name="__('admin.reviews.by', ['name' => $review->user?->name ?? '—'])" :delete="route('admin.reviews.destroy', $review)">
                                @if($review->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="approved">
                                        <x-ui.button type="submit" variant="quiet" class="hover:text-ok!" aria-label="{{ __('admin.reviews.approve') }}">
                                            <x-icon name="check-lg" class="size-4" />
                                        </x-ui.button>
                                    </form>
                                @endif
                                @if($review->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="rejected">
                                        <x-ui.button type="submit" variant="quiet" class="hover:text-warn!" aria-label="{{ __('admin.reviews.reject') }}">
                                            <x-icon name="slash-circle" class="size-4" />
                                        </x-ui.button>
                                    </form>
                                @endif
                            </x-admin.row-actions>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-admin.table>

        <div class="mt-10">{{ $reviews->onEachSide(1)->links() }}</div>
    @else
        <x-ui.empty>{{ $q || $status ? __('admin.list.no_match') : __('admin.reviews.empty') }}</x-ui.empty>
    @endif
</x-app-layout>
