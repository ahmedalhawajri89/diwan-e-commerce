{{--
  The sign-in family: sign in, create account, forgotten and reset password,
  confirm password, verify email.

  Half photograph, half form, the full height of the screen. The shop's header
  and footer step aside — a visitor signing in has one thing to do — leaving a
  way back to the shop, the language switch, and the policy links.

  Child views pass:
    heading  the page's one headline
    body     a sentence under it
    status   a confirmation to show above the form (optional)
    tabs     true on sign-in and create-account, to switch between the two
  and fill @section('form'), plus @section('after') for links under it.
--}}
@php
    $locales   = config('localization.supported');
    $current   = $locales[app()->getLocale()] ?? $locales[config('app.fallback_locale')];
    $alternate = collect($locales)->except(app()->getLocale())->take(1);

    $tabs ??= false;
    $status ??= null;

    // Every link here is a 44px target: the page is used on a phone more
    // than anywhere else, and these were 16 to 34px tall.
    $tab = 'inline-flex items-end min-h-11 -mb-px pb-3 border-0 border-b-2 border-transparent font-label text-sm text-muted no-underline '
         . 'transition-colors hover:text-ink aria-[current=page]:border-ink aria-[current=page]:text-ink';
    $small = 'inline-flex items-center min-h-11 min-w-11 text-muted no-underline transition-colors hover:text-ink';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">

<head>
    @include('partials.head')
</head>

<body class="bg-paper text-ink font-body">
    @include('partials.icon-sprite')

    <a href="#content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:start-3 focus:z-50 focus:px-4 focus:py-2 focus:bg-ink focus:text-paper">
        {{ __('diwan.common.skip') }}
    </a>

    <div class="grid min-h-svh lg:grid-cols-2">

        {{-- ══ The photograph ═════════════════════════════════════════════ --}}
        <aside class="relative isolate overflow-hidden bg-deep text-on-deep h-[30svh] min-h-56 lg:order-last lg:sticky lg:top-0 lg:h-svh">
            <x-picture :src="asset('assets/images/auth-panel.jpg')"
                       :alt="__('diwan.auth.side_alt')"
                       sizes="(max-width: 64rem) 100vw, 50vw" :width="1600" :height="2000"
                       fetchpriority="high" decoding="async"
                       class="absolute inset-0 -z-10 size-full object-cover motion-safe:animate-settle" />
            <div class="absolute inset-0 -z-10 bg-linear-to-t from-scrim via-photo to-photo" aria-hidden="true"></div>

            {{-- The words, in their own box: the <picture> above is a flex item
                 of nothing, and must not take a share of the column. --}}
            <div class="flex flex-col justify-between h-full p-6 lg:p-12">

            <a href="{{ route('furnish.index') }}"
               class="self-start font-display font-semibold text-2xl tracking-[0.2em] rtl:tracking-normal text-on-deep no-underline">
                {{ __('diwan.brand.name') }}
            </a>

            <figure class="hidden lg:block m-0 max-w-[30ch] motion-safe:animate-fade-up [animation-delay:500ms]">
                <blockquote class="m-0 font-display text-3xl leading-snug text-on-deep">
                    {{ __('diwan.auth.side_quote') }}
                </blockquote>
                <figcaption class="mt-4 text-sm text-on-deep-muted">{{ __('diwan.auth.side_caption') }}</figcaption>
            </figure>
            </div>
        </aside>

        {{-- ══ The form ═══════════════════════════════════════════════════ --}}
        <div class="flex flex-col min-h-[70svh] lg:min-h-svh px-6 sm:px-12 lg:px-16">
            <header class="flex items-center justify-between gap-4 h-20 text-sm">
                <a href="{{ route('furnish.index') }}" class="group inline-flex items-center gap-2 {{ $small }}">
                    <x-icon name="arrow-left" class="size-3.5 transition-transform duration-300 group-hover:-translate-x-0.5 rtl:group-hover:translate-x-0.5" />
                    {{ __('diwan.auth.back_to_shop') }}
                </a>

                @foreach($alternate as $code => $meta)
                    <a href="{{ route('locale.switch', $code) }}" hreflang="{{ $code }}" class="{{ $small }} justify-center">
                        <span class="sr-only">{{ __('diwan.nav.language') }}:</span>
                        <span lang="{{ $code }}">{{ $meta['native'] }}</span>
                    </a>
                @endforeach
            </header>

            <main id="content" class="flex flex-1 items-center py-12">
                <div class="w-full max-w-sm mx-auto motion-safe:animate-fade-up">
                    @if($tabs)
                        <nav class="flex gap-8 mb-12 border-0 border-b border-rule" aria-label="{{ __('diwan.auth.sign_in_title') }} / {{ __('diwan.auth.register_title') }}">
                            <a href="{{ route('login') }}" class="{{ $tab }}" @if(request()->routeIs('login')) aria-current="page" @endif>{{ __('diwan.auth.sign_in_title') }}</a>
                            <a href="{{ route('register') }}" class="{{ $tab }}" @if(request()->routeIs('register')) aria-current="page" @endif>{{ __('diwan.auth.register_title') }}</a>
                        </nav>
                    @endif

                    <h1 class="m-0 font-display font-medium rtl:font-semibold text-3xl leading-snug tracking-display">{{ $heading }}</h1>
                    <p class="m-0 mt-3 text-sm leading-body text-muted">{{ $body }}</p>

                    @if($status)
                        <p role="status" class="flex items-start gap-3 m-0 mt-8 px-4 py-3 bg-ok-wash text-ok text-sm">
                            <x-icon name="check-lg" class="size-4 mt-0.5 shrink-0" />
                            <span>{{ $status }}</span>
                        </p>
                    @endif

                    @if(session('error'))
                        <p role="alert" class="flex items-start gap-3 m-0 mt-8 px-4 py-3 bg-danger-wash text-danger text-sm">
                            <x-icon name="exclamation-triangle" class="size-4 mt-0.5 shrink-0" />
                            <span>{{ session('error') }}</span>
                        </p>
                    @endif

                    <div class="mt-10">
                        @yield('form')
                    </div>

                    @hasSection('after')
                        <div class="mt-8 text-sm text-muted">
                            @yield('after')
                        </div>
                    @endif
                </div>
            </main>

            <footer class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 py-6 border-0 border-t border-rule text-xs">
                <span class="text-muted">&copy; {{ date('Y') }} {{ __('diwan.brand.name') }}</span>
                <span class="flex gap-5">
                    <a href="{{ route('furnish.page', 'privacy') }}" class="{{ $small }}">{{ __('diwan.pages.privacy.title') }}</a>
                    <a href="{{ route('furnish.page', 'terms') }}" class="{{ $small }}">{{ __('diwan.pages.terms.title') }}</a>
                </span>
            </footer>
        </div>
    </div>

    @include('partials.app-data')
</body>

</html>
