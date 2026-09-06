{{--
  Hallmark · genre: modern-minimal · macrostructure: 15 Split Studio · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  Markers are buttons, not hover targets: a hover-only hotspot is unreachable on
  a touch screen and invisible to a keyboard. Each one opens a real product with
  its real measurements.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.lookbook.title'))
@section('description', __('diwan.lookbook.body'))

@section('content')

    <section class="hm-section--flush">
        <div class="hm-page">
            <div class="hm-dip hm-rise align-items-end">
                <div class="hm-dip__claim">
                    <h1 class="hm-dip__title">{{ __('diwan.lookbook.lede') }}</h1>
                    <p class="hm-dip__body mb-0">{{ __('diwan.lookbook.body') }}</p>
                </div>
            </div>
        </div>
    </section>

    @forelse($looks as $index => $look)
        {{-- Every look after the first opens on a rule; the first one does not,
             because the section above it already closed on one. --}}
        <section @class(['hm-section--tight', 'hm-ruled-t' => $index > 0])>
            <div class="hm-page">
                <div class="hm-head">
                    <h2 class="hm-head__title">{{ __('diwan.' . $look['title']) }}</h2>
                    <span class="hm-head__aside hm-label">{{ trans_choice('diwan.piece_count', count($look['hotspots'])) }}</span>
                </div>

                <div class="hm-frame">
                    <x-picture class="hm-media hm-media--16x9" :src="$look['image']" :alt="__('diwan.' . $look['title'])"
                               sizes="(max-width: 84rem) 100vw, 79rem"
                               :width="1600" :height="900"
                               loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async" />

                    @foreach($look['hotspots'] as $spot)
                        @php $product = $spot['product']; @endphp
                        @if($product)
                            <a href="{{ route('furnish.product.show', $product->slug) }}"
                               class="hm-util hm-util--spot"
                               {{-- Only the coordinates stay inline: where a piece sits in a
                                    photograph is data. Everything else is .hm-util--spot. --}}
                               style="top: {{ $spot['top'] }}%; inset-inline-start: {{ $spot['left'] }}%;"
                               aria-label="{{ __('diwan.lookbook.marker', ['name' => $product->name]) }}">
                                <x-icon name="plus-lg" />
                            </a>
                        @endif
                    @endforeach
                </div>

                {{-- The markers are a shortcut, not the only route. The same
                     pieces are listed beneath so the section works without
                     precise pointing, on any device. --}}
                <div class="hm-grid hm-grid--3 hm-mt-lg">
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
                        <x-icon name="arrow-right" />
                    </a>
                </div>
            </div>
        </section>
    @endforelse

@endsection
