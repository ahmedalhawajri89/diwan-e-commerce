{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md
  The app is healthy here — the visitor simply cannot have this page — so it
  keeps the storefront around them rather than dropping them onto a bare screen.
  500 and 503 are the opposite case and stand alone; see errors/minimal.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.error.429_title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page hm-page--narrow">
            <p class="hm-num hm-muted hm-t-2xl hm-mb-xs">429</p>

            <h1 class="hm-t-2xl hm-m-0 hm-mt-0 hm-mb-sm">{{ __('diwan.error.429_title') }}</h1>
            <p class="hm-muted hm-measure hm-mb-xl">{{ __('diwan.error.429_body') }}</p>

            <hr class="hm-rule hm-mb-lg">

            <ul class="hm-plainlist hm-plainlist--stack">
                <li><a href="{{ route('furnish.index') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.home') }} <x-icon name="arrow-right" /></a></li>
                <li><a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.catalogue') }} <x-icon name="arrow-right" /></a></li>
                <li><a href="{{ route('furnish.contact') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.contact') }} <x-icon name="arrow-right" /></a></li>
            </ul>
        </div>
    </section>

@endsection
