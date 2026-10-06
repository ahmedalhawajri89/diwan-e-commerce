{{--
  The scale rule — layer 3 of the fit system.

  A hairline dimension rule, stopped at both ends like a drawing's, carrying
  the piece's real measurements in tabular figures. When the dimensions are
  not on file it says so; it never estimates.

  @param  \App\Models\ProductVariant|null  $variant
  @param  int  $i  position in a grid (kept for callers; the rule no longer staggers)
--}}
@props(['variant' => null, 'i' => 0])

@if($variant && $variant->width_cm && $variant->depth_cm)
    <div class="flex items-center gap-3">
        <span aria-hidden="true"
              class="relative h-px flex-1 bg-rule-2 before:absolute before:start-0 before:-top-1 before:h-2.25 before:w-px before:bg-rule-2 after:absolute after:end-0 after:-top-1 after:h-2.25 after:w-px after:bg-rule-2"></span>
        <span class="flex gap-2.5 font-mono text-[0.6875rem] text-muted" dir="ltr">
            <span><abbr title="{{ __('diwan.scale.label') }}" class="no-underline text-rule-2">{{ __('diwan.scale.width') }}</abbr> {{ $variant->width_cm }}</span>
            <span><abbr title="{{ __('diwan.scale.label') }}" class="no-underline text-rule-2">{{ __('diwan.scale.depth') }}</abbr> {{ $variant->depth_cm }}</span>
            @if($variant->height_cm)
                <span><abbr title="{{ __('diwan.scale.label') }}" class="no-underline text-rule-2">{{ __('diwan.scale.height') }}</abbr> {{ $variant->height_cm }}</span>
            @endif
        </span>
    </div>
@else
    {{-- The honest fallback: no numbers on file, and no invented ones. --}}
    <p class="m-0 text-[0.6875rem] text-muted">{{ __('diwan.scale.not_recorded') }}</p>
@endif
