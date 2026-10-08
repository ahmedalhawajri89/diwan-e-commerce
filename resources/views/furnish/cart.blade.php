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

        {{-- A removal is undone here, not confirmed beforehand: the line goes
             back exactly as it was, through the same Add to bag route. --}}
        @if($undo = session('bag_undo'))
            <form method="POST" action="{{ route('cart.add') }}" role="status"
                  class="flex flex-wrap items-center justify-between gap-3 mt-8 px-5 py-3 bg-paper-2 text-sm">
                @csrf
                <input type="hidden" name="product_id" value="{{ $undo['product_id'] }}">
                @if($undo['variant_id'])
                    <input type="hidden" name="variant_id" value="{{ $undo['variant_id'] }}">
                @endif
                <input type="hidden" name="quantity" value="{{ $undo['quantity'] }}">
                <span>{{ __('diwan.bag.removed', ['name' => $undo['name'] ?? '—']) }}</span>
                <button type="submit" class="inline-flex items-center min-h-11 p-0 border-0 bg-transparent text-sm font-medium text-accent-ink underline underline-offset-4 cursor-pointer hover:text-ink">
                    {{ __('diwan.bag.undo') }}
                </button>
            </form>
        @endif

        @if($items->isEmpty())
            <x-ui.empty :title="__('diwan.bag.empty')" class="mt-10">
                <x-slot:action>
                    <x-ui.link :href="route('furnish.products')">{{ __('diwan.bag.empty_cta') }}</x-ui.link>
                </x-slot:action>
            </x-ui.empty>

            {{-- What this visitor looked at before, so an empty bag is a way
                 back in rather than a dead end. --}}
            @if($recent->isNotEmpty())
                <section class="mt-16" aria-labelledby="recentTitle">
                    <h2 id="recentTitle" class="m-0 mb-8 font-display font-medium rtl:font-semibold text-2xl">{{ __('diwan.product.recently_viewed') }}</h2>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-12 lg:gap-x-6">
                        @foreach($recent as $index => $product)
                            <x-product-card :product="$product" :room="$room" :i="$index" />
                        @endforeach
                    </div>
                </section>
            @endif
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
                                        <p class="m-0 mt-1 text-xs text-muted">{{ $item->variant->colourLabel() }}</p>
                                    @endif
                                    <p class="m-0 mt-1 font-mono text-xs text-muted">{{ __('diwan.common.currency') }}{{ number_format($item->price, 2) }}</p>
                                    <div class="flex flex-wrap gap-1.5 mt-3">
                                        <x-fit-badge :variant="$item->variant" :room="$room" />
                                        <x-passage-badge :variant="$item->variant" :way-in="$wayIn" :onCard="true" />
                                    </div>
                                    @php $stock = $item->variant?->stock; @endphp
                                    @if($stock !== null && $item->quantity > $stock)
                                        <p class="flex items-center gap-2 m-0 mt-2 text-xs text-danger">
                                            <x-icon name="exclamation-triangle" class="size-3.5" />{{ __('diwan.bag.over_stock', ['count' => $stock]) }}
                                        </p>
                                    @elseif($stock !== null && $stock <= 5)
                                        <p class="m-0 mt-2 text-xs text-warn">{{ __('diwan.product.low_stock', ['count' => $stock]) }}</p>
                                    @endif
                                </div>

                                <div class="col-span-2 sm:col-span-1 sm:col-start-3 sm:row-start-1 sm:row-span-2 flex items-center justify-between gap-6 sm:flex-col sm:items-end sm:justify-between">
                                    <p class="m-0 font-mono text-accent-ink sm:order-last">{{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}</p>

                                    <div class="flex flex-wrap items-center gap-2">
                                        {{-- The stepper changes the number; Update sends it.
                                             It used to submit on every change, so a keyboard
                                             user stepping 1 → 3 reloaded the page twice. --}}
                                        <form method="POST" action="{{ route('cart.update', $item->id) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <x-ui.stepper name="quantity" :value="$item->quantity" :min="1"
                                                          :max="max($item->quantity, $item->variant?->stock ?? 999)"
                                                          :label="__('diwan.bag.quantity_of', ['name' => $item->product?->name])" />
                                            <button type="submit"
                                                    class="inline-flex items-center min-h-11 px-3 border-0 bg-transparent text-sm text-accent-ink underline underline-offset-4 cursor-pointer hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                {{ __('diwan.bag.update') }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="{{ __('diwan.bag.remove_named', ['name' => $item->product?->name]) }}"
                                                    class="inline-flex items-center justify-center size-11 p-0 border-0 bg-transparent text-muted cursor-pointer hover:text-danger focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                                                <x-icon name="trash" class="size-4" />
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @include('partials.passage-check', ['items' => $items])

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
