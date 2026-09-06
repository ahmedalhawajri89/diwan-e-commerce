{{--
  The scale rule — layer 3 of the fit system.

  A hairline dimension rule carrying the piece's real measurements in tabular
  figures. When the dimensions are not on file it says so; it never estimates.

  @param  \App\Models\ProductVariant|null  $variant
  @param  int  $i  DOM index, so the draw animation staggers with its card
--}}
@props(['variant' => null, 'i' => 0])

@if($variant && $variant->width_cm && $variant->depth_cm)
    <div class="hm-scale hm-i-{{ $i }}">
        <span class="hm-scale__bar" aria-hidden="true"></span>
        <span class="hm-scale__dims hm-num">
            <span class="hm-scale__dim">
                <abbr title="{{ __('diwan.scale.label') }}">{{ __('diwan.scale.width') }}</abbr>{{ $variant->width_cm }}
            </span>
            <span class="hm-scale__dim">
                <abbr title="{{ __('diwan.scale.label') }}">{{ __('diwan.scale.depth') }}</abbr>{{ $variant->depth_cm }}
            </span>
            @if($variant->height_cm)
                <span class="hm-scale__dim">
                    <abbr title="{{ __('diwan.scale.label') }}">{{ __('diwan.scale.height') }}</abbr>{{ $variant->height_cm }}
                </span>
            @endif
        </span>
    </div>
@else
    {{-- The honest fallback. design.md gives this the force of a colour token. --}}
    <div class="hm-scale hm-scale--absent">
        <span>{{ __('diwan.scale.not_recorded') }}</span>
    </div>
@endif
