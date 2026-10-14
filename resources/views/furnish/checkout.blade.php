{{--
  The way-in check is the fit system's last useful moment: a piece that cannot
  reach the room is worth flagging before the courier arrives, not after
  (partials/passage-check).
--}}
@extends('layouts.furnish')

@section('title', __('diwan.checkout.title'))

@php
    $items = $cart?->items ?? collect();
    $subtotal = $items->sum(fn ($item) => $item->price * $item->quantity);

    // Each city's figures, written by the server, so choosing one shows its
    // fee and the new total at once (deliveryChoice) and nothing is worked out
    // in the browser. An unknown fee adds nothing: it is agreed on the call.
    $figures = fn ($fee) => [
        'fee' => $fee === null ? __('diwan.bag.shipping_on_contact') : App\Support\Money::format($fee),
        'total' => App\Support\Money::format($subtotal + ($fee ?? 0)),
        'vat' => App\Support\Money::format(App\Support\Money::vatIn($subtotal + ($fee ?? 0))),
        // "Total" once delivery is in it; "total, before delivery" while it is not.
        'label' => $fee === null ? __('diwan.bag.grand') : __('diwan.bag.grand_all'),
    ];
    $none = ['fee' => $zones->isEmpty() ? __('diwan.bag.shipping_on_contact') : __('diwan.checkout.choose_city'), 'total' => App\Support\Money::format($subtotal), 'vat' => App\Support\Money::format(App\Support\Money::vatIn($subtotal)), 'label' => __('diwan.bag.grand')];
    $chosen = old('delivery_zone_id');

    $step = 'm-0 mb-8 flex items-baseline gap-3 font-display text-2xl';
    $num = 'font-mono text-xs text-accent-ink';
    $line = 'flex justify-between gap-4 py-3 text-sm';
@endphp

@section('content')
    <x-ui.page class="pb-(--section-gap)">
        {{-- Where the visitor is in the three steps. The bag is behind them
             and can be reached; the confirmation is ahead. --}}
        <nav aria-label="{{ __('diwan.checkout.steps') }}" class="pt-8">
            <ol class="m-0 p-0 list-none flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                <li class="inline-flex items-center gap-2">
                    <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-2 min-h-11 text-muted no-underline hover:text-ink">
                        <x-icon name="check-lg" class="size-3.5 text-ok" />{{ __('diwan.checkout.step_bag') }}
                    </a>
                </li>
                <li aria-hidden="true" class="text-rule-2">—</li>
                <li aria-current="step" class="inline-flex items-center gap-2 font-medium text-ink">
                    <span class="inline-flex items-center justify-center size-5 rounded-pill bg-ink text-on-accent font-mono text-[0.625rem]">2</span>{{ __('diwan.checkout.step_delivery') }}
                </li>
                <li aria-hidden="true" class="text-rule-2">—</li>
                <li class="inline-flex items-center gap-2 text-muted">
                    <span class="inline-flex items-center justify-center size-5 rounded-pill border border-rule-2 font-mono text-[0.625rem]">3</span>{{ __('diwan.checkout.step_done') }}
                </li>
            </ol>
        </nav>

        <x-ui.page-head :title="__('diwan.checkout.title')" class="pt-6!" />

        @include('partials.passage-check', ['items' => $items, 'doorway' => true])

        <form method="POST" action="{{ route('checkout.process') }}" x-data="submitOnce" @submit="lock"
              class="grid gap-12 pt-10 lg:grid-cols-[1fr_24rem] lg:gap-16">
            @csrf

            <div class="contents" x-data="deliveryChoice" data-fee="{{ $none['fee'] }}" data-total="{{ $none['total'] }}" data-vat="{{ $none['vat'] }}" data-label="{{ $none['label'] }}">
            <div class="min-w-0">
                <h2 class="{{ $step }}"><span class="{{ $num }}" dir="ltr">01</span>{{ __('diwan.checkout.delivery') }}</h2>

                @if($last && ! old('street'))
                    <p class="m-0 -mt-4 mb-7 text-sm text-muted">{{ __('diwan.checkout.prefilled') }}</p>
                @endif

                <div class="grid gap-7 sm:grid-cols-2">
                    <x-ui.field name="country" :label="__('diwan.checkout.country')" :value="old('country', $last?->country)"
                                required maxlength="100" autocomplete="country-name" />
                    @if($zones->isNotEmpty())
                        {{-- The cities the shop delivers to, each with its fee. --}}
                        <x-ui.select name="delivery_zone_id" :label="__('diwan.checkout.city')" required x-ref="zone" x-on:change="pick">
                            <option value="">{{ __('diwan.checkout.choose_city') }}</option>
                            @foreach($zones as $zone)
                                @php $f = $figures($zone->fee !== null ? (float) $zone->fee : null); @endphp
                                <option value="{{ $zone->id }}" data-fee="{{ $f['fee'] }}" data-total="{{ $f['total'] }}" data-vat="{{ $f['vat'] }}" data-label="{{ $f['label'] }}"
                                        @selected((string) $chosen === (string) $zone->id)>
                                    {{ $zone->name }} — {{ $f['fee'] }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    @else
                        <x-ui.field name="city" :label="__('diwan.checkout.city')" :value="old('city', $last?->city)"
                                    required maxlength="100" autocomplete="address-level2" />
                    @endif
                    <div class="sm:col-span-2">
                        <x-ui.field name="street" :label="__('diwan.checkout.street')" :value="old('street', $last?->street)"
                                    required maxlength="255" autocomplete="street-address" />
                    </div>
                    <x-ui.field name="phone" type="tel" :label="__('diwan.checkout.phone')" :value="old('phone', $last?->phone)"
                                required maxlength="20" autocomplete="tel" dir="ltr" />
                    <div class="sm:col-span-2">
                        <x-ui.field name="notes" type="textarea" :label="__('diwan.checkout.notes')" :value="old('notes')"
                                    :hint="__('diwan.checkout.notes_hint')" maxlength="500" rows="3" dir="auto" />
                    </div>
                </div>

                {{-- When it comes: a day and a part of the day, only on the days the
                     shop has said it delivers; otherwise the shop calls to agree one. --}}
                <h2 class="{{ $step }} mt-14"><span class="{{ $num }}" dir="ltr">02</span>{{ __('diwan.checkout.when') }}</h2>
                @if($dates->isNotEmpty())
                    <div class="grid gap-6 sm:grid-cols-2">
                        <x-ui.select name="delivery_date" :label="__('diwan.checkout.date')" required>
                            <option value="">{{ __('diwan.checkout.choose_date') }}</option>
                            @foreach($dates as $day)
                                <option value="{{ $day->toDateString() }}" @selected(old('delivery_date') === $day->toDateString())>{{ $day->translatedFormat('l j F') }}</option>
                            @endforeach
                        </x-ui.select>
                        <fieldset class="m-0 p-0 border-0">
                            <legend class="block mb-3 font-label text-xs font-medium uppercase tracking-label text-muted">{{ __('diwan.checkout.window') }}</legend>
                            <div class="flex gap-2">
                                @foreach(App\Support\DeliveryDates::WINDOWS as $window)
                                    <label class="inline-flex items-center justify-center min-h-11 px-4 border border-rule-2 rounded-control text-sm cursor-pointer transition-colors hover:border-ink has-checked:border-ink has-checked:bg-ink has-checked:text-paper has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-focus">
                                        <input type="radio" name="delivery_window" value="{{ $window }}" required @checked(old('delivery_window') === $window) class="sr-only">
                                        {{ __('diwan.checkout.windows.'.$window) }}
                                    </label>
                                @endforeach
                            </div>
                            @error('delivery_window')<p class="m-0 mt-2 text-sm text-danger">{{ $message }}</p>@enderror
                        </fieldset>
                    </div>
                @else
                    <p class="flex items-start gap-3 m-0 p-4 bg-paper-2 text-sm leading-body text-neutral">
                        <x-icon name="telephone" class="size-4 mt-0.5 shrink-0" />{{ __('diwan.checkout.call_to_book') }}
                    </p>
                @endif

                <h2 class="{{ $step }} mt-14"><span class="{{ $num }}" dir="ltr">03</span>{{ __('diwan.checkout.method') }}</h2>

                {{-- One method, stated plainly. The card fields that used to sit
                     here validated sixteen digits and a CVC and then discarded
                     them — no gateway, no storage. A form that asks for a card
                     it cannot protect is worse than a form that does not ask. --}}
                <div class="flex items-start gap-4 p-5 border border-ink">
                    <span class="inline-flex items-center justify-center size-10 shrink-0 rounded-pill bg-ink text-paper">
                        <x-icon name="cash-coin" class="size-4" />
                    </span>
                    <div>
                        <p class="m-0 font-medium">{{ __('diwan.checkout.cod') }}</p>
                        <p class="m-0 mt-1 text-sm leading-body text-muted">{{ __('diwan.checkout.cod_note') }}</p>
                    </div>
                </div>
            </div>

            {{-- ══ Order summary ═══════════════════════════════════════════ --}}
            <aside class="lg:sticky lg:top-36 self-start p-6 sm:p-8 bg-paper-2">
                <div class="flex items-baseline justify-between gap-4 mb-4">
                    <h2 class="m-0 font-display text-2xl">{{ __('diwan.checkout.review') }}</h2>
                    <span class="font-mono text-xs text-muted">{{ trans_choice('diwan.piece_count', $items->sum('quantity')) }}</span>
                </div>

                <ul class="m-0 p-0 list-none">
                    @foreach($items as $item)
                        <li class="flex items-center gap-4 py-3 border-0 border-b border-rule">
                            <span class="relative block w-12 aspect-[4/5] shrink-0 overflow-hidden bg-paper-3">
                                <x-picture :src="$item->product?->imageUrl() ?? asset('assets/images/product-placeholder.jpg')" alt=""
                                           sizes="3rem" :width="96" :height="120" loading="lazy"
                                           class="absolute inset-0 size-full object-cover" />
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm truncate">{{ $item->product?->name }}</span>
                                <span class="block text-xs text-muted">
                                    @if($item->variant?->color){{ $item->variant->colourLabel() }} · @endif<bdi dir="ltr" class="font-mono">&times;{{ $item->quantity }}</bdi>
                                </span>
                            </span>
                            <span class="font-mono text-sm">@money($item->price * $item->quantity)</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-2">
                    <div class="{{ $line }}">
                        <span class="text-muted">{{ __('diwan.bag.subtotal') }}</span>
                        <span class="font-mono">@money($subtotal)</span>
                    </div>
                    <div class="{{ $line }}">
                        <span class="text-muted">{{ __('diwan.bag.shipping') }}</span>
                        <span x-text="fee">{{ $none['fee'] }}</span>
                    </div>
                    <div class="{{ $line }} mt-2 pt-4 border-0 border-t border-rule-2 text-base">
                        <span x-text="label">{{ $none['label'] }}</span>
                        <span class="font-mono text-lg" x-text="total">{{ $none['total'] }}</span>
                    </div>
                    <div class="flex justify-between gap-4 pb-1 text-xs text-muted">
                        <span>{{ __('diwan.money.vat_line', ['rate' => App\Support\Money::rate()]) }}</span>
                        <span class="font-mono" x-text="vat">{{ $none['vat'] }}</span>
                    </div>
                </div>

                <x-ui.button type="submit" :block="true" class="mt-6" ::disabled="busy">
                    <span x-show="idle">{{ __('diwan.checkout.place') }}</span>
                    <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
                </x-ui.button>
            </aside>
            </div>
        </form>
    </x-ui.page>
@endsection
