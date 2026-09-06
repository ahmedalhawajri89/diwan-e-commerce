{{--
  Hallmark · genre: modern-minimal · theme: custom (Diwan) · design-system: design.md · designed-as-app
  nav: N11 Mega-menu panel (search-forward · my-room in the bar · grouped panel)
  footer: Ft5 Statement (one closing line · inline utility links · licence beneath)
  enrichment: Tier A/B — the CSS/SVG scale figure, and nothing else
  bidi: locale-driven dir + font pairing; see config/localization.php
--}}
@php
    $locales   = config('localization.supported');
    $current   = $locales[app()->getLocale()] ?? $locales[config('app.fallback_locale')];
    $alternate = collect($locales)->except(app()->getLocale())->take(1);

    // $navCategories and $cartCount arrive from StorefrontNavComposer, which
    // is bound to this layout in AppServiceProvider. The category row is real
    // data, not decoration: the panel lists what the catalogue actually
    // contains, with real counts.
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
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

    {{-- Typefaces are bundled; see resources/scss/furnish/_fonts.scss. --}}

    @vite(['tokens.css', 'resources/scss/furnish/style.scss', 'resources/js/furnish/main.js'])
</head>

<body>
    {{-- Every icon on the page, defined once and referenced by <use>. --}}
    @include('partials.icon-sprite')

    <a href="#content" class="hm-skip">{{ __('diwan.common.skip') }}</a>

    {{-- ══ N11 · Mega-menu nav ═════════════════════════════════════════ --}}
    <header class="hm-nav">
        <div class="hm-page">
            <div class="hm-nav__bar">
                <a href="{{ route('furnish.index') }}" class="hm-nav__brand">{{ __('diwan.brand.name') }}</a>

                {{-- Search is the widest thing in the bar because this is a
                     shop, and search is how people actually move through one. --}}
                <div class="hm-nav__search">
                    <x-icon name="search" />
                    <label for="navSearchTrigger" class="hm-visually-hidden">{{ __('diwan.search.label') }}</label>
                    <input type="search" id="navSearchTrigger" autocomplete="off"
                           placeholder="{{ __('diwan.search.placeholder') }}"
                           data-bs-toggle="modal" data-bs-target="#searchModal" readonly>
                </div>

                <div class="hm-nav__utils">
                    {{-- The fit system lives in the nav, not in a settings
                         screen: it has to be present on every page. --}}
                    <button type="button"
                            class="hm-room {{ $room ? 'hm-room--set' : '' }} d-none d-md-inline-flex"
                            data-bs-toggle="modal" data-bs-target="#roomModal">
                        <x-icon name="rulers" />
                        @if($room)
                            <span class="hm-visually-hidden">{{ __('diwan.room.label') }}:</span>
                            <span class="hm-room__value hm-num">{{ $room['width'] }}&times;{{ $room['length'] }}</span>
                        @else
                            <span>{{ __('diwan.room.label') }}</span>
                        @endif
                    </button>

                    @foreach($alternate as $code => $meta)
                        <a href="{{ route('locale.switch', $code) }}" class="hm-util hm-util--text"
                           lang="{{ $code }}" hreflang="{{ $code }}"
                           aria-label="{{ __('diwan.nav.language') }}: {{ $meta['name'] }}">
                            {{ $meta['native'] }}
                        </a>
                    @endforeach

                    {{-- Saved --}}
                    <div class="dropdown" id="wishlistDropdownContainer">
                        <button type="button" class="hm-util" id="wishlistDropdownBtn" data-bs-toggle="dropdown"
                                data-bs-auto-close="outside" aria-expanded="false"
                                aria-label="{{ __('diwan.util.saved_items') }}">
                            <x-icon name="bookmark" class="wishlist-header-icon" />
                            <span class="hm-util__count wishlist-counter-badge d-none">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end hm-dropdown-panel"
                             aria-labelledby="wishlistDropdownBtn">
                            <div class="d-flex justify-content-between align-items-center px-2 py-2 hm-ruled-b">
                                <span class="hm-label">{{ __('diwan.util.saved') }}</span>
                                <span class="hm-num hm-muted wishlist-counter-badge hm-t-xs">0</span>
                            </div>
                            <div class="hm-scroll-y" id="wishlistDropdownContent">
                                <p class="hm-muted p-3 mb-0 hm-t-sm">{{ __('diwan.wishlist.loading') }}</p>
                            </div>

                            {{-- The dropdown is a peek; the page is where pieces
                                 get filed into rooms and counted. --}}
                            @auth
                                <div class="px-2 py-2 hm-ruled-t">
                                    <a href="{{ route('rooms.index') }}" class="hm-link hm-link--sm">
                                        {{ __('diwan.rooms.title') }}
                                        <x-icon name="arrow-right" />
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>

                    {{-- Bag. `data-bag` is the hook the cart script uses to find
                         its counter. It used to look the bag up by its icon's
                         class, which made renaming a picture break a total. --}}
                    <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="hm-util" data-bag
                       aria-label="{{ $cartCount > 0 ? __('diwan.util.bag_count', ['count' => $cartCount]) : __('diwan.util.bag_empty') }}">
                        <x-icon name="bag" />
                        @if($cartCount > 0)
                            <span class="hm-util__count">{{ $cartCount }}</span>
                        @endif
                    </a>

                    {{-- Account --}}
                    @auth
                        <div class="dropdown d-none d-md-block">
                            <button type="button" class="hm-util" data-bs-toggle="dropdown" aria-expanded="false"
                                    aria-label="{{ __('diwan.account.menu_for', ['name' => auth()->user()->name]) }}">
                                <x-icon name="person" />
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="px-3 pt-2 pb-1">
                                    <span class="hm-label d-block">{{ __('diwan.account.signed_in_as') }}</span>
                                    <span class="hm-t-sm">{{ auth()->user()->name }}</span>
                                </li>
                                @if(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">{{ __('diwan.account.dashboard') }}</a></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">{{ __('diwan.orders.title') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('rooms.index') }}">{{ __('diwan.rooms.title') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('diwan.account.profile') }}</a></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item hm-danger">{{ __('diwan.account.sign_out') }}</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="hm-util d-none d-md-inline-flex"
                           aria-label="{{ __('diwan.account.sign_in') }}">
                            <x-icon name="person" />
                        </a>
                    @endauth

                    <button class="hm-util d-lg-none" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#mobileMenu" aria-controls="mobileMenu"
                            aria-label="{{ __('diwan.nav.menu') }}">
                        <x-icon name="list" />
                    </button>
                </div>
            </div>

            <nav aria-label="{{ __('diwan.nav.primary') }}">
                <ul class="hm-nav__cats">
                    <li>
                        <button type="button" class="hm-nav__cat" id="megaTrigger"
                                aria-expanded="false" aria-controls="megaPanel">
                            {{ __('diwan.nav.browse') }}
                            <x-icon name="chevron-down" />
                        </button>
                    </li>
                    <li><a class="hm-nav__cat" href="{{ route('furnish.products') }}"
                           @if(request()->routeIs('furnish.products')) aria-current="page" @endif>{{ __('diwan.nav.catalogue') }}</a></li>
                    <li><a class="hm-nav__cat" href="{{ route('furnish.lookbook') }}"
                           @if(request()->routeIs('furnish.lookbook')) aria-current="page" @endif>{{ __('diwan.nav.rooms') }}</a></li>
                    <li><a class="hm-nav__cat" href="{{ route('furnish.about') }}"
                           @if(request()->routeIs('furnish.about')) aria-current="page" @endif>{{ __('diwan.nav.story') }}</a></li>
                    <li><a class="hm-nav__cat" href="{{ route('furnish.testimonials') }}"
                           @if(request()->routeIs('furnish.testimonials')) aria-current="page" @endif>{{ __('diwan.nav.reviews') }}</a></li>
                    <li><a class="hm-nav__cat" href="{{ route('furnish.contact') }}"
                           @if(request()->routeIs('furnish.contact')) aria-current="page" @endif>{{ __('diwan.nav.contact') }}</a></li>
                </ul>
            </nav>
        </div>

        {{-- The panel. Real categories, real counts — nothing invented. --}}
        <div class="hm-mega" id="megaPanel" hidden>
            <div class="hm-page">
                <div class="hm-mega__grid">
                    <div class="hm-mega__group">
                        <h3>{{ __('diwan.nav.by_piece') }}</h3>
                        <ul>
                            @foreach($navCategories as $category)
                                <li>
                                    <a href="{{ route('furnish.category', $category->slug) }}">
                                        <span>{{ $category->name }}</span>
                                        <span class="hm-mega__count">{{ $category->products_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="hm-mega__group">
                        <h3>{{ __('diwan.catalogue.price') }}</h3>
                        <ul>
                            <li><a href="{{ route('furnish.products', ['prices' => ['under_100']]) }}"><span>{{ __('diwan.catalogue.under', ['amount' => 100]) }}</span></a></li>
                            <li><a href="{{ route('furnish.products', ['prices' => ['100_300']]) }}"><span>{{ __('diwan.catalogue.between', ['from' => 100, 'to' => 300]) }}</span></a></li>
                            <li><a href="{{ route('furnish.products', ['prices' => ['above_300']]) }}"><span>{{ __('diwan.catalogue.above', ['amount' => 300]) }}</span></a></li>
                        </ul>
                    </div>

                    <div class="hm-mega__group">
                        <h3>{{ __('diwan.nav.by_room') }}</h3>
                        <ul>
                            <li><a href="{{ route('furnish.lookbook') }}"><span>{{ __('diwan.lookbook.title') }}</span></a></li>
                            <li><a href="{{ route('furnish.products', ['sort' => 'date_new']) }}"><span>{{ __('diwan.catalogue.sorts.new') }}</span></a></li>
                            <li><a href="{{ route('furnish.products') }}"><span>{{ __('diwan.nav.shop_all') }}</span></a></li>
                        </ul>
                    </div>

                    <div class="hm-mega__group">
                        <h3>{{ __('diwan.nav.help') }}</h3>
                        <ul>
                            <li><a href="{{ route('furnish.about') }}"><span>{{ __('diwan.about.title') }}</span></a></li>
                            <li><a href="{{ route('furnish.contact') }}"><span>{{ __('diwan.contact.measure') }}</span></a></li>
                            <li><a href="{{ route('furnish.testimonials') }}"><span>{{ __('diwan.testimonials.title') }}</span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="hm-scrim" id="megaScrim" hidden></div>

    <main id="content">
        @if(session('success') || session('status'))
            <div class="hm-page hm-pt-md">
                <p class="hm-note hm-note--ok" role="status">
                    <x-icon name="check-lg" />
                    <span>{{ session('success') ?? session('status') }}</span>
                </p>
            </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
            <div class="hm-page hm-pt-md">
                <p class="hm-note hm-note--danger" role="alert">
                    <x-icon name="exclamation-triangle" />
                    <span>{{ session('error') ?? (isset($errors) ? $errors->first() : '') }}</span>
                </p>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ══ Newsletter — inline form-as-CTA, never its own card ═════════ --}}
    <section class="hm-section--tight hm-ruled-t">
        <div class="hm-page">
            <div class="row g-4 align-items-end">
                <div class="col-lg-5">
                    <h2 class="hm-t-xl hm-m-0 hm-mt-0 hm-mb-2xs">{{ __('diwan.newsletter.title') }}</h2>
                    <p class="hm-muted mb-0 hm-t-sm hm-hold-44ch">
                        {{ __('diwan.newsletter.body') }}
                    </p>
                </div>
                <div class="col-lg-7">
                    <form class="hm-form-cta" method="POST" action="{{ route('newsletter.subscribe') }}">
                        @csrf
                        <label for="newsletterEmail" class="hm-visually-hidden">{{ __('diwan.newsletter.email') }}</label>
                        <input id="newsletterEmail" type="email" name="email" required
                               placeholder="{{ __('diwan.newsletter.placeholder') }}" autocomplete="email" dir="ltr">
                        <button type="submit">{{ __('diwan.newsletter.submit') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ Ft5 · Statement footer ══════════════════════════════════════ --}}
    <footer class="hm-foot">
        <div class="hm-page">
            <p class="hm-foot__statement">{{ __('diwan.brand.tagline') }}</p>

            <div class="hm-foot__meta">
                <p class="hm-foot__name">{{ __('diwan.brand.name') }}</p>
                <ul class="hm-foot__links">
                    <li><a href="{{ route('furnish.products') }}">{{ __('diwan.nav.catalogue') }}</a></li>
                    <li><a href="{{ route('furnish.lookbook') }}">{{ __('diwan.nav.rooms') }}</a></li>
                    <li><a href="{{ route('furnish.about') }}">{{ __('diwan.nav.story') }}</a></li>
                    <li><a href="{{ route('furnish.testimonials') }}">{{ __('diwan.nav.reviews') }}</a></li>
                    <li><a href="{{ route('furnish.contact') }}">{{ __('diwan.nav.contact') }}</a></li>
                    {{-- Ft5 keeps one inline row, not four columns of links.
                         These three belong to a shop that takes money. --}}
                    <li><a href="{{ route('furnish.page', 'shipping') }}">{{ __('diwan.pages.shipping.title') }}</a></li>
                    <li><a href="{{ route('furnish.page', 'privacy') }}">{{ __('diwan.pages.privacy.title') }}</a></li>
                    <li><a href="{{ route('furnish.page', 'terms') }}">{{ __('diwan.pages.terms.title') }}</a></li>
                </ul>
            </div>

            <p class="hm-foot__legal">
                &copy; {{ date('Y') }} {{ __('diwan.brand.name') }} · {{ __('diwan.brand.colophon') }}
            </p>
        </div>
    </footer>

    {{-- ══ My room ═════════════════════════════════════════════════════ --}}
    <x-room-dialog :room="$room" />

    {{-- ══ Mobile disclosure ═══════════════════════════════════════════ --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header">
            <span class="hm-label" id="mobileMenuLabel">{{ __('diwan.nav.menu') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                    aria-label="{{ __('diwan.nav.close') }}"></button>
        </div>
        <div class="offcanvas-body">
            {{-- On a phone the room control moves in here rather than
                 disappearing: the fit system is not a desktop feature. --}}
            <button type="button" class="hm-room {{ $room ? 'hm-room--set' : '' }} w-100 justify-content-center hm-mb-lg"
                    data-bs-toggle="modal" data-bs-target="#roomModal" data-bs-dismiss="offcanvas">
                <x-icon name="rulers" />
                @if($room)
                    <span class="hm-room__value hm-num">{{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}</span>
                @else
                    <span>{{ __('diwan.room.set_prompt') }}</span>
                @endif
            </button>

            <nav aria-label="{{ __('diwan.nav.mobile') }}">
                <ul class="hm-plainlist hm-plainlist--stack">
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.index') }}">{{ __('diwan.nav.home') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.products') }}">{{ __('diwan.nav.catalogue') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.lookbook') }}">{{ __('diwan.nav.rooms') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.about') }}">{{ __('diwan.nav.story') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.testimonials') }}">{{ __('diwan.nav.reviews') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.contact') }}">{{ __('diwan.nav.contact') }}</a></li>
                </ul>
            </nav>

            <hr class="hm-rule hm-my-lg">

            @foreach($alternate as $code => $meta)
                <a href="{{ route('locale.switch', $code) }}" class="hm-link hm-link--sm d-block mb-3" lang="{{ $code }}">
                    {{ $meta['native'] }}
                </a>
            @endforeach

            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="hm-link hm-link--sm d-block mb-3">{{ __('diwan.account.dashboard') }}</a>
                @endif
                <a href="{{ route('profile.edit') }}" class="hm-link hm-link--sm d-block mb-3">{{ __('diwan.account.profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="hm-btn hm-btn--ghost hm-btn--block">{{ __('diwan.account.sign_out') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hm-btn hm-btn--primary hm-btn--block">{{ __('diwan.account.sign_in') }}</a>
            @endauth
        </div>
    </div>

    {{-- ══ Search ══════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body hm-p-xl">
                    <div class="d-flex justify-content-between align-items-baseline hm-mb-lg">
                        <span class="hm-label" id="searchModalLabel">{{ __('diwan.util.search') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="{{ __('diwan.nav.close') }}"></button>
                    </div>

                    <div class="hm-form-cta">
                        <label for="searchInput" class="hm-visually-hidden">{{ __('diwan.search.label') }}</label>
                        <input type="search" id="searchInput" autocomplete="off"
                               placeholder="{{ __('diwan.search.placeholder') }}">
                        <span class="hm-label" id="searchSpinner" hidden>{{ __('diwan.search.searching') }}</span>
                    </div>

                    <div class="hm-mt-lg" id="searchResults"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" nonce="{{ $cspNonce ?? '' }}"></script>

    {{-- Everything the front-end scripts need from the server, in one place.

         The scripts used to live in <script> blocks in this file — 431 lines of
         search, wishlist and menu logic — because they needed these nine values
         and a Blade template was the only thing that had them. They are now
         modules under resources/js/furnish, and this is the seam: the server
         writes JSON, the modules read it, and neither knows about the other.

         A data block rather than `window.X = {{ ... }}`: JSON in a
         `type="application/json"` script is never executed, so a translated
         string containing a quote cannot become code. --}}
    @php
        // Built here rather than inline in @json(...) — Blade's directive
        // argument parser cannot handle a multi-line array literal.
        $bootstrapData = [
            'authenticated' => auth()->check(),
            'csrf' => csrf_token(),

            'routes' => [
                'login' => route('login'),
                'products' => route('furnish.products'),
                'search' => route('api.search'),
                'wishlistToggle' => route('wishlist.toggle'),
                'wishlistSync' => route('wishlist.sync'),
                'wishlistDropdown' => route('wishlist.dropdown'),
                'wishlistGuestDropdown' => route('wishlist.guest.dropdown'),
                'wishlistMoveToCart' => route('wishlist.moveToCart'),
            ],

            // `:placeholders` are substituted client-side, so the JS carries no
            // English — or Arabic — of its own.
            'i18n' => [
                'searchNone' => __('diwan.search.none', ['query' => '__QUERY__']),
                'searchError' => __('diwan.search.error'),
                'browseAll' => __('diwan.search.browse_all'),
                'wishlistEmpty' => __('diwan.wishlist.empty'),
                'wishlistBrowse' => __('diwan.wishlist.browse'),
                'moveToBag' => __('diwan.wishlist.move_to_bag'),
                'moveNamed' => __('diwan.wishlist.move_named', ['name' => '__NAME__']),
                'remove' => __('diwan.wishlist.remove'),
                'removeNamed' => __('diwan.wishlist.remove_named', ['name' => '__NAME__']),
                'updateFailed' => __('diwan.wishlist.update_failed'),
                'moveFailed' => __('diwan.wishlist.move_failed'),
            ],
        ];
    @endphp
    {{-- The behaviour itself is in resources/js/furnish — search.js,
         wishlist.js and mega-menu.js, started by main.js. It used to be three
         <script> blocks here, 431 lines, unreachable by any linter or test. --}}

    <script id="diwan-data" type="application/json" nonce="{{ $cspNonce ?? '' }}">{!! json_encode($bootstrapData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>




    @stack('scripts')
</body>

</html>
