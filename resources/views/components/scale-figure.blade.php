{{--
  The 175 cm human reference — the second half of layer 3.

  Tier-A/B enrichment: a hand-drawn SVG figure and a CSS box, both scaled from
  the recorded measurements. No library, no generated asset, no AR theatre.
  The piece's box is drawn at its true proportion against a person, which is
  the only way a centimetre becomes information.

  @param  \App\Models\ProductVariant|null  $variant
  @param  string|null  $name  the piece's name, for the drawing's label
--}}
@props(['variant' => null, 'name' => null])

@php
    $human = 175;                                   // reference height, cm
    $h = $variant?->height_cm;
    $w = $variant?->width_cm;
    $d = $variant?->depth_cm;

    // The tallest thing in the drawing sets the scale, so a wardrobe does not
    // overflow the frame and a stool is not drawn the same size as a sofa.
    $tallest = max($human, $h ?: 0);
    $humanPct = ($human / $tallest) * 100;
    $piecePct = $h ? ($h / $tallest) * 100 : null;

    // Width is drawn against a 4 m wall so pieces stay comparable to each other.
    $wallCm = 400;
    $widthPct = $w ? min(100, ($w / $wallCm) * 100) : null;

    // Every call site already has the product in hand, so the name is passed
    // in. Reading `$variant->product` here instead cost a query on each of the
    // three pages that draw this — the inverse relation is never eager loaded,
    // because a variant is always reached *through* its product.
    $label = $name ?? ($variant?->relationLoaded('product') ? $variant->product?->name : null);
@endphp

@if($h && $w)
    <figure class="hm-m-0">
        <div class="hm-human hm-human__base" role="img"
             aria-label="{{ $label ? $label . ' — ' : '' }}{{ $w }}×{{ $d }}×{{ $h }} {{ __('diwan.room.unit') }}. {{ __('diwan.scale.human') }}">

            <div class="hm-human__figure" style="height: {{ $humanPct }}%;">
                <span class="hm-human__mark hm-num">{{ __('diwan.scale.human_short') }}</span>
                {{-- A person, drawn plainly: head, torso, legs. Nothing cute. --}}
                <svg viewBox="0 0 44 175" width="100%" height="100%" preserveAspectRatio="xMidYMax meet"
                     fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <circle cx="22" cy="14" r="11" />
                    <path d="M22 25 V95" stroke-linecap="round" />
                    <path d="M22 40 L6 68 M22 40 L38 68" stroke-linecap="round" />
                    <path d="M22 95 L10 173 M22 95 L34 173" stroke-linecap="round" />
                </svg>
            </div>

            <div class="hm-human__piece"
                 style="height: {{ $piecePct }}%; flex: 0 0 {{ $widthPct }}%; max-width: {{ $widthPct }}%;">
                <span class="hm-num">{{ $w }}&thinsp;&times;&thinsp;{{ $h }}</span>
            </div>
        </div>
        <figcaption class="hm-muted hm-t-xs hm-mt-xs">
            {{ __('diwan.scale.caption') }}
        </figcaption>
    </figure>
@else
    <div class="hm-note">
        <x-icon name="dash-lg" />
        <span>{{ __('diwan.fit.explain.unknown') }}</span>
    </div>
@endif
