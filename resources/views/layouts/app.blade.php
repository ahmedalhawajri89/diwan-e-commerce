{{--
  Hallmark · genre: modern-minimal · macrostructure: 13 Index-First · theme: custom (Diwan)
  design-system: design.md · designed-as-app
  nav: N3 Side-rail — design.md's explicit per-page allowance. Only app pages
  may use it, and app pages may not use the storefront's mega-menu.
  enrichment: none. Function carries the panel.

  This layout used to load Tailwind while the storefront loaded Bootstrap plus
  the token layer, so the project shipped two visual systems that could drift
  apart. It now loads the same bundle as the shop.
--}}
@php
    $locales   = config('localization.supported');
    $current   = $locales[app()->getLocale()] ?? $locales[config('app.fallback_locale')];
    $alternate = collect($locales)->except(app()->getLocale())->take(1);

    // $counts arrives from AdminNavComposer, which is bound to this layout in
    // AppServiceProvider. They are the panel's only "metric", and they are
    // real — design.md forbids the invented kind.
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? __('admin.panel') }} — {{ __('diwan.brand.name') }}</title>

    {{-- Typefaces are bundled; see resources/scss/furnish/_fonts.scss. --}}

    @vite(['tokens.css', 'resources/scss/furnish/style.scss', 'resources/js/furnish/main.js'])
</head>

<body>
    {{-- Every icon on the page, defined once and referenced by <use>. --}}
    @include('partials.icon-sprite')

    <a href="#content" class="hm-skip">{{ __('diwan.common.skip') }}</a>

    <div class="hm-app">
        {{-- ══ N3 · Side-rail ══════════════════════════════════════════ --}}
        <nav class="hm-rail-nav" aria-label="{{ __('admin.panel') }}">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="hm-rail-nav__brand">
                    {{ __('diwan.brand.name') }}
                    <span class="hm-rail-nav__tag">{{ __('admin.panel') }}</span>
                </a>

                <button type="button" class="hm-rail-nav__toggle hm-mt-sm" id="railToggle"
                        aria-expanded="false" aria-controls="railPanel">
                    <x-icon name="list" />
                    {{ __('admin.menu') }}
                </button>
            </div>

            <div id="railPanel" class="hm-rail-nav__panel d-flex flex-column hm-gap-lg hm-flex-auto">

                @php
                    $groups = [
                        'catalogue' => [
                            ['route' => 'admin.dashboard',        'icon' => 'grid',        'label' => 'overview',   'count' => null],
                            ['route' => 'admin.products.index',   'icon' => 'box-seam',    'label' => 'products',   'count' => $counts['products']],
                            ['route' => 'admin.categories.index', 'icon' => 'tags',        'label' => 'categories', 'count' => $counts['categories']],
                        ],
                        'sales' => [
                            ['route' => 'admin.orders.index',     'icon' => 'receipt',     'label' => 'orders',     'count' => $counts['orders']],
                            ['route' => 'admin.reviews.index',    'icon' => 'star',        'label' => 'reviews',    'count' => $counts['reviews']],
                        ],
                        'people' => [
                            ['route' => 'admin.users.index',      'icon' => 'person',      'label' => 'users',      'count' => $counts['users']],
                            ['route' => 'admin.contacts.index',   'icon' => 'envelope',    'label' => 'messages',   'count' => $counts['messages']],
                            ['route' => 'admin.newsletters.index','icon' => 'send',        'label' => 'newsletter', 'count' => $counts['newsletter']],
                        ],
                    ];
                @endphp

                @foreach($groups as $group => $links)
                    <div class="hm-rail-nav__group">
                        <h2>{{ __('admin.group.' . $group) }}</h2>
                        <ul>
                            @foreach($links as $link)
                                <li>
                                    <a href="{{ route($link['route']) }}" class="hm-rail-nav__link"
                                       @if(request()->routeIs(str_replace('.index', '.*', $link['route']))) aria-current="page" @endif>
                                        <x-icon :name="$link['icon']" />
                                        <span>{{ __('admin.nav.' . $link['label']) }}</span>
                                        @if($link['count'] !== null)
                                            <span class="hm-rail-nav__count">{{ $link['count'] }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                <div class="hm-rail-nav__foot">
                    <p class="hm-label hm-mb-3xs">{{ __('admin.signed_in') }}</p>
                    <p class="hm-t-sm hm-mb-sm">{{ auth()->user()?->name }}</p>

                    <a href="{{ route('furnish.index') }}" class="hm-link hm-link--sm d-block mb-2">
                        <x-icon name="arrow-left" />
                        {{ __('admin.back') }}
                    </a>
                    <a href="{{ route('profile.edit') }}" class="hm-link hm-link--sm d-block mb-2">{{ __('admin.profile') }}</a>

                    @foreach($alternate as $code => $meta)
                        <a href="{{ route('locale.switch', $code) }}" class="hm-link hm-link--sm d-block mb-3"
                           lang="{{ $code }}" hreflang="{{ $code }}">{{ $meta['native'] }}</a>
                    @endforeach

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="hm-btn hm-btn--ghost hm-btn--block">{{ __('admin.sign_out') }}</button>
                    </form>
                </div>
            </div>
        </nav>

        {{-- ══ Content ═════════════════════════════════════════════════ --}}
        <main class="hm-app__main" id="content">
            @if(session('success') || session('status'))
                <p class="hm-note hm-note--ok hm-mb-lg" role="status">
                    <x-icon name="check-lg" />
                    <span>{{ session('success') ?? session('status') }}</span>
                </p>
            @endif

            @if(session('error'))
                <p class="hm-note hm-note--danger hm-mb-lg" role="alert">
                    <x-icon name="exclamation-triangle" />
                    <span>{{ session('error') }}</span>
                </p>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="hm-note hm-note--danger hm-mb-lg" role="alert">
                    <x-icon name="exclamation-triangle" />
                    <span>
                        @foreach($errors->all() as $error)
                            {{ $error }}@if(! $loop->last)<br>@endif
                        @endforeach
                    </span>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @flasher_render

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" nonce="{{ $cspNonce ?? '' }}"></script>
    {{-- The strings the delete confirmation needs. The behaviour itself is in
         resources/js/furnish/admin.js — it used to be a <script> block here. --}}
    @php
        $adminData = [
            'admin' => [
                'confirm' => __('admin.action.confirm'),
                'confirmDelete' => __('admin.action.confirm_delete'),
                'yesDelete' => __('admin.action.yes_delete'),
                'cancel' => __('admin.action.cancel'),
            ],
        ];
    @endphp
    <script id="diwan-data" type="application/json" nonce="{{ $cspNonce ?? '' }}">{!! json_encode($adminData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    @stack('scripts')
</body>

</html>
