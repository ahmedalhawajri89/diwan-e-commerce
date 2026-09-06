{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  H2 knobs: ratio = 6/6 · right = photo · divider = negative space
  Three principles as a spec sheet (F3), not as three icon tiles.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.about.title'))
@section('description', __('diwan.about.lede'))
@section('og_image', asset('assets/images/workshop.jpg'))

@section('content')

    <section class="hm-section">
        <div class="hm-page">
            <div class="hm-dip hm-dip--even hm-rise">
                <div class="hm-dip__claim">
                    <h1 class="hm-dip__title hm-dip__title--lg">{{ __('diwan.about.lede') }}</h1>
                    <p class="hm-dip__body">{{ __('diwan.about.body_one') }}</p>
                    <p class="hm-dip__body">{{ __('diwan.about.body_two') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.about.cta') }}
                        <x-icon name="arrow-right" />
                    </a>
                </div>
                <div class="hm-dip__proof">
                    <x-picture class="hm-dip__img hm-dip__img--wide" :src="asset('assets/images/workshop.jpg')"
                               :alt="__('diwan.about.alt_one')"
                               sizes="(max-width: 60rem) 92vw, 32rem"
                               :width="1200" :height="900"
                               fetchpriority="high" decoding="async" />
                </div>
            </div>
        </div>
    </section>

    {{-- ══ F3 · Tabular spec sheet — the principles as rows, not tiles ══ --}}
    <section class="hm-section--tight hm-on-paper-2 hm-ruled-y">
        <div class="hm-page">
            <div class="hm-head">
                <h2 class="hm-head__title">{{ __('diwan.about.principle') }}</h2>
            </div>

            @foreach([
                ['t' => __('diwan.about.p_one_t'),   'b' => __('diwan.about.p_one_b')],
                ['t' => __('diwan.about.p_two_t'),   'b' => __('diwan.about.p_two_b')],
                ['t' => __('diwan.about.p_three_t'), 'b' => __('diwan.about.p_three_b')],
            ] as $index => $principle)
                <div class="row g-3 hm-rise hm-i-{{ $index + 1 }} hm-listrow hm-listrow--lg">
                    <div class="col-md-4">
                        <h3 class="hm-t-lg hm-m-0">{{ $principle['t'] }}</h3>
                    </div>
                    <div class="col-md-8">
                        <p class="hm-measure mb-0 hm-ink-2">{{ $principle['b'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

@endsection
