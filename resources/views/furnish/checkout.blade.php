{{--
  Hallmark · genre: modern-minimal · macrostructure: 11 Catalogue · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  The doorway check is the fit system's last useful moment: a piece that cannot
  come through the front door is worth flagging before the courier arrives, not
  after.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.checkout.title'))

@section('content')

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
    @endphp

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-head">
                <h1 class="hm-head__title">{{ __('diwan.checkout.title') }}</h1>
            </div>

            @if($tooWide->isNotEmpty())
                <p class="hm-note hm-note--warn hm-mb-lg" role="status">
                    <x-icon name="exclamation-triangle" />
                    <span>
                        <strong>{{ __('diwan.checkout.doorway') }}</strong> —
                        {{ __('diwan.checkout.doorway_note') }}
                        <span class="hm-muted">({{ $tooWide->map(fn ($i) => $i->product?->name)->filter()->join('، ') }})</span>
                    </span>
                </p>
            @endif

            <form method="POST" action="{{ route('checkout.process') }}">
                @csrf

                <div class="row g-5">
                    <div class="col-lg-7">
                        <h2 class="hm-t-lg hm-m-0 hm-mt-0 hm-mb-md">{{ __('diwan.checkout.delivery') }}</h2>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="hm-field @error('country') hm-field--error @enderror">
                                    <span class="hm-field__label">{{ __('diwan.checkout.country') }}</span>
                                    <input type="text" name="country" value="{{ old('country') }}" required
                                           maxlength="100" autocomplete="country-name">
                                    @error('country')<span class="hm-field__error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label class="hm-field @error('city') hm-field--error @enderror">
                                    <span class="hm-field__label">{{ __('diwan.checkout.city') }}</span>
                                    <input type="text" name="city" value="{{ old('city') }}" required
                                           maxlength="100" autocomplete="address-level2">
                                    @error('city')<span class="hm-field__error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                            <div class="col-12">
                                <label class="hm-field @error('street') hm-field--error @enderror">
                                    <span class="hm-field__label">{{ __('diwan.checkout.street') }}</span>
                                    <input type="text" name="street" value="{{ old('street') }}" required
                                           maxlength="255" autocomplete="street-address">
                                    @error('street')<span class="hm-field__error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                            <div class="col-sm-6">
                                <label class="hm-field @error('phone') hm-field--error @enderror">
                                    <span class="hm-field__label">{{ __('diwan.checkout.phone') }}</span>
                                    <input type="tel" name="phone" value="{{ old('phone') }}" required
                                           maxlength="20" autocomplete="tel" dir="ltr">
                                    @error('phone')<span class="hm-field__error">{{ $message }}</span>@enderror
                                </label>
                            </div>
                        </div>

                        <h2 class="hm-t-lg hm-m-0 hm-mt-xl hm-mb-md">{{ __('diwan.checkout.method') }}</h2>

                        {{-- One method, stated plainly. The card fields that
                             used to sit here validated sixteen digits and a CVC
                             and then discarded them — no gateway, no storage.
                             A form that asks for a card it cannot protect is
                             worse than a form that does not ask. --}}
                        <div class="hm-note hm-note--info">
                            <x-icon name="cash-coin" />
                            <span>
                                <strong>{{ __('diwan.checkout.cod') }}</strong><br>
                                {{ __('diwan.checkout.cod_note') }}
                            </span>
                        </div>
                    </div>

                    {{-- ══ Order summary ═════════════════════════════════ --}}
                    <div class="col-lg-5">
                        <div class="hm-panel">
                            <div class="hm-panel__head">
                                <h2 class="hm-t-lg hm-m-0">{{ __('diwan.checkout.review') }}</h2>
                                <span class="hm-label">{{ trans_choice('diwan.piece_count', $items->sum('quantity')) }}</span>
                            </div>

                            @foreach($items as $item)
                                <div class="d-flex gap-3 align-items-start hm-listrow hm-listrow--sm">
                                    <x-picture class="hm-thumb hm-thumb--sm" :src="$item->product?->imageUrl() ?? asset('assets/images/product-placeholder.jpg')"
                                               alt=""
                                               sizes="3.5rem"
                                               :width="56"
                                               :height="56"
                                               loading="lazy" />
                                    <div class="hm-fill">
                                        <p class="hm-t-sm hm-strong hm-m-0">{{ $item->product?->name }}</p>
                                        <p class="hm-muted hm-num hm-t-xs hm-m-0">
                                            &times;{{ $item->quantity }}
                                        </p>
                                    </div>
                                    <span class="hm-num hm-t-sm">
                                        {{ __('diwan.common.currency') }}{{ number_format($item->price * $item->quantity, 2) }}
                                    </span>
                                </div>
                            @endforeach

                            <p class="hm-total hm-mt-sm">
                                <span class="hm-muted">{{ __('diwan.bag.subtotal') }}</span>
                                <span class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($subtotal, 2) }}</span>
                            </p>
                            <p class="hm-total">
                                <span class="hm-muted">{{ __('diwan.bag.shipping') }}</span>
                                <span>{{ __('diwan.bag.free') }}</span>
                            </p>
                            <p class="hm-total hm-total--grand">
                                <span>{{ __('diwan.bag.grand') }}</span>
                                <span class="hm-num">{{ __('diwan.common.currency') }}{{ number_format($subtotal, 2) }}</span>
                            </p>

                            <button type="submit" class="hm-btn hm-btn--primary hm-btn--block hm-btn--lg hm-mt-lg">
                                {{ __('diwan.checkout.place') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

@endsection
