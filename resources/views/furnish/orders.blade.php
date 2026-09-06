{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · theme: custom (Diwan)
  design-system: design.md · designed-as-app

  The page IS the list. One line at the top, hairline rows, no hero.
  Every row opens; the account page used to print five of these as dead text.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.orders.title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page hm-page--narrow">

            <div class="hm-app__head">
                <div>
                    <h1 class="hm-app__title">{{ __('diwan.orders.title') }}</h1>
                    <p class="hm-app__intro">{{ __('diwan.orders.intro') }}</p>
                </div>
                @if($orders->total() > 0)
                    <span class="hm-head__aside hm-num hm-muted">{{ $orders->total() }}</span>
                @endif
            </div>

            @if($orders->count() > 0)
                @foreach($orders as $order)
                    <a href="{{ route('orders.show', $order) }}"
                       class="d-flex flex-wrap gap-3 align-items-center hm-listrow">

                        <span class="hm-num hm-col-fig hm-accent-ink">
                            #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                        </span>

                        <span class="hm-muted hm-num hm-fill-sm hm-t-sm">
                            {{ $order->created_at->translatedFormat('j F Y') }}
                        </span>

                        <span class="hm-chip {{ $order->status === 'cancelled' ? 'hm-chip--danger' : ($order->status === 'delivered' ? 'hm-chip--ok' : 'hm-chip--warn') }}">
                            {{ __('diwan.orders.status_values.' . $order->status) }}
                        </span>

                        <span class="hm-muted hm-t-xs">
                            {{ trans_choice('diwan.piece_count', $order->items_count) }}
                        </span>

                        <span class="hm-num hm-mis-auto">
                            {{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}
                        </span>

                        <x-icon name="chevron-right" class="hm-muted" />
                    </a>
                @endforeach

                <div class="hm-mt-xl">{{ $orders->onEachSide(1)->links() }}</div>
            @else
                <div class="hm-empty">
                    <p>{{ __('diwan.orders.empty') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.orders.browse') }}
                        <x-icon name="arrow-right" />
                    </a>
                </div>
            @endif

        </div>
    </section>

@endsection
