{{--
  An amount set large: the whole riyals at full size, the halalas smaller
  and quieter, the unit small on the side the language puts it ("SAR 1,499"
  / "1,499 ر.س"), so a figure reads at a glance and never breaks across two
  lines. The figure itself comes from App\Support\Money.

  @param  float  $value
--}}
@props(['value'])

@php
    $figure = App\Support\Money::figure($value);
    [$whole, $cents] = array_pad(explode('.', $figure), 2, null);
    $unit = App\Support\Money::symbol();
    $first = App\Support\Money::symbolFirst();
@endphp

<span {{ $attributes->merge(['class' => 'whitespace-nowrap']) }}>@if($first)<span class="text-[0.55em] align-[0.6em] text-muted me-1">{{ $unit }}</span>@endif<bdi dir="ltr">{{ $whole }}@if($cents)<span class="text-[0.5em] text-muted">.{{ $cents }}</span>@endif</bdi>@unless($first)<span class="text-[0.55em] align-[0.6em] text-muted ms-1">{{ $unit }}</span>@endunless</span>
