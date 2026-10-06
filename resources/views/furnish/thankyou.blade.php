{{--
  Reads the order's own snapshots rather than the live product rows, so the
  confirmation stays true if a piece is later renamed or delisted. No
  recommendation grid: the moment after buying is not a moment to sell.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.thankyou.title'))

@php
    $line = 'flex justify-between gap-4 py-3 text-sm border-0 border-b border-rule';
@endphp

@section('content')
    <x-ui.page class="max-w-3xl py-(--section-gap)">
        <div class="text-center">
            <span class="inline-flex items-center justify-center size-14 rounded-pill bg-ok-wash text-ok motion-safe:animate-fade-up">
                <x-icon name="check-lg" class="size-6" />
            </span>
            <h1 class="m-0 mt-6 font-display font-medium rtl:font-semibold text-display-s leading-tight tracking-display">{{ __('diwan.thankyou.heading') }}</h1>
            <p class="m-0 mx-auto mt-4 max-w-[48ch] leading-body text-muted">{{ __('diwan.thankyou.body') }}</p>
        </div>

        <div class="mt-12 p-6 sm:p-8 bg-paper-2">
            <div class="flex items-baseline justify-between gap-4 mb-2">
                <h2 class="m-0 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('diwan.thankyou.number') }}</h2>
                <span class="font-mono text-lg" dir="ltr">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>

            <div class="{{ $line }}">
                <span class="text-muted">{{ __('diwan.thankyou.placed') }}</span>
                <span class="font-mono">{{ $order->created_at->translatedFormat('j F Y') }}</span>
            </div>
            <div class="{{ $line }}">
                <span class="text-muted">{{ __('diwan.thankyou.status') }}</span>
                <span>{{ $order->status }}</span>
            </div>

            @foreach($order->items as $item)
                <div class="{{ $line }}">
                    <span>{{ $item->product_name_snapshot }} <span class="font-mono text-xs text-muted" dir="ltr">&times;{{ $item->quantity }}</span></span>
                    <span class="font-mono">{{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}</span>
                </div>
            @endforeach

            <div class="flex justify-between gap-4 pt-4 text-base">
                <span>{{ __('diwan.thankyou.total') }}</span>
                <span class="font-mono text-lg">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-4 mt-10">
            {{-- The order is reachable from here on, not only in the seconds
                 after buying. --}}
            <x-ui.button :href="route('orders.show', $order)">{{ __('diwan.orders.view') }}</x-ui.button>
            <x-ui.link :href="route('furnish.products')">{{ __('diwan.thankyou.continue') }}</x-ui.link>
        </div>
    </x-ui.page>
@endsection
