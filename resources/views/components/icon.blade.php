{{--
  One icon, drawn from the sprite in partials/icon-sprite.blade.php.

  Default is decorative: `aria-hidden`, because almost every icon in this app
  sits beside a word or inside a control that already carries an aria-label,
  and a screen reader announcing "arrow right" after "Browse the catalogue"
  is noise.

  Pass a `label` only when the icon is the whole meaning of its element and
  nothing else names it. It becomes a real accessible name via <title>, which
  is what SVG has instead of alt text.

  @param  string       $name   sprite id without the `i-` prefix, e.g. "arrow-right"
  @param  string|null  $label  accessible name; omit for decorative icons
--}}
@props(['name', 'label' => null])

@php
    /**
     * An arrow means "onward", and onward is leftward in Arabic. Every
     * "Browse the catalogue →" on the Arabic site pointed back into its own
     * sentence: the layout around the glyph was mirrored by `dir="rtl"`, and
     * the glyph itself by nothing.
     *
     * Swapped at the source rather than flipped with `transform: scaleX(-1)`,
     * because four rules in the main stylesheet already animate these icons with a
     * transform — a hover nudge, a chevron rotation — and the last one to
     * apply would silently un-mirror the arrow.
     *
     * Only glyphs whose meaning is a direction. A bag, a person or a bookmark
     * means the same thing in both scripts, and `chevron-down` points along
     * the block axis, which RTL does not flip.
     */
    $mirrored = [
        'arrow-right' => 'arrow-left',
        'arrow-left' => 'arrow-right',
        'chevron-right' => 'chevron-left',
        'chevron-left' => 'chevron-right',
    ];

    $locales = config('localization.supported');
    $rtl = ($locales[app()->getLocale()]['dir'] ?? 'ltr') === 'rtl';

    $glyph = $rtl ? ($mirrored[$name] ?? $name) : $name;
@endphp

{{-- Sized and coloured by [data-icon] in app.css: 1em square, currentColor.
     A size-* passed in overrides it, because utilities outrank base. --}}
<svg data-icon {{ $attributes }}
     @if($label)
         role="img" aria-label="{{ $label }}"
     @else
         aria-hidden="true"
     @endif
     focusable="false"><use href="#i-{{ $glyph }}" /></svg>
