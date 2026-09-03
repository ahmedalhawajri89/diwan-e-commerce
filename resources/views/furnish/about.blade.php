{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  H2 knobs: ratio = 6/6 · right = photo · divider = negative space
  Three principles as a spec sheet (F3), not as three icon tiles.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.about.title'))

@section('content')

    <section class="hm-section">
        <div class="hm-page">
            <div class="hm-dip hm-dip--even hm-rise" style="--i: 0">
                <div class="hm-dip__claim">
                    <h1 class="hm-dip__title hm-dip__title--lg">{{ __('diwan.about.lede') }}</h1>
                    <p class="hm-dip__body">{{ __('diwan.about.body_one') }}</p>
                    <p class="hm-dip__body">{{ __('diwan.about.body_two') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.about.cta') }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="hm-dip__proof">
                    <img class="hm-dip__img hm-dip__img--wide" src="{{ asset('assets/images/about.jpg') }}"
                         alt="{{ __('diwan.about.alt_one') }}"
                         fetchpriority="high" decoding="async" width="1200" height="900">
                </div>
            </div>
        </div>
    </section>

    {{-- ══ F3 · Tabular spec sheet — the principles as rows, not tiles ══ --}}
    <section class="hm-section--tight" style="background: var(--color-paper-2); border-block: var(--rule-hair) solid var(--color-rule);">
        <div class="hm-page">
            <div class="hm-head">
                <h2 class="hm-head__title">{{ __('diwan.about.principle') }}</h2>
            </div>

            @foreach([
                ['t' => __('diwan.about.p_one_t'),   'b' => __('diwan.about.p_one_b')],
                ['t' => __('diwan.about.p_two_t'),   'b' => __('diwan.about.p_two_b')],
                ['t' => __('diwan.about.p_three_t'), 'b' => __('diwan.about.p_three_b')],
            ] as $index => $principle)
                <div class="row g-3 hm-rise" style="--i: {{ $index + 1 }}; padding-block: var(--space-lg); border-bottom: var(--rule-hair) solid var(--color-rule);">
                    <div class="col-md-4">
                        <h3 style="font-size: var(--text-lg); margin: 0;">{{ $principle['t'] }}</h3>
                    </div>
                    <div class="col-md-8">
                        <p class="hm-measure mb-0" style="color: var(--color-ink-2);">{{ $principle['b'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

@endsection
