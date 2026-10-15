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
                <span class="font-mono text-lg" dir="ltr">{{ $order->number() }}</span>
            </div>

            <div class="{{ $line }}">
                <span class="text-muted">{{ __('diwan.thankyou.placed') }}</span>
                <span class="font-mono">{{ $order->created_at->translatedFormat('j F Y') }}</span>
            </div>
            <div class="{{ $line }}">
                <span class="text-muted">{{ __('diwan.thankyou.status') }}</span>
                {{-- It printed the raw word "pending", in English, in both languages. --}}
                <span>{{ __('diwan.orders.status_values.'.$order->status) }}</span>
            </div>

            @foreach($order->items as $item)
                <div class="{{ $line }}">
                    <span class="min-w-0 [overflow-wrap:anywhere]">{{ $item->product_name_snapshot }} <bdi dir="ltr" class="font-mono text-xs text-muted">&times;{{ $item->quantity }}</bdi></span>
                    <span class="font-mono">@money($item->price * $item->quantity)</span>
                </div>
            @endforeach

            <div class="flex justify-between gap-4 pt-4 text-base">
                <span>{{ __('diwan.thankyou.total') }}</span>
                <span class="font-mono text-lg">@money($order->total_price)</span>
            </div>
            <x-vat-line :amount="$order->total_price" :rate="$order->vat_rate" :vat="$order->vat_amount" :order="true" class="mt-1" />
            <x-delivery-summary :order="$order" class="mt-5 pt-4 border-0 border-t border-rule" />
        </div>

        {{-- What happens next: the three things the shop will actually do,
             with the number it will call. --}}
        <section class="mt-12" aria-labelledby="nextTitle">
            <h2 id="nextTitle" class="m-0 mb-6 font-display text-2xl">{{ __('diwan.thankyou.next_title') }}</h2>
            <ol class="m-0 p-0 list-none grid gap-4">
                @foreach(__('diwan.thankyou.next_steps', ['phone' => '__PHONE__']) as $n => $step)
                    <li class="flex gap-4 text-sm leading-body">
                        <span class="inline-flex items-center justify-center shrink-0 size-7 rounded-pill border border-rule-2 font-mono text-xs" aria-hidden="true">{{ $n + 1 }}</span>
                        <span class="pt-1">
                            @php [$before, $after] = array_pad(explode('__PHONE__', $step, 2), 2, null); @endphp
                            {{ $before }}@if($after !== null)<bdi dir="ltr" class="font-mono">{{ $order->address?->phone ?? '—' }}</bdi>{{ $after }}@endif
                        </span>
                    </li>
                @endforeach
            </ol>
        </section>

        <x-order-timeline :order="$order" class="mt-12" />

        <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-4 mt-10">
            {{-- The order is reachable from here on, not only in the seconds
                 after buying. --}}
            <x-ui.button :href="route('orders.show', $order)">{{ __('diwan.orders.view') }}</x-ui.button>
            <x-ui.link :href="route('furnish.products')">{{ __('diwan.thankyou.continue') }}</x-ui.link>
        </div>
    </x-ui.page>
@endsection
