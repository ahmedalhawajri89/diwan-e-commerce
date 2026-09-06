{{--
  A photograph, served in the smallest format and size the browser will take.

  Renders a <picture> with AVIF and WebP sources ahead of the original file.
  A browser picks the first <source> whose type it understands, and inside
  that, the width its own `sizes` calculation asks for — so a phone fetches a
  320px AVIF where it used to fetch a 1920px JPEG.

  Degrades quietly: with no derivatives on file it emits a plain <img> with
  exactly the attributes it was given, which is what an admin's photograph
  looks like between upload and the next `php artisan images:derive`.

  `sizes` is required whenever the picture is not full-viewport-width. Without
  it a browser assumes 100vw and picks a file far larger than it will draw —
  which is the bug this component exists to fix, so the value is worth
  thinking about at each call site rather than defaulting.

  @param  string       $src     URL or path, as `asset()` or ProductImage::url gives it
  @param  string       $alt     empty string for a decorative photograph
  @param  string|null  $sizes   e.g. "(max-width: 60rem) 100vw, 30rem"
  @param  int|null     $width   intrinsic width for CLS; falls back to the manifest
  @param  int|null     $height  intrinsic height for CLS
--}}
@props([
    'src',
    'alt' => '',
    'sizes' => null,
    'width' => null,
    'height' => null,
])

@php
    use App\Support\Responsive;

    $resolved = Responsive::for($src);

    // An explicit width/height wins: several layouts declare the *rendered*
    // aspect rather than the file's, because the CSS crops with object-fit,
    // and the declared pair is what reserves the right box before it loads.
    $w = $width ?? $resolved['width'];
    $h = $height ?? $resolved['height'];
@endphp

@if($resolved['sources'])
    <picture>
        @foreach($resolved['sources'] as $type => $srcset)
            <source type="{{ $type }}" srcset="{{ $srcset }}" @if($sizes) sizes="{{ $sizes }}" @endif>
        @endforeach

        <img src="{{ $src }}" alt="{{ $alt }}"
             @if($w) width="{{ $w }}" @endif
             @if($h) height="{{ $h }}" @endif
             {{ $attributes }}>
    </picture>
@else
    <img src="{{ $src }}" alt="{{ $alt }}"
         @if($w) width="{{ $w }}" @endif
         @if($h) height="{{ $h }}" @endif
         {{ $attributes }}>
@endif
