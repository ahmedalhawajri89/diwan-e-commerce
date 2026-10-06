{{--
  An amount set large: the whole currency units at full size, the cents
  smaller and quieter, so a figure reads at a glance and never breaks across
  two lines.

  @param  float  $value
--}}
@props(['value'])

@php [$whole, $cents] = explode('.', number_format($value, 2)); @endphp

<span {{ $attributes->merge(['class' => 'whitespace-nowrap']) }} dir="ltr"><span class="text-[0.55em] align-[0.6em] text-muted me-0.5">{{ __('diwan.common.currency') }}</span>{{ $whole }}<span class="text-[0.5em] text-muted">.{{ $cents }}</span></span>
