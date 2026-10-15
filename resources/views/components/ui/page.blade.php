{{--
  The page's measure: centred, as wide as --container-page, with the gutter
  that narrows on a phone. Every storefront section sits inside one.
--}}
<div {{ $attributes->merge(['class' => 'mx-auto w-full max-w-page px-(--page-gutter)']) }}>
    {{ $slot }}
</div>
