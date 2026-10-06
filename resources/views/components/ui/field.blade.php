{{--
  A form field: its label above, one ruled line to write on, and — when the
  server refused the value — what was wrong, in words, under it. The error
  is tied to the input with aria-describedby so a screen reader reads it too.

  @param  string       $name
  @param  string       $label
  @param  string       $type     any input type, or `textarea`; `password` gains a show/hide toggle
  @param  string|null  $value
  @param  string|null  $hint     shown when there is no error
  @param  string       $bag      the error bag, for a page with several forms

  Every other attribute (required, autocomplete, autofocus, dir, maxlength…)
  passes straight through to the <input>.
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'bag' => 'default'])

@php
    $id = $attributes->get('id', 'field-'.$name);
    $error = $errors->getBag($bag)->first($name);
    $reveal = $type === 'password';

    $line = $error ? 'border-danger' : 'border-rule-2';
@endphp

<div class="block"
     @if($reveal) x-data="passwordField" data-show="{{ __('diwan.auth.show_password') }}" data-hide="{{ __('diwan.auth.hide_password') }}" @endif>
    <label for="{{ $id }}" class="block font-label text-xs font-medium uppercase tracking-label text-muted">
        {{ $label }}
    </label>

    <div class="flex items-center gap-3 border-0 border-b {{ $line }} transition-[border-color,box-shadow] duration-200 focus-within:border-ink focus-within:shadow-[0_1px_0_0_var(--color-ink)]">
        @if($type === 'textarea')
            <textarea id="{{ $id }}" name="{{ $name }}"
                      @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                      {{ $attributes->except('id')->merge([
                          'rows' => 5,
                          'class' => 'block w-full min-w-0 py-3 px-0 border-0 bg-transparent text-base leading-body text-ink outline-none resize-y',
                      ]) }}>{{ $value }}</textarea>
        @else
            <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
                   @if(! $reveal && $value !== null) value="{{ $value }}" @endif
                   @if($reveal) x-ref="input" @endif
                   @if($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
                   {{ $attributes->except('id')->merge([
                       'class' => 'block w-full min-w-0 h-12 p-0 border-0 bg-transparent text-base text-ink outline-none '
                                . 'autofill:shadow-[inset_0_0_0_100px_var(--color-paper)]',
                   ]) }}>
        @endif

        @if($reveal)
            <button type="button" @click="toggle" :aria-pressed="shown" aria-pressed="false" aria-controls="{{ $id }}"
                    class="shrink-0 min-h-11 min-w-11 p-0 border-0 bg-transparent font-label text-xs text-muted cursor-pointer transition-colors hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                <span x-text="label">{{ __('diwan.auth.show_password') }}</span>
            </button>
        @endif
    </div>

    @if($error)
        <p id="{{ $id }}-error" class="flex items-center gap-2 m-0 mt-2 text-xs text-danger">
            <x-icon name="exclamation-triangle" class="size-3.5" />
            {{ $error }}
        </p>
    @elseif($hint)
        <p class="m-0 mt-2 text-xs text-muted">{{ $hint }}</p>
    @endif
</div>
