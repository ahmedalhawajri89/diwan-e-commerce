{{--
  The site's buttons, in one place, so a page names the kind it wants instead
  of repeating twenty utilities.

  primary  dark ink fill — one per view
  ghost    ruled outline, for the second action
  light    paper fill, for a button laid over a photograph or the deep band
  quiet    no frame, for row actions in a table (usually an icon alone)
  danger   the one filled red button, for an action that cannot be undone

  Renders an <a> when given `href`, otherwise a <button type="button">.

  @param  string       $variant  primary | ghost | light
  @param  string|null  $href
  @param  bool         $block    full width
--}}
@props(['variant' => 'primary', 'href' => null, 'block' => false])

@php
    $base = 'inline-flex items-center justify-center gap-2 min-h-12 px-7 py-3 '
          . 'rounded-control border font-label text-xs font-medium uppercase '
          . 'tracking-label no-underline cursor-pointer transition-colors duration-200 '
          . 'focus-visible:outline-2 focus-visible:outline-offset-2';

    $look = match ($variant) {
        'ghost' => 'bg-transparent text-ink border-rule-2 hover:border-ink',
        // On the deep band the focus colour is 2.92:1; brass is 8.19:1.
        'light' => 'bg-paper text-ink border-paper hover:bg-paper-2 hover:border-paper-2',
        'quiet' => 'bg-transparent text-muted border-transparent hover:text-ink hover:bg-paper-2 min-h-9! px-2.5! py-1.5!',
        'danger' => 'bg-danger text-paper border-danger hover:opacity-90',
        default => 'bg-accent text-on-accent border-accent hover:bg-accent-hover hover:border-accent-hover',
    };

    // The ring's colour is chosen once per variant, so two colours never
    // compete for the same outline.
    $ring = $variant === 'light' ? ' focus-visible:outline-brass' : ' focus-visible:outline-focus';

    $classes = $base.' '.$look.$ring.($block ? ' w-full' : '');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
