{{--
  Hallmark · genre: modern-minimal · macrostructure: 11 Catalogue · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  Layer 4 of the fit system lands here: the bag states what its contents take
  up together, and what share of the visitor's floor that is.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.bag.title'))

@section('content')

    @php
        use App\Support\Fit;

        $items = $cart?->items ?? collect();
        $subtotal = $items->sum(fn ($item) => $item->price * $item->quantity);

        // Quantity counts: two identical chairs occupy two footprints.
        $variants = $items->flatMap(fn ($item) => array_fill(0, max(1, $item->quantity), $item->variant));
        $totals = Fit::total($variants, $room);
    @endphp

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-head">
                <h1 class="hm-head__title">{{ __('diwan.bag.title') }}</h1>
                @if($items->isNotEmpty())
                    <span class="hm-head__aside hm-label">{{ trans_choice('diwan.piece_count', $items->sum('quantity')) }}</span>
                @endif
            </div>

            @if($items->isEmpty())
                <div class="hm-empty">
                    <p>{{ __('diwan.bag.empty') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.bag.empty_cta') }}
                        <x-icon name="arrow-right" />
                    </a>
                </div>
            @else
                <div class="row g-5">
                    <div class="col-lg-8">
                        <div class="hm-table__wrap">
                            <table class="hm-table">
                                <thead>
                                    <tr>
                                        <th scope="col">{{ __('diwan.bag.item') }}</th>
                                        <th scope="col">{{ __('diwan.bag.price') }}</th>
                                        <th scope="col">{{ __('diwan.bag.quantity') }}</th>
                                        <th scope="col">{{ __('diwan.bag.total') }}</th>
                                        <th scope="col"><span class="hm-visually-hidden">{{ __('diwan.bag.remove') }}</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex gap-3">
                                                    <x-picture class="hm-thumb hm-thumb--lg" :src="$item->product?->imageUrl() ?? asset('assets/images/product-placeholder.jpg')"
                                                               alt=""
                                                               sizes="4.5rem"
                                                               :width="72"
                                                               :height="72"
                                                               loading="lazy" />
                                                    <div class="hm-minw-0">
                                                        <p class="hm-strong hm-m-0 hm-mt-0 hm-mb-3xs">
                                                            @if($item->product)
                                                                <a href="{{ route('furnish.product.show', $item->product->slug) }}">{{ $item->product->name }}</a>
                                                            @else
                                                                {{ __('diwan.common.error') }}
                                                            @endif
                                                        </p>
                                                        @if($item->variant?->color)
                                                            <p class="hm-muted hm-t-xs hm-m-0 hm-mt-0 hm-mb-2xs">{{ $item->variant->color }}</p>
                                                        @endif
                                                        <x-fit-badge :variant="$item->variant" :room="$room" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($item->price, 2) }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('cart.update', $item->id) }}"
                                                      class="d-flex gap-2 align-items-center">
                                                    @csrf
                                                    @method('PUT')
                                                    <label class="hm-visually-hidden" for="qty-{{ $item->id }}">{{ __('diwan.bag.quantity') }}</label>
                                                    <input id="qty-{{ $item->id }}" class="hm-input hm-input--qty" type="number" name="quantity"
                                                           value="{{ $item->quantity }}" min="1" dir="ltr" data-autosubmit>
                                                    <noscript>
                                                        <button type="submit" class="hm-btn hm-btn--quiet">{{ __('diwan.bag.update') }}</button>
                                                    </noscript>
                                                </form>
                                            </td>
                                            <td class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}</td>
                                            <td>
                                                <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="hm-btn hm-btn--quiet"
                                                            aria-label="{{ __('diwan.bag.remove') }}">
                                                        <x-icon name="x-lg" />
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p class="hm-mt-lg">
                            <a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">
                                <x-icon name="arrow-left" />
                                {{ __('diwan.bag.continue') }}
                            </a>
                        </p>
                    </div>

                    <div class="col-lg-4">
                        <div class="hm-panel">
                            <div class="hm-panel__head">
                                <h2 class="hm-t-lg hm-m-0">{{ __('diwan.bag.summary') }}</h2>
                            </div>

                            <p class="hm-total">
                                <span class="hm-muted">{{ __('diwan.bag.subtotal') }}</span>
                                <span class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($subtotal, 2) }}</span>
                            </p>
                            <p class="hm-total">
                                <span class="hm-muted">{{ __('diwan.bag.shipping') }}</span>
                                <span>{{ __('diwan.bag.free') }}</span>
                            </p>
                            <p class="hm-total hm-total--grand">
                                <span>{{ __('diwan.bag.grand') }}</span>
                                <span class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($subtotal, 2) }}</span>
                            </p>

                            {{-- Layer 4 · what the bag adds up to in the room. --}}
                            @if($totals['footprint'] > 0)
                                <div class="hm-mt-md hm-pt-md hm-ruled-t">
                                    <p class="hm-label hm-mb-2xs">{{ __('diwan.wishlist.room_total') }}</p>
                                    <p class="hm-t-sm hm-m-0">
                                        @if($totals['share'])
                                            {{ __('diwan.bag.together', [
                                                'area'    => number_format($totals['footprint'], 2),
                                                'percent' => round($totals['share'] * 100),
                                            ]) }}
                                        @else
                                            {{ __('diwan.bag.together_no_room', ['area' => number_format($totals['footprint'], 2)]) }}
                                        @endif
                                    </p>
                                    @if($totals['unknown'] > 0)
                                        <p class="hm-field__hint hm-mt-2xs">
                                            {{ __('diwan.wishlist.unmeasured', ['count' => $totals['unknown']]) }}
                                        </p>
                                    @endif
                                </div>
                            @endif

                            <a href="{{ route('checkout') }}" class="hm-btn hm-btn--primary hm-btn--block hm-mt-lg">
                                {{ __('diwan.bag.checkout') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

@endsection
