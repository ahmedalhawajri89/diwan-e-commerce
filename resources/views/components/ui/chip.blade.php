{{--
  A small status word on a tinted ground: an order's state, a stock level.
  Always a word; the colour only seconds it.

  @param  string  $tone  ok | warn | danger | neutral
--}}
@props(['tone' => 'neutral'])

@php
    $look = [
        'ok' => 'bg-ok-wash text-ok',
        'warn' => 'bg-warn-wash text-warn',
        'danger' => 'bg-danger-wash text-danger',
    ][$tone] ?? 'bg-paper-2 text-neutral';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-pill font-label text-[0.6875rem] font-medium leading-none whitespace-nowrap '.$look]) }}>
    <span class="size-1.5 rounded-pill bg-current" aria-hidden="true"></span>
    {{ $slot }}
</span>
