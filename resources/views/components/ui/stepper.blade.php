{{--
  A quantity with − and + either side, at 44px each so a thumb can hit them.
  The middle is a real <input type="number"> carrying the field's name, so the
  form posts the same thing with or without JavaScript. Never submits on its
  own (see resources/js/components/stepper.js).

  @param  string  $name
  @param  int     $value
  @param  int     $min
  @param  int     $max    from real stock; the + stops there
  @param  string  $label  read by screen readers; the field has no visible label
--}}
@props(['name' => 'quantity', 'value' => 1, 'min' => 1, 'max' => 999, 'label'])

@php
    $step = 'inline-flex items-center justify-center size-11 p-0 border-0 bg-transparent text-ink cursor-pointer '
          . 'hover:bg-paper-2 disabled:text-rule-2 disabled:cursor-not-allowed disabled:hover:bg-transparent '
          . 'focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus';
@endphp

<div x-data="stepper" {{ $attributes->merge(['class' => 'inline-flex items-stretch h-12 border border-rule-2 rounded-control bg-paper']) }}>
    <button type="button" class="{{ $step }} h-full" @click="dec" :disabled="atMin" aria-label="{{ __('diwan.product.decrease') }}">
        <x-icon name="dash-lg" class="size-3.5" />
    </button>
    <label class="contents">
        <span class="sr-only">{{ $label }}</span>
        <input x-ref="input" type="number" name="{{ $name }}" value="{{ $value }}" min="{{ $min }}" max="{{ $max }}"
               inputmode="numeric" dir="ltr" @change="read"
               class="w-12 min-w-0 p-0 border-0 bg-transparent text-center font-mono text-ink [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus">
    </label>
    <button type="button" class="{{ $step }} h-full" @click="inc" :disabled="atMax" aria-label="{{ __('diwan.product.increase') }}">
        <x-icon name="plus-lg" class="size-3.5" />
    </button>
</div>
