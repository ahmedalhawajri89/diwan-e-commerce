{{--
  The page IS the list. One line at the top, hairline rows, no hero.
  Every row opens.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.orders.title'))

@php
    $tone = fn ($status) => ['cancelled' => 'danger', 'delivered' => 'ok'][$status] ?? 'warn';
@endphp

@section('content')
    <x-ui.page class="max-w-4xl pb-(--section-gap)">
        <x-ui.page-head :title="__('diwan.orders.title')" :lede="__('diwan.orders.intro')">
            <x-slot:aside>
                @if($orders->total() > 0)
                    <span class="font-mono text-xs text-muted">{{ $orders->total() }}</span>
                @endif
            </x-slot:aside>
        </x-ui.page-head>

        @if($orders->count() > 0)
            <ul class="m-0 p-0 list-none">
                @foreach($orders as $order)
                    <li>
                        <a href="{{ route('orders.show', $order) }}"
                           class="group grid grid-cols-[1fr_auto] sm:grid-cols-[7rem_1fr_auto_auto_auto] items-center gap-x-6 gap-y-2 py-5 border-0 border-b border-rule text-ink no-underline transition-colors hover:bg-paper-2 px-2 -mx-2">
                            <span class="font-mono text-accent-ink" dir="ltr">{{ $order->number() }}</span>
                            <span class="text-sm text-muted order-3 sm:order-none">{{ $order->created_at->translatedFormat('j F Y') }}</span>
                            <span class="order-4 sm:order-none"><x-ui.chip :tone="$tone($order->status)">{{ __('diwan.orders.status_values.'.$order->status) }}</x-ui.chip></span>
                            <span class="hidden sm:inline text-xs text-muted">{{ trans_choice('diwan.piece_count', $order->items_count) }}</span>
                            <span class="flex items-center justify-end gap-3 font-mono">
                                {{ __('diwan.common.currency') }}{{ number_format($order->total_price, 2) }}
                                <x-icon name="chevron-right" class="size-3.5 text-muted transition-transform group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5" />
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-12">{{ $orders->onEachSide(1)->links() }}</div>
        @else
            <x-ui.empty :title="__('diwan.orders.empty')" class="mt-10">
                <x-slot:action>
                    <x-ui.link :href="route('furnish.products')">{{ __('diwan.orders.browse') }}</x-ui.link>
                </x-slot:action>
            </x-ui.empty>
        @endif
    </x-ui.page>
@endsection
