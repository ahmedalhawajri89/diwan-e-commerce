{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app

  The short auth flows — forgotten password, reset, confirm, verify — share one
  shape so the whole journey reads as one system rather than four screens that
  happen to be adjacent. They use the same diptych as sign-in, with a narrower
  form column, because design.md puts auth/* in the marketing family.

  Replaces layouts/guest.blade.php, which still loaded Tailwind and was the
  project's third stylesheet.

  @param  string  $heading
  @param  string  $body
  @param  string  $image  asset path for the evidence side
--}}
@extends('layouts.furnish')

@section('title', $heading)

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-dip hm-dip--even" style="align-items: center;">
                <div class="hm-dip__claim" style="max-width: 28rem;">
                    <h1 class="hm-dip__title" style="font-size: var(--text-3xl);">{{ $heading }}</h1>
                    <p class="hm-dip__body" style="font-size: var(--text-sm);">{{ $body }}</p>

                    @isset($status)
                        <p class="hm-note hm-note--ok" role="status" style="margin-bottom: var(--space-md);">
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                            <span>{{ $status }}</span>
                        </p>
                    @endisset

                    @yield('form')

                    <p class="hm-muted" style="font-size: var(--text-sm); margin-top: var(--space-lg);">
                        <a href="{{ route('login') }}" class="hm-link hm-link--sm">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i>
                            {{ __('diwan.auth.sign_in_title') }}
                        </a>
                    </p>
                </div>

                <div class="hm-dip__proof">
                    <img class="hm-dip__img" src="{{ asset($image ?? 'assets/images/login-bg.jpg') }}"
                         alt="{{ __('diwan.auth.side_alt') }}"
                         loading="lazy" decoding="async" width="1000" height="1250">
                    <p class="hm-dip__caption">{{ __('diwan.auth.side_caption') }}</p>
                </div>
            </div>
        </div>
    </section>

@endsection
