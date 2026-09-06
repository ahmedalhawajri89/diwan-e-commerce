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
            <p class="hm-num hm-muted hm-t-2xl hm-mb-xs">404</p>

            <h1 class="hm-t-2xl hm-m-0 hm-mt-0 hm-mb-sm">{{ __('diwan.error.404_title') }}</h1>
            <p class="hm-muted hm-measure hm-mb-xl">{{ __('diwan.error.404_body') }}</p>

            <form method="GET" action="{{ route('furnish.products') }}" class="hm-form-cta hm-hold-field hm-mb-xl">
                <label for="notFoundSearch" class="hm-visually-hidden">{{ __('diwan.search.label') }}</label>
                <input type="search" id="notFoundSearch" name="q" placeholder="{{ __('diwan.search.placeholder') }}">
                <button type="submit">{{ __('diwan.util.search') }}</button>
            </form>

            <hr class="hm-rule hm-mb-lg">

            <ul class="hm-plainlist hm-plainlist--stack">
                <li><a href="{{ route('furnish.index') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.home') }} <x-icon name="arrow-right" /></a></li>
                <li><a href="{{ route('furnish.products') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.catalogue') }} <x-icon name="arrow-right" /></a></li>
                <li><a href="{{ route('furnish.lookbook') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.rooms') }} <x-icon name="arrow-right" /></a></li>
                <li><a href="{{ route('furnish.contact') }}" class="hm-link hm-link--sm">{{ __('diwan.nav.contact') }} <x-icon name="arrow-right" /></a></li>
            </ul>
        </div>
    </section>

@endsection
