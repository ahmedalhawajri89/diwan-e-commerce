{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · theme: custom (Diwan)
  design-system: design.md · designed-as-app

  Shipping, privacy and terms. One template for all three — they differ only in
  which copy block they read, and a policy page that reads as a document rather
  than a landing page is the right shape for a document.

  The copy states what the code actually does, which is the only claim we can
  make honestly: the room lives in the session, payment is cash on delivery
  because no gateway exists, dimensions are recorded rather than estimated.
  Anything needing a real figure the owner has not supplied — a returns window,
  a delivery radius — is left as a labelled hole rather than invented.

  @param  string  $page  shipping | privacy | terms
--}}
@extends('layouts.furnish')

@section('title', __("diwan.pages.{$page}.title"))

@section('content')

    @php
        // Sections are numbered in the copy file, so the template does not need
        // to know how many any given page has.
        $sections = collect(range(1, 6))
            ->map(fn ($n) => [
                'title' => "diwan.pages.{$page}.s{$n}_title",
                'body'  => "diwan.pages.{$page}.s{$n}_body",
            ])
            ->filter(fn ($s) => Lang::has($s['title']));

        $others = collect(['shipping', 'privacy', 'terms'])->reject(fn ($p) => $p === $page);
    @endphp

    <section class="hm-section--flush">
        <div class="hm-page hm-page--narrow">

            <div class="hm-app__head">
                <div>
                    <h1 class="hm-app__title">{{ __("diwan.pages.{$page}.title") }}</h1>
                    <p class="hm-app__intro">{{ __("diwan.pages.{$page}.lede") }}</p>
                </div>
            </div>

            @foreach($sections as $section)
                <section style="padding-block: var(--space-lg); border-bottom: var(--rule-hair) solid var(--color-rule);">
                    <h2 style="font-size: var(--text-lg); margin: 0 0 var(--space-xs);">
                        {{ __($section['title']) }}
                    </h2>
                    <p class="hm-measure mb-0" style="color: var(--color-ink-2);">
                        {{ __($section['body']) }}
                    </p>
                </section>
            @endforeach

            {{-- The honest hole. design.md gives this the force of a colour
                 token: a policy with an invented number is worse than a policy
                 that says the number is coming. --}}
            <section style="padding-block: var(--space-lg); border-bottom: var(--rule-hair) solid var(--color-rule);">
                <p class="hm-note hm-note--warn mb-0">
                    <i class="bi bi-dash-lg" aria-hidden="true"></i>
                    <span>
                        <strong>{{ __('diwan.pages.awaiting') }}</strong> —
                        {{ __('diwan.pages.awaiting_note') }}
                    </span>
                </p>
            </section>

            <nav style="padding-top: var(--space-xl);" aria-label="{{ __('diwan.nav.help') }}">
                <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-wrap: wrap; gap: var(--space-lg);">
                    @foreach($others as $other)
                        <li>
                            <a href="{{ route('furnish.page', $other) }}" class="hm-link hm-link--sm">
                                {{ __("diwan.pages.{$other}.title") }}
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('furnish.contact') }}" class="hm-link hm-link--sm">
                            {{ __('diwan.nav.contact') }}
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    </li>
                </ul>
            </nav>

        </div>
    </section>

@endsection
