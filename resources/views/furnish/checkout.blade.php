{{--
  The doorway check is the fit system's last useful moment: a piece that cannot
  come through the front door is worth flagging before the courier arrives, not
  after.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.checkout.title'))

@php
    use App\Support\Fit;

    $items = $cart?->items ?? collect();
    $subtotal = $items->sum(fn ($item) => $item->price * $item->quantity);

    // Anything whose narrowest face is wider than a standard doorway.
    $tooWide = $items->filter(function ($item) {
        $v = $item->variant;
        if (! $v || ! $v->width_cm || ! $v->depth_cm) {
            return false;
        }

        return min($v->width_cm, $v->depth_cm, $v->height_cm ?: PHP_INT_MAX) > Fit::DOORWAY_CM;
    });

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

        @if($tooWide->isNotEmpty())
            <x-ui.note tone="warn" class="mt-8">
                <strong>{{ __('diwan.checkout.doorway') }}</strong> —
                {{ __('diwan.checkout.doorway_note') }}
                <span class="opacity-80">({{ $tooWide->map(fn ($i) => $i->product?->name)->filter()->join(app()->getLocale() === 'ar' ? '، ' : ', ') }})</span>
            </x-ui.note>
        @endif

        <form method="POST" action="{{ route('checkout.process') }}" x-data="submitOnce" @submit="lock"
              class="grid gap-12 pt-10 lg:grid-cols-[1fr_24rem] lg:gap-16">
            @csrf

            <div class="min-w-0">
                <h2 class="{{ $step }}"><span class="{{ $num }}" dir="ltr">01</span>{{ __('diwan.checkout.delivery') }}</h2>

                @if($last && ! old('street'))
                    <p class="m-0 -mt-4 mb-7 text-sm text-muted">{{ __('diwan.checkout.prefilled') }}</p>
                @endif

                <div class="grid gap-7 sm:grid-cols-2">
                    <x-ui.field name="country" :label="__('diwan.checkout.country')" :value="old('country', $last?->country)"
                                required maxlength="100" autocomplete="country-name" />
                    <x-ui.field name="city" :label="__('diwan.checkout.city')" :value="old('city', $last?->city)"
                                required maxlength="100" autocomplete="address-level2" />
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

                <h2 class="{{ $step }} mt-14"><span class="{{ $num }}" dir="ltr">02</span>{{ __('diwan.checkout.method') }}</h2>

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
                            <span class="font-mono text-sm">{{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-2">
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
                </div>

                <x-ui.button type="submit" :block="true" class="mt-6" ::disabled="busy">
                    <span x-show="idle">{{ __('diwan.checkout.place') }}</span>
                    <span x-show="busy" x-cloak>{{ __('diwan.auth.submitting') }}</span>
                </x-ui.button>
            </aside>
        </form>
    </x-ui.page>
@endsection
