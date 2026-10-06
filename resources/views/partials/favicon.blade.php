{{--
  The shop's mark in the browser: the د of ديوان drawn as a carpenter's
  square, in brass on the panel's espresso.

  favicon.svg for browsers that take a vector (sharp at any size); the .ico
  for the rest and for anything that asks for /favicon.ico by habit; the
  apple-touch icon and the manifest for a phone's home screen. The large
  icons carry the ruler's ticks, the small ones leave them out because at
  16px they would read as noise.

  The address-bar colour comes from config, because a view names no colour
  of its own (DesignSystemTest).
--}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">
<meta name="theme-color" content="{{ config('app.theme_color') }}">
