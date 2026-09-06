{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  H2 knobs: ratio = 7/5 · right = photo · divider = negative space
  The diptych alternates direction down the page: claim, evidence, claim.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.home.title'))

@section('content')

    {{-- ══ Fold I — the hero diptych ═══════════════════════════════════ --}}
    <section class="hm-section">
        <div class="hm-page">
            <div class="hm-dip hm-rise">
                <div class="hm-dip__claim">
                    <h1 class="hm-dip__title hm-dip__title--lg">{{ __('diwan.home.lede') }}</h1>
                    <p class="hm-dip__body">{{ __('diwan.home.hero_body') }}</p>
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        {{-- One primary CTA per view. The second is a link, not a button. --}}
                        <a href="{{ route('furnish.products') }}" class="hm-btn hm-btn--primary hm-btn--lg">
                            {{ __('diwan.home.hero_cta') }}
                        </a>
                        <button type="button" class="hm-link" data-bs-toggle="modal" data-bs-target="#roomModal">
                            {{ __('diwan.home.hero_second') }}
                            <x-icon name="arrow-right" />
                        </button>
                    </div>
                </div>

                <div class="hm-dip__proof">
                    <x-picture class="hm-dip__img" :src="asset('assets/images/hero-living-room.jpg')"
                               :alt="__('diwan.home.hero_alt')"
                               sizes="(max-width: 60rem) 92vw, 32rem"
                               :width="1200" :height="1500"
                               fetchpriority="high" decoding="async" />
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Fold II — the differentiator, stated plainly ════════════════ --}}
    <section class="hm-section--tight hm-on-paper-2 hm-ruled-y">
        <div class="hm-page">
            <div class="hm-dip hm-dip--flip hm-rise hm-i-1">
                <div class="hm-dip__claim">
                    <h2 class="hm-dip__title">{{ __('diwan.home.fit_title') }}</h2>
                    <p class="hm-dip__body">{{ __('diwan.home.fit_body') }}</p>

                    @if($room)
                        <p class="hm-note hm-note--ok hm-hold-field">
                            <x-icon name="check-lg" />
                            <span>
                                {{ __('diwan.room.label') }} —
                                <span class="hm-num">{{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}</span>
                            </span>
                        </p>
                    @else
                        <button type="button" class="hm-btn hm-btn--ghost" data-bs-toggle="modal" data-bs-target="#roomModal">
                            <x-icon name="rulers" />
                            {{ __('diwan.home.fit_cta') }}
                        </button>
                    @endif
                </div>

                <div class="hm-dip__proof">
                    {{-- The evidence side is the mechanism itself, drawn from a
                         real piece in the catalogue rather than illustrated. --}}
                    @php $demo = $featuredProducts->first(fn ($p) => $p->primaryVariant()?->hasDimensions()); @endphp
                    @if($demo)
                        <x-scale-figure :variant="$demo->primaryVariant()" :name="$demo->name" />
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Fold III — the catalogue band ═══════════════════════════════ --}}
    @if($featuredProducts->isNotEmpty())
        <section class="hm-section--tight">
            <div class="hm-page">
                <div class="hm-head">
                    <h2 class="hm-head__title">{{ __('diwan.home.most_saved') }}</h2>
                    <span class="hm-head__aside hm-label">{{ __('diwan.home.most_saved_note') }}</span>
                </div>

                <div class="hm-grid">
                    @foreach($featuredProducts as $index => $product)
                        <x-product-card :product="$product" :room="$room" :i="$index" />
                    @endforeach
                </div>

                <p class="hm-mt-xl">
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.home.catalogue_cta') }}
                        <x-icon name="arrow-right" />
                    </a>
                </p>
            </div>
        </section>
    @endif

    {{-- ══ Fold IV — craft, back to the original direction ═════════════ --}}
    <section class="hm-section--tight hm-ruled-t">
        <div class="hm-page">
            <div class="hm-dip">
                <div class="hm-dip__claim">
                    <h2 class="hm-dip__title">{{ __('diwan.home.craft_title') }}</h2>
                    <p class="hm-dip__body">{{ __('diwan.home.craft_body') }}</p>
                    <a href="{{ route('furnish.about') }}" class="hm-link">
                        {{ __('diwan.home.craft_cta') }}
                        <x-icon name="arrow-right" />
                    </a>
                </div>
                <div class="hm-dip__proof">
                    <x-picture class="hm-dip__img hm-dip__img--wide" :src="asset('assets/images/workshop.jpg')"
                               :alt="__('diwan.home.craft_alt')"
                               sizes="(max-width: 60rem) 92vw, 32rem"
                               :width="1200" :height="900"
                               loading="lazy" decoding="async" />
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Fold V — proof, only when it is real ════════════════════════ --}}
    @if($reviews->isNotEmpty())
        <section class="hm-section--tight hm-ruled-t">
            <div class="hm-page">
                <div class="hm-head">
                    <h2 class="hm-head__title">{{ __('diwan.home.reviews_title') }}</h2>
                    <a href="{{ route('furnish.testimonials') }}" class="hm-head__aside hm-link hm-link--sm">
                        {{ __('diwan.home.reviews_all') }}
                        <x-icon name="arrow-right" />
                    </a>
                </div>

                <div class="row g-4">
                    @foreach($reviews as $review)
                        <div class="col-md-4">
                            <figure class="hm-panel h-100 hm-m-0">
                                <x-star-rating :rating="$review->rating" class="hm-mb-xs" />
                                <blockquote class="hm-m-0 hm-mt-0 hm-mb-md hm-t-sm">
                                    {{ $review->comment }}
                                </blockquote>
                                <figcaption class="hm-muted hm-t-xs">
                                    {{ $review->user?->name }}
                                    @if($review->product)
                                        · {{ __('diwan.home.review_on', ['product' => $review->product->name]) }}
                                    @endif
                                </figcaption>
                            </figure>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection
