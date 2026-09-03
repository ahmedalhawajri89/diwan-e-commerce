{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  Reads the reviews table where status = approved. If there are none, the page
  says so. design.md forbids inventing a testimonial to fill the space.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.testimonials.title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-dip hm-dip--even hm-rise" style="--i: 0; align-items: end;">
                <div class="hm-dip__claim">
                    <h1 class="hm-dip__title">{{ __('diwan.testimonials.lede') }}</h1>
                    <p class="hm-dip__body mb-0">{{ __('diwan.testimonials.body') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="hm-section--tight">
        <div class="hm-page">
            @if($reviews->count() > 0)
                <div class="row g-4">
                    @foreach($reviews as $index => $review)
                        <div class="col-md-6 col-lg-4">
                            <figure class="hm-panel h-100 hm-rise" style="margin: 0; --i: {{ $index }}">
                                <p class="hm-num hm-muted" style="font-size: var(--text-xs); margin-bottom: var(--space-sm);"
                                   aria-label="{{ __('diwan.testimonials.rating', ['rating' => $review->rating]) }}">
                                    {{ str_repeat('★', $review->rating) }}<span style="color: var(--color-rule-2);">{{ str_repeat('★', 5 - $review->rating) }}</span>
                                </p>

                                <blockquote style="margin: 0 0 var(--space-md); font-size: var(--text-sm); color: var(--color-ink-2);">
                                    {{ $review->comment }}
                                </blockquote>

                                <figcaption class="hm-muted" style="font-size: var(--text-xs);">
                                    <span style="color: var(--color-ink);">{{ $review->user?->name }}</span>
                                    @if($review->product)
                                        <br>
                                        <a href="{{ route('furnish.product.show', $review->product->slug) }}" class="hm-link hm-link--sm">
                                            {{ __('diwan.testimonials.on', ['product' => $review->product->name]) }}
                                        </a>
                                    @endif
                                </figcaption>
                            </figure>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: var(--space-2xl);">
                    {{ $reviews->onEachSide(1)->links() }}
                </div>
            @else
                <div class="hm-empty">
                    <p>{{ __('diwan.testimonials.empty') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.nav.catalogue') }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

@endsection
