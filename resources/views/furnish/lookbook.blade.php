{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  Markers are buttons, not hover targets: a hover-only hotspot is unreachable on
  a touch screen and invisible to a keyboard. Each one opens a real product with
  its real measurements.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.lookbook.title'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-dip hm-rise" style="--i: 0; align-items: end;">
                <div class="hm-dip__claim">
                    <h1 class="hm-dip__title">{{ __('diwan.lookbook.lede') }}</h1>
                    <p class="hm-dip__body mb-0">{{ __('diwan.lookbook.body') }}</p>
                </div>
            </div>
        </div>
    </section>

    @forelse($looks as $index => $look)
        <section class="hm-section--tight"
                 @if($index > 0) style="border-top: var(--rule-hair) solid var(--color-rule);" @endif>
            <div class="hm-page">
                <div class="hm-head">
                    <h2 class="hm-head__title">{{ __('diwan.' . $look['title']) }}</h2>
                    <span class="hm-head__aside hm-label">{{ trans_choice('diwan.piece_count', count($look['hotspots'])) }}</span>
                </div>

                <div style="position: relative; border-radius: var(--radius-surface); overflow: hidden;">
                    <img src="{{ $look['image'] }}" alt="{{ __('diwan.' . $look['title']) }}"
                         loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async"
                         style="width: 100%; aspect-ratio: 16/9; object-fit: cover; background: var(--color-paper-3);"
                         width="1600" height="900">

                    @foreach($look['hotspots'] as $spot)
                        @php $product = $spot['product']; @endphp
                        @if($product)
                            <a href="{{ route('furnish.product.show', $product->slug) }}"
                               class="hm-util"
                               style="position: absolute; top: {{ $spot['top'] }}%; inset-inline-start: {{ $spot['left'] }}%;
                                      transform: translate(-50%, -50%); background: var(--color-paper);
                                      border: var(--rule-hair) solid var(--color-accent-ink); color: var(--color-accent-ink);
                                      width: 2rem; height: 2rem; min-width: 0; border-radius: var(--radius-pill);"
                               aria-label="{{ __('diwan.lookbook.marker', ['name' => $product->name]) }}">
                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                            </a>
                        @endif
                    @endforeach
                </div>

                {{-- The markers are a shortcut, not the only route. The same
                     pieces are listed beneath so the section works without
                     precise pointing, on any device. --}}
                <div class="hm-grid hm-grid--3" style="margin-top: var(--space-lg);">
                    @foreach($look['hotspots'] as $n => $spot)
                        @if($spot['product'])
                            <x-product-card :product="$spot['product']" :room="$room" :i="$n" />
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @empty
        <section class="hm-section--tight">
            <div class="hm-page">
                <div class="hm-empty">
                    <p>{{ __('diwan.lookbook.empty') }}</p>
                    <a href="{{ route('furnish.products') }}" class="hm-link">
                        {{ __('diwan.nav.catalogue') }}
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </section>
    @endforelse

@endsection
