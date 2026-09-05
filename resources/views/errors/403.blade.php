{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · design-system: design.md
  The app is healthy here — the visitor simply cannot have this page — so it
  keeps the storefront around them rather than dropping them onto a bare screen.
  500 and 503 are the opposite case and stand alone; see errors/minimal.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.error.403_title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page hm-page--narrow">
            <p class="hm-num hm-muted" style="font-size: var(--text-2xl); margin-bottom: var(--space-xs);">403</p>

            <h1 style="font-size: var(--text-2xl); margin: 0 0 var(--space-sm);">{{ __('diwan.error.403_title') }}</h1>
            <p class="hm-muted hm-measure" style="margin-bottom: var(--space-xl);">{{ __('diwan.error.403_body') }}</p>

            <hr class="hm-rule" style="margin-bottom: var(--space-lg);">

            <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: var(--space-xs);">
                <li><a href="{{ route('furnish.index') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.home') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a></li>
                <li><a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.catalogue') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a></li>
                <li><a href="{{ route('furnish.contact') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.contact') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a></li>
            </ul>
        </div>
    </section>

@endsection
