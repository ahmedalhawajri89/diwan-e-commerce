{{--
  Hallmark · genre: modern-minimal · macrostructure: 11 Catalogue · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  Reads the order's own snapshots rather than the live product rows, so the
  confirmation stays true if a piece is later renamed or delisted. No
  recommendation grid: the moment after buying is not a moment to sell.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.thankyou.title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page hm-page--narrow">
            <p class="hm-note hm-note--ok hm-mb-xl" role="status">
                <x-icon name="check-lg" />
                <span>{{ __('diwan.thankyou.heading') }}</span>
            </p>

            <h1 class="hm-t-3xl hm-track-display hm-m-0 hm-mt-0 hm-mb-md">
                {{ __('diwan.thankyou.heading') }}
            </h1>
            <p class="hm-muted hm-measure hm-mb-xl">{{ __('diwan.thankyou.body') }}</p>

            <div class="hm-panel">
                <div class="hm-panel__head">
                    <h2 class="hm-t-lg hm-m-0">{{ __('diwan.thankyou.number') }}</h2>
                    <span class="hm-num">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>

                <p class="hm-total">
                    <span class="hm-muted">{{ __('diwan.thankyou.placed') }}</span>
                    <span class="hm-num">{{ $order->created_at->translatedFormat('j F Y') }}</span>
                </p>
                <p class="hm-total">
                    <span class="hm-muted">{{ __('diwan.thankyou.status') }}</span>
                    <span>{{ $order->status }}</span>
                </p>
                <p class="hm-total hm-total--grand">
                    <span>{{ __('diwan.thankyou.total') }}</span>
                    <span class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</span>
                </p>
            </div>

            @if($order->items->isNotEmpty())
                <div class="hm-mt-xl">
                    <div class="hm-head">
                        <h2 class="hm-head__title hm-t-xl">{{ __('diwan.thankyou.items') }}</h2>
                    </div>

                    @foreach($order->items as $item)
                        <div class="d-flex gap-3 align-items-center hm-listrow">
                            <div class="hm-fill">
                                <p class="hm-strong hm-m-0">{{ $item->product_name_snapshot }}</p>
                                <p class="hm-muted hm-num hm-t-xs hm-m-0">&times;{{ $item->quantity }}</p>
                            </div>
                            <span class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="d-flex flex-wrap gap-4 hm-mt-xl">
                {{-- The order is reachable from here on, not only in the
                     seconds after buying. --}}
                <a href="{{ route('orders.show', $order) }}" class="hm-link">
                    {{ __('diwan.orders.view') }}
                    <x-icon name="arrow-right" />
                </a>
                <a href="{{ route('furnish.products') }}" class="hm-link">
                    {{ __('diwan.thankyou.continue') }}
                    <x-icon name="arrow-right" />
                </a>
            </div>
        </div>
    </section>

@endsection
