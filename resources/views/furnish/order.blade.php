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

            <nav class="hm-mb-lg" aria-label="{{ __('diwan.orders.back') }}">
                <a href="{{ route('orders.index') }}" class="hm-link hm-link--sm">
                    <x-icon name="arrow-left" />
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
                    <dd class="hm-t-lg">{{ __('diwan.orders.status_values.' . $order->status) }}</dd>
                </div>
                <div class="hm-stat">
                    <dt>{{ __('diwan.orders.payment') }}</dt>
                    <dd class="hm-t-lg">{{ __('diwan.orders.payment_values.' . $order->payment_status) }}</dd>
                </div>
                <div class="hm-stat">
                    <dt>{{ __('diwan.orders.total') }}</dt>
                    <dd>{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</dd>
                </div>
            </dl>

            {{-- ══ What was bought ════════════════════════════════════ --}}
            <section class="hm-listrow hm-listrow--xl hm-listrow--top">
                <div class="hm-head">
                    <h2 class="hm-head__title hm-t-lg">{{ __('diwan.orders.items') }}</h2>
                    <span class="hm-head__aside hm-label">
                        {{ trans_choice('diwan.piece_count', $order->items->sum('quantity')) }}
                    </span>
                </div>

                @foreach($order->items as $item)
                    <div class="d-flex gap-3 align-items-center hm-listrow">

                        <x-picture class="hm-thumb hm-thumb--md" :src="$item->product?->imageUrl() ?? asset('assets/images/product-placeholder.jpg')"
                                   alt=""
                                   sizes="4rem"
                                   :width="64"
                                   :height="64"
                                   loading="lazy" />

                        <div class="hm-fill">
                            <p class="hm-strong hm-m-0 hm-mt-0 hm-mb-3xs">
                                {{-- The snapshot is the truth; the live product is only a link. --}}
                                {{ $item->product_name_snapshot }}
                            </p>

                            @if($item->product)
                                <a href="{{ route('furnish.product.show', $item->product->slug) }}"
                                   class="hm-link hm-link--sm">
                                    {{ __('diwan.orders.again') }}
                                    <x-icon name="arrow-right" />
                                </a>
                            @else
                                <span class="hm-muted hm-t-xs">
                                    {{ __('diwan.orders.gone') }}
                                </span>
                            @endif
                        </div>

                        <span class="hm-muted hm-num hm-t-sm">&times;{{ $item->quantity }}</span>

                        <span class="hm-num hm-col-fig hm-end">
                            {{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}
                        </span>
                    </div>
                @endforeach

                <p class="hm-total hm-total--grand hm-mt-md">
                    <span>{{ __('diwan.orders.total') }}</span>
                    <span class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</span>
                </p>
            </section>

            {{-- ══ Where it went ══════════════════════════════════════ --}}
            <section class="hm-listrow hm-listrow--xl hm-listrow--top">
                <h2 class="hm-head__title hm-t-lg hm-mb-md">
                    {{ __('diwan.orders.delivery') }}
                </h2>

                @if($order->address)
                    <address class="hm-panel hm-roman hm-t-sm hm-hold-tight">
                        {{ $order->address->street }}<br>
                        {{ $order->address->city }}<br>
                        {{ $order->address->country }}

                        @if($order->address->phone)
                            <span class="d-block hm-num hm-mt-xs hm-pt-xs hm-ruled-t" dir="ltr">
                                {{ $order->address->phone }}
                            </span>
                        @endif
                    </address>
                @else
                    <p class="hm-muted hm-t-sm">{{ __('diwan.orders.no_address') }}</p>
                @endif
            </section>

        </div>
    </section>

@endsection
