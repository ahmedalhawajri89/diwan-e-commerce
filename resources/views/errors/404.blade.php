{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  An error page is an app page: no display type above --text-2xl, no hero, no
  enrichment. It offers the two things a lost visitor actually wants — search,
  and the way back.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.error.404_title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page hm-page--narrow">
            <p class="hm-num hm-muted" style="font-size: var(--text-2xl); margin-bottom: var(--space-xs);">404</p>

            <h1 style="font-size: var(--text-2xl); margin: 0 0 var(--space-sm);">{{ __('diwan.error.404_title') }}</h1>
            <p class="hm-muted hm-measure" style="margin-bottom: var(--space-xl);">{{ __('diwan.error.404_body') }}</p>

            <form method="GET" action="{{ route('furnish.products') }}" class="hm-form-cta"
                  style="max-width: 34rem; margin-bottom: var(--space-xl);">
                <label for="notFoundSearch" class="hm-visually-hidden">{{ __('diwan.search.label') }}</label>
                <input type="search" id="notFoundSearch" name="q" placeholder="{{ __('diwan.search.placeholder') }}">
                <button type="submit">{{ __('diwan.util.search') }}</button>
            </form>

            <hr class="hm-rule" style="margin-bottom: var(--space-lg);">

            <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: var(--space-xs);">
                <li><a href="{{ route('furnish.index') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.home') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a></li>
                <li><a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.catalogue') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a></li>
                <li><a href="{{ route('furnish.lookbook') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.rooms') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a></li>
                <li><a href="{{ route('furnish.contact') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.contact') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a></li>
            </ul>
        </div>
    </section>

@endsection
