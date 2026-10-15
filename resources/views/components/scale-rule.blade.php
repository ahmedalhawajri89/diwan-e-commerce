{{--
  The scale rule — layer 3 of the fit system.

  A hairline dimension rule, stopped at both ends like a drawing's, carrying
  the piece's real measurements in tabular figures. When the dimensions are
  not on file it says so; it never estimates.

  Each axis is its letter and its figure, the figure isolated left-to-right.
  The whole group used to be one `dir="ltr"` run, so in Arabic the letters
  (ع م ا) were pulled into a left-to-right line and read back to front:
  "184 ع 208 م 107". Now the group runs in the page's direction and only the
  numbers are isolated. The letters are muted ink, not the rule's grey (they
  were 3.3:1), and each says its whole word to a screen reader.

  @param  \App\Models\ProductVariant|null  $variant
  @param  int  $i  position in a grid (kept for callers; the rule no longer staggers)
--}}
@props(['variant' => null, 'i' => 0])

@if($variant && $variant->width_cm && $variant->depth_cm)
    @php
        $axes = array_filter([
            'width' => $variant->width_cm,
            'depth' => $variant->depth_cm,
            'height' => $variant->height_cm,
        ]);
    @endphp
    <div class="flex items-center gap-3">
        <span aria-hidden="true"
              class="relative h-px flex-1 bg-rule-2 before:absolute before:start-0 before:-top-1 before:h-2.25 before:w-px before:bg-rule-2 after:absolute after:end-0 after:-top-1 after:h-2.25 after:w-px after:bg-rule-2"></span>
        <span class="flex gap-2.5 text-[0.6875rem] text-muted">
            @foreach($axes as $axis => $cm)
                <span class="whitespace-nowrap">
                    <span aria-hidden="true">{{ __('diwan.scale.'.$axis) }}</span>
                    <span class="sr-only">{{ __('diwan.scale.'.$axis.'_full') }}</span>
                    <bdi dir="ltr" class="font-mono text-ink-2">{{ $cm }}</bdi>
                </span>
            @endforeach
            <span class="sr-only">{{ __('diwan.room.unit') }}</span>
        </span>
    </div>
@else
    {{-- The honest fallback: no numbers on file, and no invented ones. --}}
    <p class="m-0 text-[0.6875rem] text-muted">{{ __('diwan.scale.not_recorded') }}</p>
@endif
