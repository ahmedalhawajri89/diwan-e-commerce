{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  Reads the reviews table where status = approved. If there are none, the page
  says so. design.md forbids inventing a testimonial to fill the space.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.testimonials.title'))
@section('description', __('diwan.testimonials.body'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-dip hm-dip--even hm-rise align-items-end">
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
                            <figure class="hm-panel h-100 hm-rise hm-m-0 hm-i-{{ $index }}">
                                <x-star-rating :rating="$review->rating" class="hm-mb-sm" />

                                <blockquote class="hm-m-0 hm-mt-0 hm-mb-md hm-t-sm hm-ink-2">
                                    {{ $review->comment }}
                                </blockquote>

                                <figcaption class="hm-muted hm-t-xs">
                                    <span class="hm-ink">{{ $review->user?->name }}</span>
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

                <div class="hm-mt-2xl">
                    {{ $reviews->onEachSide(1)->links() }}
                </div>
            @else
                <div class="hm-empty">
                    <p>{{ __('diwan.testimonials.empty') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.nav.catalogue') }}
                        <x-icon name="arrow-right" />
                    </a>
                </div>
            @endif
        </div>
    </section>

@endsection
