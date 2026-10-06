{{--
  Layer 4 of the fit system lands here: the bag states what its contents take
  up together, and what share of the visitor's floor that is.

  Each piece is a row rather than a table cell, so the bag reads the same on a
  phone as on a desk; the summary sits beside it and stays in view.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.bag.title'))

@php
    use App\Support\Fit;

    $items = $cart?->items ?? collect();
    $subtotal = $items->sum(fn ($item) => $item->price * $item->quantity);

    // Quantity counts: two identical chairs occupy two footprints.
    $variants = $items->flatMap(fn ($item) => array_fill(0, max(1, $item->quantity), $item->variant));
    $totals = Fit::total($variants, $room);

    $line = 'flex justify-between gap-4 py-3 text-sm';
@endphp

@section('content')
    <x-ui.page class="pb-(--section-gap)">
        <x-ui.page-head :title="__('diwan.bag.title')">
            <x-slot:aside>
                @if($items->isNotEmpty())
                    <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $items->sum('quantity')) }}</span>
                @endif
            </x-slot:aside>
        </x-ui.page-head>

        @if($items->isEmpty())
            <x-ui.empty :title="__('diwan.bag.empty')" class="mt-10">
                <x-slot:action>
                    <x-ui.link :href="route('furnish.products')">{{ __('diwan.bag.empty_cta') }}</x-ui.link>
                </x-slot:action>
            </x-ui.empty>
        @else
            <div class="grid gap-12 pt-10 lg:grid-cols-[1fr_22rem] lg:gap-16">
                <div class="min-w-0">
                    <ul class="m-0 p-0 list-none">
                        @foreach($items as $item)
                            <li class="grid grid-cols-[5.5rem_1fr] sm:grid-cols-[7rem_1fr_auto] gap-x-5 gap-y-3 py-6 border-0 border-b border-rule first:pt-0">
                                <div class="relative aspect-[4/5] overflow-hidden bg-paper-2 sm:row-span-2">
                                    <x-picture :src="$item->product?->imageUrl() ?? asset('assets/images/product-placeholder.jpg')" alt=""
                                               sizes="7rem" :width="160" :height="200" loading="lazy"
                                               class="absolute inset-0 size-full object-cover" />
                                </div>

                                <div class="min-w-0">
                                    <p class="m-0 font-display text-xl leading-snug">
                                        @if($item->product)
                                            <a href="{{ route('furnish.product.show', $item->product->slug) }}" class="text-ink no-underline hover:underline underline-offset-4">{{ $item->product->name }}</a>
                                        @else
                                            {{ __('diwan.common.error') }}
                                        @endif
                                    </p>
                                    @if($item->variant?->color)
                                        <p class="m-0 mt-1 text-xs text-muted">{{ $item->variant->color }}</p>
                                    @endif
                                    <p class="m-0 mt-1 font-mono text-xs text-muted">{{ __('diwan.common.currency') }}{{ number_format($item->price, 2) }}</p>
                                    <div class="mt-3"><x-fit-badge :variant="$item->variant" :room="$room" /></div>
                                </div>

                                <div class="col-span-2 sm:col-span-1 sm:col-start-3 sm:row-start-1 sm:row-span-2 flex items-center justify-between gap-6 sm:flex-col sm:items-end sm:justify-between">
                                    <p class="m-0 font-mono text-accent-ink sm:order-last">{{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}</p>

                                    <div class="flex items-center gap-3">
                                        <form method="POST" action="{{ route('cart.update', $item->id) }}" class="flex items-center">
                                            @csrf
                                            @method('PUT')
                                            <label class="sr-only" for="qty-{{ $item->id }}">{{ __('diwan.bag.quantity') }}</label>
                                            <input id="qty-{{ $item->id }}" type="number" name="quantity" value="{{ $item->quantity }}" min="1" dir="ltr" data-autosubmit
                                                   class="w-16 h-10 px-2 bg-paper border border-rule-2 rounded-control text-center font-mono text-sm focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus">
                                            <noscript>
                                                <x-ui.button type="submit" variant="ghost" class="ms-2">{{ __('diwan.bag.update') }}</x-ui.button>
                                            </noscript>
                                        </form>

                                        <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="{{ __('diwan.bag.remove') }}"
                                                    class="inline-flex items-center justify-center size-10 p-0 border-0 bg-transparent text-muted cursor-pointer hover:text-danger">
                                                <x-icon name="trash" class="size-4" />
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        <a href="{{ route('furnish.products') }}" class="group inline-flex items-center gap-2 text-sm text-muted no-underline hover:text-ink">
                            <x-icon name="arrow-left" class="size-3.5 transition-transform group-hover:-translate-x-0.5 rtl:group-hover:translate-x-0.5" />
                            {{ __('diwan.bag.continue') }}
                        </a>
                    </div>
                </div>

                <aside class="lg:sticky lg:top-36 self-start p-6 sm:p-8 bg-paper-2">
                    <h2 class="m-0 mb-4 font-display text-2xl">{{ __('diwan.bag.summary') }}</h2>

                    <div class="{{ $line }}">
                        <span class="text-muted">{{ __('diwan.bag.subtotal') }}</span>
                        <span class="font-mono">{{ __('diwan.common.currency') }}{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="{{ $line }}">
                        <span class="text-muted">{{ __('diwan.bag.shipping') }}</span>
                        <span>{{ __('diwan.bag.shipping_on_contact') }}</span>
                    </div>
                    <div class="{{ $line }} mt-2 pt-4 border-0 border-t border-rule-2 text-base">
                        <span>{{ __('diwan.bag.grand') }}</span>
                        <span class="font-mono text-lg">{{ __('diwan.common.currency') }}{{ number_format($subtotal, 2) }}</span>
                    </div>

                    {{-- Layer 4 · what the bag adds up to in the room. --}}
                    @if($totals['footprint'] > 0)
                        <div class="mt-5 pt-5 border-0 border-t border-rule">
                            <p class="flex items-center gap-2 m-0 mb-2 font-label text-xs font-medium uppercase tracking-label text-muted">
                                <x-icon name="rulers" class="size-3.5" />{{ __('diwan.wishlist.room_total') }}
                            </p>
                            <p class="m-0 text-sm leading-body">
                                @if($totals['share'])
                                    {{ __('diwan.bag.together', ['area' => number_format($totals['footprint'], 2), 'percent' => round($totals['share'] * 100)]) }}
                                @else
                                    {{ __('diwan.bag.together_no_room', ['area' => number_format($totals['footprint'], 2)]) }}
                                @endif
                            </p>
                            @if($totals['unknown'] > 0)
                                <p class="m-0 mt-2 text-xs text-muted">{{ __('diwan.wishlist.unmeasured', ['count' => $totals['unknown']]) }}</p>
                            @endif
                        </div>
                    @endif

                    <x-ui.button :href="route('checkout')" :block="true" class="mt-6">{{ __('diwan.bag.checkout') }}</x-ui.button>
                    @guest
                        {{-- Said before they press, so the sign-in page is no surprise. --}}
                        <p class="m-0 mt-3 text-xs leading-body text-muted">{{ __('diwan.bag.guest_note') }}</p>
                    @endguest
                </aside>
            </div>
        @endif
    </x-ui.page>
@endsection
