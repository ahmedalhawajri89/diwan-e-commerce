{{--
  A sparkline: the shape of a figure across the period, one point a day, no
  axis and no labels. It shows the trend under a number; the number itself
  is the reading. Drawn only when there is something to draw.

  @param  array<float|int>  $values  oldest first
--}}
@props(['values'])

@php
    $values = array_values($values);
    $n = count($values);
    $max = $n ? max($values) : 0;
    $points = $max > 0 && $n > 1
        ? collect($values)->map(fn ($v, $i) => round($i / ($n - 1) * 100, 2).','.round(22 - $v / $max * 20, 2))->implode(' ')
        : null;
@endphp

@if($points)
    <svg viewBox="0 0 100 24" preserveAspectRatio="none" {{ $attributes->merge(['class' => 'block w-full h-8']) }} aria-hidden="true" dir="ltr">
        <polygon points="0,24 {{ $points }} 100,24" class="fill-accent-wash" />
        <polyline points="{{ $points }}" class="fill-none stroke-accent-ink" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="round" />
    </svg>
@endif
