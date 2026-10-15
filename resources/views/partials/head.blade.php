{{-- Everything inside <head> that every storefront page shares: the
     character set, the viewport, the CSRF token, SEO, and the assets. Used by
     layouts/furnish and layouts/auth so the two can never drift. --}}
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">
@include('partials.favicon')
{{-- Title, description, canonical, hreflang, Open Graph and JSON-LD.
     Pages contribute through @section('description') / @section('og_image')
     and by sharing a `$schema` array; everything falls back to the brand. --}}
@include('partials.seo', [
    'seoTitle'       => trim($__env->yieldContent('title')),
    'seoDescription' => trim($__env->yieldContent('description')),
    'seoImage'       => trim($__env->yieldContent('og_image')),
    'seoType'        => trim($__env->yieldContent('og_type')) ?: 'website',
    'seoSchema'      => $schema ?? null,
])

{{-- Typefaces are bundled; see resources/css/fonts.css. --}}

@vite(['resources/css/app.css', 'resources/js/app.js'])

@include('partials.speculation')
