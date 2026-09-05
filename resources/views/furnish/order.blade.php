{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · theme: custom (Diwan)
  design-system: design.md · designed-as-app

  One order, readable at any time rather than only in the seconds after buying.

  Names and images come from the order's own snapshots, so a piece renamed or
  delisted later cannot rewrite what somebody bought. The link back to the
  product is offered only when the product is still there; when it is gone the
  page says so instead of dangling a dead link.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.orders.number') . ' #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page hm-page--narrow">

            <nav aria-label="{{ __('diwan.orders.back') }}" style="margin-bottom: var(--space-lg);">
                <a href="{{ route('orders.index') }}" class="hm-link hm-link--sm">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    {{ __('diwan.orders.back') }}
                </a>
            </nav>

            <div class="hm-app__head">
                <div>
                    <h1 class="hm-app__title hm-num">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h1>
                    <p class="hm-app__intro hm-num">{{ $order->created_at->translatedFormat('j F Y') }}</p>
                </div>
            </div>

            <dl class="hm-stats">
                <div class="hm-stat">
                    <dt>{{ __('diwan.orders.status') }}</dt>
                    <dd style="font-size: var(--text-lg);">{{ __('diwan.orders.status_values.' . $order->status) }}</dd>
                </div>
                <div class="hm-stat">
                    <dt>{{ __('diwan.orders.payment') }}</dt>
                    <dd style="font-size: var(--text-lg);">{{ __('diwan.orders.payment_values.' . $order->payment_status) }}</dd>
                </div>
                <div class="hm-stat">
                    <dt>{{ __('diwan.orders.total') }}</dt>
                    <dd>{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</dd>
                </div>
            </dl>

            {{-- ══ What was bought ════════════════════════════════════ --}}
            <section style="padding-block: var(--space-xl); border-top: var(--rule-hair) solid var(--color-rule);">
                <div class="hm-head">
                    <h2 class="hm-head__title" style="font-size: var(--text-lg);">{{ __('diwan.orders.items') }}</h2>
                    <span class="hm-head__aside hm-label">
                        {{ trans_choice('diwan.piece_count', $order->items->sum('quantity')) }}
                    </span>
                </div>

                @foreach($order->items as $item)
                    <div class="d-flex gap-3 align-items-center"
                         style="padding-block: var(--space-md); border-bottom: var(--rule-hair) solid var(--color-rule);">

                        <img src="{{ $item->product?->imageUrl() ?? asset('assets/images/product-img-1.jpg') }}"
                             alt="" loading="lazy" width="64" height="64"
                             style="width: 4rem; height: 4rem; object-fit: cover; border-radius: var(--radius-control); background: var(--color-paper-3); flex-shrink: 0;">

                        <div style="flex: 1 1 auto; min-width: 0;">
                            <p style="font-weight: 500; margin: 0 0 var(--space-3xs);">
                                {{-- The snapshot is the truth; the live product is only a link. --}}
                                {{ $item->product_name_snapshot }}
                            </p>

                            @if($item->product)
                                <a href="{{ route('furnish.product.show', $item->product->slug) }}"
                                   class="hm-link hm-link--sm">
                                    {{ __('diwan.orders.again') }}
                                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                </a>
                            @else
                                <span class="hm-muted" style="font-size: var(--text-xs);">
                                    {{ __('diwan.orders.gone') }}
                                </span>
                            @endif
                        </div>

                        <span class="hm-muted hm-num" style="font-size: var(--text-sm);">&times;{{ $item->quantity }}</span>

                        <span class="hm-num" style="flex: 0 0 6rem; text-align: end;">
                            {{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}
                        </span>
                    </div>
                @endforeach

                <p class="hm-total hm-total--grand" style="margin-top: var(--space-md);">
                    <span>{{ __('diwan.orders.total') }}</span>
                    <span class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</span>
                </p>
            </section>

            {{-- ══ Where it went ══════════════════════════════════════ --}}
            <section style="padding-block: var(--space-xl); border-top: var(--rule-hair) solid var(--color-rule);">
                <h2 class="hm-head__title" style="font-size: var(--text-lg); margin-bottom: var(--space-md);">
                    {{ __('diwan.orders.delivery') }}
                </h2>

                @if($order->address)
                    <address class="hm-panel" style="font-style: normal; font-size: var(--text-sm); max-width: 26rem;">
                        {{ $order->address->street }}<br>
                        {{ $order->address->city }}<br>
                        {{ $order->address->country }}

                        @if($order->address->phone)
                            <span class="d-block hm-num" dir="ltr"
                                  style="margin-top: var(--space-xs); padding-top: var(--space-xs); border-top: var(--rule-hair) solid var(--color-rule);">
                                {{ $order->address->phone }}
                            </span>
                        @endif
                    </address>
                @else
                    <p class="hm-muted" style="font-size: var(--text-sm);">{{ __('diwan.orders.no_address') }}</p>
                @endif
            </section>

        </div>
    </section>

@endsection
