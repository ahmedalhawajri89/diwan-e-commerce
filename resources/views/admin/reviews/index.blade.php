{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md
  This queue is the reason design.md can promise "no invented testimonials":
  nothing reaches the shop until it is approved here.
--}}
<x-app-layout>
    <x-slot name="title">{{ __('admin.reviews.title') }}</x-slot>

    <div class="hm-app__head">
        <div>
            <h1 class="hm-app__title">{{ __('admin.reviews.title') }}</h1>
            <p class="hm-app__intro">{{ __('admin.reviews.intro') }}</p>
        </div>
    </div>

    @if($reviews->count() > 0)
        <div class="hm-table__wrap">
            <table class="hm-table hm-table--dense">
                <thead>
                    <tr>
                        <th scope="col">{{ __('admin.reviews.author') }}</th>
                        <th scope="col">{{ __('admin.reviews.product') }}</th>
                        <th scope="col">{{ __('admin.reviews.rating') }}</th>
                        <th scope="col">{{ __('admin.reviews.comment') }}</th>
                        <th scope="col">{{ __('admin.reviews.status') }}</th>
                        <th scope="col"><span class="hm-visually-hidden">{{ __('admin.action.edit') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reviews as $review)
                        <tr>
                            <td>{{ $review->user?->name ?? '—' }}</td>
                            <td>
                                @if($review->product)
                                    <a href="{{ route('furnish.product.show', $review->product->slug) }}">{{ $review->product->name }}</a>
                                @else
                                    <span class="hm-muted">—</span>
                                @endif
                            </td>
                            <td class="hm-num" aria-label="{{ $review->rating }} / 5">
                                {{ str_repeat('★', $review->rating) }}<span class="hm-faint">{{ str_repeat('★', 5 - $review->rating) }}</span>
                            </td>
                            <td class="hm-hold-tight">
                                <span class="hm-t-sm">{{ $review->comment }}</span>
                            </td>
                            <td>
                                <span class="hm-chip {{ $review->status === 'approved' ? 'hm-chip--ok' : ($review->status === 'rejected' ? 'hm-chip--danger' : 'hm-chip--warn') }}">
                                    {{ __('admin.reviews.values.' . $review->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="hm-actions">
                                    @if($review->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="hm-btn hm-btn--quiet hm-ok"
                                                    aria-label="{{ __('admin.reviews.approve') }}">
                                                <x-icon name="check-lg" />
                                            </button>
                                        </form>
                                    @endif

                                    @if($review->status !== 'rejected')
                                        <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="hm-btn hm-btn--quiet hm-warn"
                                                    aria-label="{{ __('admin.reviews.reject') }}">
                                                <x-icon name="slash-circle" />
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}"
                                          class="delete-form"
                                          data-confirm-message="{{ __('admin.action.confirm_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hm-btn hm-btn--quiet hm-danger"
                                                aria-label="{{ __('admin.action.delete') }}">
                                            <x-icon name="trash" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="hm-mt-xl">{{ $reviews->onEachSide(1)->links() }}</div>
    @else
        <p class="hm-empty">{{ __('admin.reviews.empty') }}</p>
    @endif
</x-app-layout>
