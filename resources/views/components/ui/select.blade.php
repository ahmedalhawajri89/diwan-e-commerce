{{--
  A labelled <select>, with the server's refusal under it like <x-ui.field>.
  The options are the slot.

  @param  string  $name
  @param  string  $label
  @param  string  $bag   the error bag
--}}
@props(['name', 'label', 'bag' => 'default'])

@php
    $id = $attributes->get('id', 'field-'.$name);
    $error = $errors->getBag($bag)->first($name);
@endphp

<div class="block">
    <label for="{{ $id }}" class="block mb-2 font-label text-xs font-medium uppercase tracking-label text-muted">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}"
            @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except('id')->merge([
                'class' => 'block w-full h-11 px-3 bg-paper border rounded-control text-sm text-ink cursor-pointer '
                         . 'focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-focus '
                         . ($error ? 'border-danger' : 'border-rule-2'),
            ]) }}>
        {{ $slot }}
    </select>
    @if($error)
        <p id="{{ $id }}-error" class="flex items-center gap-2 m-0 mt-2 text-xs text-danger">
            <x-icon name="exclamation-triangle" class="size-3.5" />{{ $error }}
        </p>
    @endif
</div>
