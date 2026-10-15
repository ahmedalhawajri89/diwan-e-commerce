{{--
  The shape of a figure across the period: one thin bar a day, no axis, no
  labels. Bars rather than a line because a shop's days are often empty, and
  a line through one sale in thirty days draws a ramp that never happened.
  An empty day is a short tick on the baseline, so the calendar still shows.
  Drawn only when the period holds something.

  @param  array<float|int>  $values  oldest first
--}}
@props(['values'])

@php
    $values = array_values($values);
    $n = count($values);
    $max = $n ? max($values) : 0;
@endphp

@if($max > 0)
    <svg viewBox="0 0 {{ $n * 4 }} 24" preserveAspectRatio="none" {{ $attributes->merge(['class' => 'block w-full h-8']) }} aria-hidden="true" dir="ltr">
        @foreach($values as $i => $v)
            @php $h = $v > 0 ? max($v / $max * 24, 2) : 0.75; @endphp
            <rect x="{{ $i * 4 + 0.6 }}" y="{{ 24 - $h }}" width="2.8" height="{{ $h }}"
                  class="{{ $v > 0 ? 'fill-accent-ink' : 'fill-rule' }} origin-bottom [transform-box:fill-box] motion-safe:animate-grow" />
        @endforeach
    </svg>
@endif
