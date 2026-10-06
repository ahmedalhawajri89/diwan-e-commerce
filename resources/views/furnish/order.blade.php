{{--
  One order, readable at any time rather than only in the seconds after buying.

  Names come from the order's own snapshots, so a piece renamed or delisted
  later cannot rewrite what somebody bought. The link back to the product is
  offered only when the product is still there; when it is gone the page says
  so instead of dangling a dead link.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.orders.number').' #'.str_pad($order->id, 6, '0', STR_PAD_LEFT))

@php
    $tone = ['cancelled' => 'danger', 'delivered' => 'ok'][$order->status] ?? 'warn';
    $label = 'm-0 font-label text-xs font-medium uppercase tracking-label text-muted';
    $section = 'mt-12 pt-10 border-0 border-t border-rule';
@endphp

@section('content')
    <x-ui.page class="max-w-4xl pb-(--section-gap)">
        <nav class="pt-8" aria-label="{{ __('diwan.orders.back') }}">
            <a href="{{ route('orders.index') }}" class="group inline-flex items-center gap-2 text-sm text-muted no-underline hover:text-ink">
                <x-icon name="arrow-left" class="size-3.5 transition-transform group-hover:-translate-x-0.5 rtl:group-hover:translate-x-0.5" />
                {{ __('diwan.orders.back') }}
            </a>
        </nav>

        <x-ui.page-head :title="'#'.str_pad($order->id, 6, '0', STR_PAD_LEFT)" :lede="$order->created_at->translatedFormat('j F Y')" class="pt-6!">
            <x-slot:aside>
                <x-ui.chip :tone="$tone">{{ __('diwan.orders.status_values.'.$order->status) }}</x-ui.chip>
            </x-slot:aside>
        </x-ui.page-head>

        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-px m-0 mt-10 bg-rule border border-rule">
            <div class="p-5 bg-paper">
                <dt class="{{ $label }}">{{ __('diwan.orders.status') }}</dt>
                <dd class="m-0 mt-2 font-display text-2xl">{{ __('diwan.orders.status_values.'.$order->status) }}</dd>
            </div>
            <div class="p-5 bg-paper">
                <dt class="{{ $label }}">{{ __('diwan.orders.payment') }}</dt>
                <dd class="m-0 mt-2 font-display text-2xl">{{ __('diwan.orders.payment_values.'.$order->payment_status) }}</dd>
            </div>
            <div class="p-5 bg-paper">
                <dt class="{{ $label }}">{{ __('diwan.orders.total') }}</dt>
                <dd class="m-0 mt-2 font-mono text-2xl text-accent-ink">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</dd>
            </div>
        </dl>

        {{-- ══ What was bought ═════════════════════════════════════════════ --}}
        <section class="{{ $section }}">
            <div class="flex items-baseline justify-between gap-4 mb-4">
                <h2 class="m-0 font-display text-2xl">{{ __('diwan.orders.items') }}</h2>
                <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $order->items->sum('quantity')) }}</span>
            </div>

            <ul class="m-0 p-0 list-none">
                @foreach($order->items as $item)
                    <li class="flex items-center gap-5 py-4 border-0 border-b border-rule">
                        <span class="relative block w-16 aspect-[4/5] shrink-0 overflow-hidden bg-paper-2">
                            <x-picture :src="$item->product?->imageUrl() ?? asset('assets/images/product-placeholder.jpg')" alt=""
                                       sizes="4rem" :width="128" :height="160" loading="lazy"
                                       class="absolute inset-0 size-full object-cover" />
                        </span>

                        <span class="flex-1 min-w-0">
                            {{-- The snapshot is the truth; the live product is only a link. --}}
                            <span class="block font-display text-lg">{{ $item->product_name_snapshot }}</span>
                            @if($item->product)
                                <a href="{{ route('furnish.product.show', $item->product->slug) }}" class="text-xs text-accent-ink underline underline-offset-4">{{ __('diwan.orders.again') }}</a>
                            @else
                                <span class="text-xs text-muted">{{ __('diwan.orders.gone') }}</span>
                            @endif
                        </span>

                        <span class="font-mono text-sm text-muted" dir="ltr">&times;{{ $item->quantity }}</span>
                        <span class="font-mono text-sm min-w-24 text-end">{{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="flex justify-between gap-4 pt-5 text-base">
                <span>{{ __('diwan.orders.total') }}</span>
                <span class="font-mono text-lg">{{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}</span>
            </div>
        </section>

        {{-- ══ Where it went ═══════════════════════════════════════════════ --}}
        <section class="{{ $section }}">
            <h2 class="m-0 mb-4 font-display text-2xl">{{ __('diwan.orders.delivery') }}</h2>

            @if($order->address)
                <address class="not-italic max-w-sm p-5 bg-paper-2 text-sm leading-body">
                    {{ $order->address->street }}<br>
                    {{ $order->address->city }}<br>
                    {{ $order->address->country }}

                    @if($order->address->phone)
                        <span class="block mt-3 pt-3 border-0 border-t border-rule font-mono" dir="ltr">{{ $order->address->phone }}</span>
                    @endif
                </address>
            @else
                <p class="m-0 text-sm text-muted">{{ __('diwan.orders.no_address') }}</p>
            @endif
        </section>
    </x-ui.page>
@endsection
