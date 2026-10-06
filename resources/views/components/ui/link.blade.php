{{--
  A text link with an arrow that leans forward on hover — the shop's second
  action, next to a filled button, or on its own. In Arabic the arrow points
  the other way; the icon component mirrors it.

  Renders an <a> when given `href`, otherwise a <button type="button"> (for
  opening a dialog with data-open-dialog).

  @param  string|null  $href
  @param  bool         $arrow
  @param  string       $tone   accent (bronze) | ink | light (on the deep band)
--}}
@props(['href' => null, 'arrow' => true, 'tone' => 'accent'])

@php
    $colour = [
        'ink' => 'text-ink hover:text-accent-ink',
        'light' => 'text-on-deep hover:text-brass',
    ][$tone] ?? 'text-accent-ink hover:text-ink';

    $classes = 'group/link inline-flex items-center gap-2 p-0 pb-1 border-0 border-b border-current '
             . 'bg-transparent text-sm no-underline cursor-pointer transition-colors '.$colour;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
        @if($arrow)<x-icon name="arrow-right" class="size-3.5 transition-transform duration-300 ease-out group-hover/link:translate-x-1 rtl:group-hover/link:-translate-x-1" />@endif
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
        @if($arrow)<x-icon name="arrow-right" class="size-3.5 transition-transform duration-300 ease-out group-hover/link:translate-x-1 rtl:group-hover/link:-translate-x-1" />@endif
    </button>
@endif
