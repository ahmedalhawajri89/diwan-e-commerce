{{--
  A short message in the flow of a page: a confirmation, a warning, a fact
  worth knowing. Always a word as well as a colour.

  @param  string       $tone  info | ok | warn | danger
  @param  string|null  $icon  a sprite name; each tone has a default
--}}
@props(['tone' => 'info', 'icon' => null])

@php
    $look = [
        'ok' => 'bg-ok-wash text-ok',
        'warn' => 'bg-warn-wash text-warn',
        'danger' => 'bg-danger-wash text-danger',
    ][$tone] ?? 'bg-paper-2 text-neutral border-0 border-s-2 border-accent-ink';

    $icon ??= ['ok' => 'check-lg', 'warn' => 'exclamation-triangle', 'danger' => 'exclamation-triangle'][$tone] ?? 'rulers';
    $role = in_array($tone, ['warn', 'danger'], true) ? 'alert' : 'status';
@endphp

<div role="{{ $role }}" {{ $attributes->merge(['class' => 'flex items-start gap-3 px-4 py-3 text-sm leading-body '.$look]) }}>
    <x-icon :name="$icon" class="size-4 mt-0.5 shrink-0" />
    <div class="min-w-0">{{ $slot }}</div>
</div>
