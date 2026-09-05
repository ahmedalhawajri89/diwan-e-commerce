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

    // The category row is real data, not decoration: the panel lists what the
    // catalogue actually contains, with real counts.
    $navCategories = \App\Models\Category::withCount(['products' => fn ($q) => $q->where('status', 'active')])
        ->orderBy('name')
        ->get();

    $cartCount = 0;
    if (auth()->check()) {
        $cartCount = (int) \App\Models\Cart::where('user_id', auth()->id())
            ->first()?->items()->sum('quantity');
    }
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('diwan.brand.strap')) — {{ __('diwan.brand.name') }}</title>
    <meta name="description" content="{{ __('diwan.brand.tagline') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(($current['font'] ?? 'latin') === 'arabic')
        {{-- Rubik 600 (display) + IBM Plex Sans Arabic (body) + Geist Mono (numerals) --}}
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600&family=IBM+Plex+Sans+Arabic:wght@400;500;600&family=Geist+Mono:wght@400;500&display=swap">
    @else
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Geist:wght@400;500;600&family=Geist+Mono:wght@400;500&display=swap">
    @endif

    @vite(['tokens.css', 'resources/scss/furnish/style.scss', 'resources/js/furnish/main.js'])
</head>

<body>
    <a href="#content" class="hm-skip">{{ __('diwan.common.skip') }}</a>

    {{-- ══ N11 · Mega-menu nav ═════════════════════════════════════════ --}}
    <header class="hm-nav">
        <div class="hm-page">
            <div class="hm-nav__bar">
                <a href="{{ route('furnish.index') }}" class="hm-nav__brand">{{ __('diwan.brand.name') }}</a>

                {{-- Search is the widest thing in the bar because this is a
                     shop, and search is how people actually move through one. --}}
                <div class="hm-nav__search">
                    <i class="bi bi-search" aria-hidden="true"></i>
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
                        <i class="bi bi-rulers" aria-hidden="true"></i>
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
                            <i class="bi bi-bookmark wishlist-header-icon" aria-hidden="true"></i>
                            <span class="hm-util__count wishlist-counter-badge d-none">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" style="width: 21rem;"
                             aria-labelledby="wishlistDropdownBtn">
                            <div class="d-flex justify-content-between align-items-center px-2 py-2"
                                 style="border-bottom: var(--rule-hair) solid var(--color-rule);">
                                <span class="hm-label">{{ __('diwan.util.saved') }}</span>
                                <span class="hm-num hm-muted wishlist-counter-badge" style="font-size: var(--text-xs);">0</span>
                            </div>
                            <div id="wishlistDropdownContent" style="max-height: 21rem; overflow-y: auto;">
                                <p class="hm-muted p-3 mb-0" style="font-size: var(--text-sm);">{{ __('diwan.wishlist.loading') }}</p>
                            </div>

                            {{-- The dropdown is a peek; the page is where pieces
                                 get filed into rooms and counted. --}}
                            @auth
                                <div class="px-2 py-2" style="border-top: var(--rule-hair) solid var(--color-rule);">
                                    <a href="{{ route('rooms.index') }}" class="hm-link hm-link--sm">
                                        {{ __('diwan.rooms.title') }}
                                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>

                    {{-- Bag --}}
                    <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="hm-util"
                       aria-label="{{ $cartCount > 0 ? __('diwan.util.bag_count', ['count' => $cartCount]) : __('diwan.util.bag_empty') }}">
                        <i class="bi bi-bag" aria-hidden="true"></i>
                        @if($cartCount > 0)
                            <span class="hm-util__count">{{ $cartCount }}</span>
                        @endif
                    </a>

                    {{-- Account --}}
                    @auth
                        <div class="dropdown d-none d-md-block">
                            <button type="button" class="hm-util" data-bs-toggle="dropdown" aria-expanded="false"
                                    aria-label="{{ __('diwan.account.menu_for', ['name' => auth()->user()->name]) }}">
                                <i class="bi bi-person" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="px-3 pt-2 pb-1">
                                    <span class="hm-label d-block">{{ __('diwan.account.signed_in_as') }}</span>
                                    <span style="font-size: var(--text-sm);">{{ auth()->user()->name }}</span>
                                </li>
                                @if(auth()->user()->roles()->whereIn('name', ['admin', 'Admin'])->exists())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">{{ __('diwan.account.dashboard') }}</a></li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">{{ __('diwan.orders.title') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('rooms.index') }}">{{ __('diwan.rooms.title') }}</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('diwan.account.profile') }}</a></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item" style="color: var(--color-danger);">{{ __('diwan.account.sign_out') }}</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="hm-util d-none d-md-inline-flex"
                           aria-label="{{ __('diwan.account.sign_in') }}">
                            <i class="bi bi-person" aria-hidden="true"></i>
                        </a>
                    @endauth

                    <button class="hm-util d-lg-none" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#mobileMenu" aria-controls="mobileMenu"
                            aria-label="{{ __('diwan.nav.menu') }}">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <nav aria-label="{{ __('diwan.nav.primary') }}">
                <ul class="hm-nav__cats">
                    <li>
                        <button type="button" class="hm-nav__cat" id="megaTrigger"
                                aria-expanded="false" aria-controls="megaPanel">
                            {{ __('diwan.nav.browse') }}
                            <i class="bi bi-chevron-down" aria-hidden="true"></i>
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
                                    <a href="{{ route('furnish.products', ['categories' => [$category->id]]) }}">
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
            <div class="hm-page" style="padding-top: var(--space-md);">
                <p class="hm-note hm-note--ok" role="status">
                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                    <span>{{ session('success') ?? session('status') }}</span>
                </p>
            </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
            <div class="hm-page" style="padding-top: var(--space-md);">
                <p class="hm-note hm-note--danger" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <span>{{ session('error') ?? (isset($errors) ? $errors->first() : '') }}</span>
                </p>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ══ Newsletter — inline form-as-CTA, never its own card ═════════ --}}
    <section class="hm-section--tight" style="border-top: var(--rule-hair) solid var(--color-rule);">
        <div class="hm-page">
            <div class="row g-4 align-items-end">
                <div class="col-lg-5">
                    <h2 style="font-size: var(--text-xl); margin: 0 0 var(--space-2xs);">{{ __('diwan.newsletter.title') }}</h2>
                    <p class="hm-muted mb-0" style="font-size: var(--text-sm); max-width: 44ch;">
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
            <button type="button" class="hm-room {{ $room ? 'hm-room--set' : '' }} w-100 justify-content-center"
                    data-bs-toggle="modal" data-bs-target="#roomModal" data-bs-dismiss="offcanvas"
                    style="margin-bottom: var(--space-lg);">
                <i class="bi bi-rulers" aria-hidden="true"></i>
                @if($room)
                    <span class="hm-room__value hm-num">{{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}</span>
                @else
                    <span>{{ __('diwan.room.set_prompt') }}</span>
                @endif
            </button>

            <nav aria-label="{{ __('diwan.nav.mobile') }}">
                <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: var(--space-xs);">
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.index') }}">{{ __('diwan.nav.home') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.products') }}">{{ __('diwan.nav.catalogue') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.lookbook') }}">{{ __('diwan.nav.rooms') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.about') }}">{{ __('diwan.nav.story') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.testimonials') }}">{{ __('diwan.nav.reviews') }}</a></li>
                    <li><a class="hm-nav__cat d-block" href="{{ route('furnish.contact') }}">{{ __('diwan.nav.contact') }}</a></li>
                </ul>
            </nav>

            <hr class="hm-rule" style="margin: var(--space-lg) 0;">

            @foreach($alternate as $code => $meta)
                <a href="{{ route('locale.switch', $code) }}" class="hm-link hm-link--sm d-block mb-3" lang="{{ $code }}">
                    {{ $meta['native'] }}
                </a>
            @endforeach

            @auth
                @if(auth()->user()->roles()->whereIn('name', ['admin', 'Admin'])->exists())
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
                <div class="modal-body" style="padding: var(--space-xl);">
                    <div class="d-flex justify-content-between align-items-baseline" style="margin-bottom: var(--space-lg);">
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

                    <div id="searchResults" style="margin-top: var(--space-lg);"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Strings the scripts need, resolved server-side so the JS carries no
         English of its own. `:placeholders` are substituted client-side. --}}
    @php
        // Built here rather than inline in @json(...) — Blade's directive
        // argument parser cannot handle a multi-line array literal.
        $i18n = [
            'searchNone'     => __('diwan.search.none', ['query' => '__QUERY__']),
            'searchError'    => __('diwan.search.error'),
            'browseAll'      => __('diwan.search.browse_all'),
            'wishlistEmpty'  => __('diwan.wishlist.empty'),
            'wishlistBrowse' => __('diwan.wishlist.browse'),
            'moveToBag'      => __('diwan.wishlist.move_to_bag'),
            'moveNamed'      => __('diwan.wishlist.move_named', ['name' => '__NAME__']),
            'remove'         => __('diwan.wishlist.remove'),
            'removeNamed'    => __('diwan.wishlist.remove_named', ['name' => '__NAME__']),
            'updateFailed'   => __('diwan.wishlist.update_failed'),
            'moveFailed'     => __('diwan.wishlist.move_failed'),
        ];
    @endphp
    <script id="diwan-i18n" type="application/json">{!! json_encode($i18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <script>
        window.FurnishI18n = JSON.parse(document.getElementById('diwan-i18n').textContent);

        // ── Search ──────────────────────────────────────────────────────
        // One modal, one listener. (The previous build shipped two modals
        // sharing an id, and the script bound to the one that never opened.)
        document.addEventListener('DOMContentLoaded', function () {
            const t = window.FurnishI18n;
            const modal = document.getElementById('searchModal');
            const input = document.getElementById('searchInput');
            const spinner = document.getElementById('searchSpinner');
            const results = document.getElementById('searchResults');
            if (!modal || !input) return;

            let timer;
            let spinnerTimer;

            modal.addEventListener('shown.bs.modal', () => input.focus());
            modal.addEventListener('hidden.bs.modal', () => {
                input.value = '';
                results.innerHTML = '';
                clearTimeout(spinnerTimer);
                spinner.hidden = true;
            });

            input.addEventListener('input', function () {
                const query = this.value.trim();
                clearTimeout(timer);
                clearTimeout(spinnerTimer);

                if (query.length < 2) {
                    results.innerHTML = '';
                    spinner.hidden = true;
                    return;
                }

                // Delay-show the spinner by 150ms so fast queries never flash it.
                spinnerTimer = setTimeout(() => { spinner.hidden = false; }, 150);

                timer = setTimeout(() => {
                    fetch('/api/search?q=' + encodeURIComponent(query))
                        .then(res => res.json())
                        .then(data => {
                            clearTimeout(spinnerTimer);
                            spinner.hidden = true;
                            render(data, query);
                        })
                        .catch(() => {
                            clearTimeout(spinnerTimer);
                            spinner.hidden = true;
                            const p = document.createElement('p');
                            p.className = 'hm-note hm-note--danger mb-0';
                            p.textContent = t.searchError;
                            results.replaceChildren(p);
                        });
                }, 300);
            });

            function render(products, query) {
                results.innerHTML = '';

                if (!products.length) {
                    const p = document.createElement('p');
                    p.className = 'hm-muted mb-0';
                    p.style.fontSize = 'var(--text-sm)';
                    p.textContent = t.searchNone.replace('__QUERY__', query);
                    results.appendChild(p);
                    return;
                }

                const list = document.createElement('div');
                products.forEach(product => {
                    const row = document.createElement('a');
                    row.href = '/product/' + product.slug;
                    row.className = 'd-flex align-items-center gap-3 py-3';
                    row.style.borderBottom = 'var(--rule-hair) solid var(--color-rule)';
                    row.style.color = 'var(--color-ink)';

                    const img = document.createElement('img');
                    img.src = product.image;
                    img.alt = '';
                    img.width = 56;
                    img.height = 72;
                    img.style.objectFit = 'cover';
                    img.style.background = 'var(--color-paper-3)';

                    const meta = document.createElement('div');
                    meta.className = 'flex-grow-1 min-w-0';

                    const name = document.createElement('div');
                    name.textContent = product.name;
                    name.style.fontWeight = '500';

                    const cat = document.createElement('div');
                    cat.className = 'hm-label';
                    cat.textContent = product.category;

                    meta.append(name, cat);

                    const price = document.createElement('div');
                    price.className = 'hm-num';
                    price.style.fontSize = 'var(--text-sm)';
                    price.dir = 'ltr';
                    price.textContent = '$' + product.price;

                    row.append(img, meta, price);
                    list.appendChild(row);
                });
                results.appendChild(list);

                if (products.length === 5) {
                    const more = document.createElement('a');
                    more.href = '/products?q=' + encodeURIComponent(query);
                    more.className = 'hm-link mt-4';
                    more.textContent = t.browseAll;
                    results.appendChild(more);
                }
            }
        });
    </script>

    <script>
        // ── Wishlist ────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function () {
            const t = window.FurnishI18n;
            const wishlistDropdownContent = document.getElementById('wishlistDropdownContent');
            const wishlistBadges = document.querySelectorAll('.wishlist-counter-badge');
            const wishlistHeaderIcon = document.querySelector('.wishlist-header-icon');
            const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
            const csrf = '{{ csrf_token() }}';

            function getGuestWishlist() {
                try {
                    return JSON.parse(localStorage.getItem('furnish_wishlist_ids')) || [];
                } catch (e) { return []; }
            }
            function setGuestWishlist(ids) {
                localStorage.setItem('furnish_wishlist_ids', JSON.stringify(ids));
            }

            if (isAuthenticated) {
                const guestIds = getGuestWishlist();
                if (guestIds.length > 0) {
                    fetch('{{ route('wishlist.sync') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: guestIds })
                    }).then(() => {
                        localStorage.removeItem('furnish_wishlist_ids');
                        fetchWishlistDropdown();
                    }).catch(() => fetchWishlistDropdown());
                } else {
                    fetchWishlistDropdown();
                }
            } else {
                fetchGuestWishlistDropdown();
            }

            function fetchGuestWishlistDropdown() {
                const guestIds = getGuestWishlist();
                if (guestIds.length === 0) {
                    updateWishlistUI([], 0);
                    return;
                }
                fetch('{{ route('wishlist.guest.dropdown') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: guestIds })
                })
                    .then(response => response.json())
                    .then(data => { if (data.success) updateWishlistUI(data.items, data.count); })
                    .catch(err => console.error('Error fetching guest wishlist:', err));
            }

            function fetchWishlistDropdown() {
                fetch('{{ route('wishlist.dropdown') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(response => response.json())
                    .then(data => { if (data.success) updateWishlistUI(data.items, data.count); })
                    .catch(err => console.error('Error fetching wishlist:', err));
            }

            function updateWishlistUI(items, count) {
                wishlistBadges.forEach(badge => {
                    badge.textContent = count;
                    badge.classList.toggle('d-none', count === 0);
                });

                if (wishlistHeaderIcon) {
                    wishlistHeaderIcon.classList.toggle('bi-bookmark-fill', count > 0);
                    wishlistHeaderIcon.classList.toggle('bi-bookmark', count === 0);
                }

                if (wishlistDropdownContent) {
                    wishlistDropdownContent.innerHTML = '';

                    if (items.length === 0) {
                        const empty = document.createElement('div');
                        empty.className = 'p-3';
                        const line = document.createElement('p');
                        line.className = 'hm-muted';
                        line.style.fontSize = 'var(--text-sm)';
                        line.textContent = t.wishlistEmpty;
                        const link = document.createElement('a');
                        link.href = '{{ route('furnish.products') }}';
                        link.className = 'hm-link hm-link--sm';
                        link.textContent = t.wishlistBrowse;
                        empty.append(line, link);
                        wishlistDropdownContent.appendChild(empty);
                    } else {
                        items.forEach(item => {
                            const row = document.createElement('div');
                            row.className = 'd-flex align-items-center gap-3 p-2';
                            row.style.borderBottom = 'var(--rule-hair) solid var(--color-rule)';

                            const thumb = document.createElement('a');
                            thumb.href = '/product/' + item.slug;
                            const img = document.createElement('img');
                            img.src = item.image;
                            img.alt = '';
                            img.width = 44;
                            img.height = 56;
                            img.style.objectFit = 'cover';
                            img.style.background = 'var(--color-paper-3)';
                            thumb.appendChild(img);

                            const meta = document.createElement('div');
                            meta.className = 'flex-grow-1 min-w-0';
                            const name = document.createElement('a');
                            name.href = '/product/' + item.slug;
                            name.className = 'd-block text-truncate';
                            name.style.color = 'var(--color-ink)';
                            name.style.fontSize = 'var(--text-sm)';
                            name.textContent = item.name;
                            const price = document.createElement('div');
                            price.className = 'hm-num hm-muted';
                            price.style.fontSize = 'var(--text-xs)';
                            price.dir = 'ltr';
                            price.textContent = '$' + item.price;
                            meta.append(name, price);

                            const actions = document.createElement('div');
                            actions.className = 'd-flex flex-column';

                            const toCart = document.createElement('button');
                            toCart.type = 'button';
                            toCart.className = 'hm-util move-to-cart-btn';
                            toCart.style.minWidth = '32px';
                            toCart.style.minHeight = '32px';
                            toCart.dataset.productId = item.product_id;
                            toCart.title = t.moveToBag;
                            toCart.setAttribute('aria-label', t.moveNamed.replace('__NAME__', item.name));
                            toCart.innerHTML = '<i class="bi bi-bag-plus" aria-hidden="true"></i>';

                            const remove = document.createElement('button');
                            remove.type = 'button';
                            remove.className = 'hm-util remove-wishlist-btn';
                            remove.style.minWidth = '32px';
                            remove.style.minHeight = '32px';
                            remove.dataset.productId = item.product_id;
                            remove.title = t.remove;
                            remove.setAttribute('aria-label', t.removeNamed.replace('__NAME__', item.name));
                            remove.innerHTML = '<i class="bi bi-x" aria-hidden="true"></i>';

                            actions.append(toCart, remove);
                            row.append(thumb, meta, actions);
                            wishlistDropdownContent.appendChild(row);
                        });

                        attachWishlistDropdownListeners();
                    }
                }

                updateProductCardHearts(items);
            }

            function updateProductCardHearts(items) {
                const saved = items.map(i => i.product_id.toString());
                document.querySelectorAll('.toggle-wishlist').forEach(btn => {
                    const pid = btn.getAttribute('data-product-id');
                    const icon = btn.querySelector('i');
                    const isSaved = saved.includes(pid);
                    btn.classList.toggle('is-saved', isSaved);
                    btn.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
                    if (icon) {
                        icon.classList.toggle('bi-bookmark-fill', isSaved);
                        icon.classList.toggle('bi-bookmark', !isSaved);
                    }
                });
            }

            function attachWishlistDropdownListeners() {
                document.querySelectorAll('.remove-wishlist-btn').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        window.toggleWishlist(this.getAttribute('data-product-id'));
                    });
                });
                document.querySelectorAll('.move-to-cart-btn').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        moveToCart(this.getAttribute('data-product-id'));
                    });
                });
            }

            // Silent success: the counter and the icon already show the result,
            // so a toast would be telling the user what they can see.
            function notifyFailure(message) {
                if (typeof window.flasher !== 'undefined') {
                    window.flasher.error(message);
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({ toast: true, position: 'bottom-end', icon: 'error', title: message, showConfirmButton: false, timer: 4000 });
                }
            }

            window.toggleWishlist = function (productId, buttonElement = null) {
                if (buttonElement) {
                    const pressed = buttonElement.getAttribute('aria-pressed') === 'true';
                    buttonElement.setAttribute('aria-pressed', pressed ? 'false' : 'true');
                    buttonElement.classList.toggle('is-saved', !pressed);
                }

                if (!isAuthenticated) {
                    let guestIds = getGuestWishlist();
                    const id = productId.toString();
                    guestIds = guestIds.includes(id) ? guestIds.filter(x => x !== id) : guestIds.concat(id);
                    setGuestWishlist(guestIds);
                    fetchGuestWishlistDropdown();
                    return;
                }

                fetch('{{ route('wishlist.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            fetchWishlistDropdown();
                        } else {
                            notifyFailure(data.message || t.updateFailed);
                        }
                    })
                    .catch(() => notifyFailure(t.updateFailed));
            };

            function moveToCart(productId) {
                if (!isAuthenticated) {
                    window.location.href = '{{ route('login') }}';
                    return;
                }

                fetch('{{ route('wishlist.moveToCart') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            notifyFailure(data.message || t.moveFailed);
                            return;
                        }
                        fetchWishlistDropdown();

                        const bagCount = document.querySelector('.bi-bag')?.parentElement?.querySelector('.hm-util__count');
                        if (bagCount) {
                            bagCount.textContent = data.cart_count;
                        } else if (data.cart_count > 0) {
                            const bag = document.querySelector('.bi-bag')?.parentElement;
                            if (bag) {
                                const badge = document.createElement('span');
                                badge.className = 'hm-util__count';
                                badge.textContent = data.cart_count;
                                bag.appendChild(badge);
                            }
                        }
                    })
                    .catch(() => notifyFailure(t.moveFailed));
            }
        });
    </script>

    <script>
        // N11 · the mega-menu panel. Click to open, click or Escape to close,
        // focus returns to the trigger. Deliberately not hover-opened: hover
        // menus are unreachable on a touch screen and hostile with a keyboard.
        document.addEventListener('DOMContentLoaded', function () {
            const trigger = document.getElementById('megaTrigger');
            const panel   = document.getElementById('megaPanel');
            const scrim   = document.getElementById('megaScrim');
            if (!trigger || !panel || !scrim) return;

            function setOpen(open) {
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                panel.hidden = !open;
                scrim.hidden = !open;
            }

            trigger.addEventListener('click', function () {
                setOpen(trigger.getAttribute('aria-expanded') !== 'true');
            });

            scrim.addEventListener('click', () => setOpen(false));

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && trigger.getAttribute('aria-expanded') === 'true') {
                    setOpen(false);
                    trigger.focus();
                }
            });

            // A pointer leaving both the panel and the bar closes it, but only
            // after a beat, so a diagonal mouse path does not slam it shut.
            let closeTimer = null;
            [panel, trigger.closest('.hm-nav')].forEach(el => {
                el.addEventListener('mouseleave', () => {
                    closeTimer = setTimeout(() => setOpen(false), 260);
                });
                el.addEventListener('mouseenter', () => clearTimeout(closeTimer));
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
