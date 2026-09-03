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
            <div class="hm-dip hm-rise" style="--i: 0">
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
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="hm-dip__proof">
                    <img class="hm-dip__img" src="{{ asset('assets/images/couch-with-cushions-glass-table.jpg') }}"
                         alt="{{ __('diwan.home.hero_alt') }}"
                         fetchpriority="high" decoding="async" width="1200" height="1500">
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Fold II — the differentiator, stated plainly ════════════════ --}}
    <section class="hm-section--tight" style="background: var(--color-paper-2); border-block: var(--rule-hair) solid var(--color-rule);">
        <div class="hm-page">
            <div class="hm-dip hm-dip--flip hm-rise" style="--i: 1">
                <div class="hm-dip__claim">
                    <h2 class="hm-dip__title">{{ __('diwan.home.fit_title') }}</h2>
                    <p class="hm-dip__body">{{ __('diwan.home.fit_body') }}</p>

                    @if($room)
                        <p class="hm-note hm-note--ok" style="max-width: 34rem;">
                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                            <span>
                                {{ __('diwan.room.label') }} —
                                <span class="hm-num">{{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}</span>
                            </span>
                        </p>
                    @else
                        <button type="button" class="hm-btn hm-btn--ghost" data-bs-toggle="modal" data-bs-target="#roomModal">
                            <i class="bi bi-rulers" aria-hidden="true"></i>
                            {{ __('diwan.home.fit_cta') }}
                        </button>
                    @endif
                </div>

                <div class="hm-dip__proof">
                    {{-- The evidence side is the mechanism itself, drawn from a
                         real piece in the catalogue rather than illustrated. --}}
                    @php $demo = $featuredProducts->first(fn ($p) => $p->primaryVariant()?->hasDimensions()); @endphp
                    @if($demo)
                        <x-scale-figure :variant="$demo->primaryVariant()" />
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

                <p style="margin-top: var(--space-xl);">
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.home.catalogue_cta') }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </p>
            </div>
        </section>
    @endif

    {{-- ══ Fold IV — craft, back to the original direction ═════════════ --}}
    <section class="hm-section--tight" style="border-top: var(--rule-hair) solid var(--color-rule);">
        <div class="hm-page">
            <div class="hm-dip">
                <div class="hm-dip__claim">
                    <h2 class="hm-dip__title">{{ __('diwan.home.craft_title') }}</h2>
                    <p class="hm-dip__body">{{ __('diwan.home.craft_body') }}</p>
                    <a href="{{ route('furnish.about') }}" class="hm-link">
                        {{ __('diwan.home.craft_cta') }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="hm-dip__proof">
                    <img class="hm-dip__img hm-dip__img--wide" src="{{ asset('assets/images/about.jpg') }}"
                         alt="{{ __('diwan.home.craft_alt') }}"
                         loading="lazy" decoding="async" width="1200" height="900">
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Fold V — proof, only when it is real ════════════════════════ --}}
    @if($reviews->isNotEmpty())
        <section class="hm-section--tight" style="border-top: var(--rule-hair) solid var(--color-rule);">
            <div class="hm-page">
                <div class="hm-head">
                    <h2 class="hm-head__title">{{ __('diwan.home.reviews_title') }}</h2>
                    <a href="{{ route('furnish.testimonials') }}" class="hm-head__aside hm-link hm-link--sm">
                        {{ __('diwan.home.reviews_all') }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="row g-4">
                    @foreach($reviews as $review)
                        <div class="col-md-4">
                            <figure class="hm-panel h-100" style="margin: 0;">
                                <p class="hm-num hm-muted" style="font-size: var(--text-xs); margin-bottom: var(--space-xs);"
                                   aria-label="{{ __('diwan.testimonials.rating', ['rating' => $review->rating]) }}">
                                    {{ str_repeat('★', $review->rating) }}<span style="color: var(--color-rule-2);">{{ str_repeat('★', 5 - $review->rating) }}</span>
                                </p>
                                <blockquote style="margin: 0 0 var(--space-md); font-size: var(--text-sm);">
                                    {{ $review->comment }}
                                </blockquote>
                                <figcaption class="hm-muted" style="font-size: var(--text-xs);">
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
