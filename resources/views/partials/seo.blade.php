{{--
  Everything a page needs to be found and to be shared.

  The site had none of it: one description — the brand tagline — repeated on
  every page, no canonical, no alternates, no Open Graph, no structured data.
  A shop that cannot be found does not sell, and every link anyone shared
  arrived as a bare blue underline with no picture and no price.

  Each page supplies what it knows through @section, and everything falls back
  to the brand. Nothing here invents a value it was not given — a page with no
  description of its own gets the brand's rather than a generated summary.

  @param  string|null  $seoTitle        set by the layout from @yield('title')
  @param  string|null  $seoDescription  @section('description')
  @param  string|null  $seoImage        @section('og_image'), an absolute URL
  @param  array        $seoSchema       JSON-LD, already an array
--}}
@php
    $locales = config('localization.supported');
    $current = app()->getLocale();
    $dir = $locales[$current]['dir'] ?? 'ltr';

    $title = trim($seoTitle ?? '') ?: __('diwan.brand.strap');
    $description = trim($seoDescription ?? '') ?: __('diwan.brand.tagline');

    // The URL without its query string. `?page=2`, `?q=sofa` and the filter
    // permutations are the same page as far as a search engine should be
    // concerned, and every one of them was previously a separate document
    // competing with the others.
    $canonical = url()->current();

    $image = trim($seoImage ?? '') ?: asset('assets/images/hero-living-room.jpg');
@endphp

<title>{{ $title }} — {{ __('diwan.brand.name') }}</title>
<meta name="description" content="{{ Str::limit(strip_tags($description), 160) }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- The same page in the other language. `x-default` points at the site's own
     default so a search engine has somewhere to send a visitor whose language
     is neither. --}}
@foreach($locales as $code => $meta)
    <link rel="alternate" hreflang="{{ $code }}" href="{{ route('locale.switch', $code) }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ route('locale.switch', config('app.fallback_locale')) }}">

{{-- Open Graph. Shared links get a picture, a title and a sentence instead of
     a bare URL — which is most of what decides whether anyone opens them. --}}
<meta property="og:type" content="{{ $seoType ?? 'website' }}">
<meta property="og:site_name" content="{{ __('diwan.brand.name') }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($description), 200) }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:locale" content="{{ $current }}">
<meta name="twitter:card" content="summary_large_image">

@if(! empty($seoSchema))
    {{-- Structured data. The fit system is the reason this is worth doing:
         a Product with real width, depth and height is exactly what a rich
         result can show, and it is the one thing this shop has that its
         competitors do not. Signed with the CSP nonce like every other
         script on the page. --}}
    <script type="application/ld+json" nonce="{{ $cspNonce ?? '' }}">
        {!! json_encode($seoSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
@endif
