{{-- Storefront layout. Direction and font pairing follow the locale; see config/localization.php. --}}
@php
    $locales   = config('localization.supported');
    $current   = $locales[app()->getLocale()] ?? $locales[config('app.fallback_locale')];
    $alternate = collect($locales)->except(app()->getLocale())->take(1);

    // $navCategories and $cartCount arrive from StorefrontNavComposer, which
    // is bound to this layout in AppServiceProvider. The category row is real
    // data, not decoration: the panel lists what the catalogue actually
    // contains, with real counts.

    // A page that opens on a full-bleed photograph asks for the header to lie
    // clear over it with @section('header_overlay', true).
    $overlay = trim($__env->yieldContent('header_overlay')) !== '';

    // Repeated class lists, named once. `group-data-over/header:` is the
    // header lying clear over a photograph; see site-header.js.
    $page   = 'mx-auto w-full max-w-page px-(--page-gutter)';
    $util   = 'relative inline-flex items-center justify-center size-10 p-0 border-0 '
            . 'bg-transparent text-ink no-underline cursor-pointer transition-colors duration-500 hover:text-accent-ink '
            . 'group-data-over/header:text-on-deep group-data-over/header:hover:text-brass';
    $count  = 'absolute top-0.5 -end-0.5 min-w-4 h-4 px-1 rounded-pill bg-ink text-paper '
            . 'font-mono text-[0.625rem] leading-4 text-center [&[hidden]]:hidden '
            . 'group-data-over/header:bg-paper group-data-over/header:text-ink';
    $cat    = 'inline-flex items-center gap-1.5 h-12 p-0 border-0 bg-transparent font-label '
            . 'text-xs uppercase tracking-label text-ink-2 no-underline cursor-pointer '
            . 'transition-colors hover:text-ink aria-[current=page]:text-ink aria-[current=page]:underline '
            . 'underline-offset-8 duration-500 group-data-over/header:text-on-deep group-data-over/header:hover:text-brass';
    $menuItem = 'block w-full px-4 py-2.5 border-0 bg-transparent text-start text-sm '
              . 'text-ink no-underline cursor-pointer hover:bg-paper-2';
    $drawerLink = 'block py-3 border-b border-rule font-display text-2xl '
                . 'text-ink no-underline';

    $navLinks = [
        ['furnish.products', __('diwan.nav.catalogue')],
        ['furnish.lookbook', __('diwan.nav.rooms')],
        ['furnish.about', __('diwan.nav.story')],
        ['furnish.testimonials', __('diwan.nav.reviews')],
        ['furnish.contact', __('diwan.nav.contact')],
    ];
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">

<head>
    @include('partials.head')
</head>

<body class="bg-paper text-ink font-body">
    {{-- Every icon on the page, defined once and referenced by <use>. --}}
    @include('partials.icon-sprite')

    <a href="#content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:start-3 focus:z-50 focus:px-4 focus:py-2 focus:bg-ink focus:text-paper">
        {{ __('diwan.common.skip') }}
    </a>

    {{-- ══ Banner — the fit system, on every page ══════════════════════════ --}}
    <div class="bg-deep text-on-deep">
        <div class="{{ $page }} flex items-center justify-between gap-4 h-10 text-xs">
            <p class="m-0 hidden sm:block text-on-deep-muted">{{ __('diwan.brand.tagline') }}</p>

            <div class="flex items-center gap-5 ms-auto">
                <button type="button" data-open-dialog="roomModal"
                        class="inline-flex items-center gap-2 p-0 border-0 bg-transparent text-on-deep cursor-pointer hover:text-brass">
                    <x-icon name="rulers" class="size-3.5" />
                    @if($room)
                        <span class="sr-only">{{ __('diwan.room.label') }}:</span>
                        <span class="font-mono" dir="ltr">{{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}</span>
                    @else
                        <span class="underline underline-offset-4 decoration-deep-rule">{{ __('diwan.room.set_prompt') }}</span>
                    @endif
                </button>

                @foreach($alternate as $code => $meta)
                    <a href="{{ route('locale.switch', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                       class="text-on-deep no-underline hover:text-brass"
                       aria-label="{{ __('diwan.nav.language') }}: {{ $meta['name'] }}">
                        {{ $meta['native'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══ Header — steps out of the way while reading down the page ═══════ --}}
    <header x-data="siteHeader" :class="barClass" :data-over="over"
            @if($overlay) data-overlay data-over @endif
            @keydown.escape.window="dismiss" @mouseleave="leave" @mouseenter="enter"
            class="group/header sticky top-0 z-40 transition-transform duration-300 ease-out">

        <div class="relative z-20 bg-paper border-b border-rule transition-colors duration-500 group-data-over/header:bg-transparent group-data-over/header:border-transparent">
            <div class="{{ $page }} grid grid-cols-[1fr_auto_1fr] items-center h-16 lg:h-20">

                {{-- Start: the menu on a phone, search everywhere. Search is a
                     button that opens the dialog; it is not a field of its own. --}}
                <div class="flex items-center gap-1 lg:gap-3">
                    <button type="button" data-open-dialog="mobileMenu" class="{{ $util }} lg:hidden"
                            aria-label="{{ __('diwan.nav.menu') }}">
                        <x-icon name="list" class="size-5" />
                    </button>

                    <button type="button" data-open-dialog="searchModal"
                            class="{{ $util }} lg:w-auto lg:gap-2.5 lg:px-0"
                            aria-label="{{ __('diwan.search.label') }}">
                        <x-icon name="search" class="size-4.5" />
                        <span class="hidden lg:inline text-sm text-muted transition-colors duration-500 group-data-over/header:text-on-deep">{{ __('diwan.util.search') }}</span>
                    </button>
                </div>

                <a href="{{ route('furnish.index') }}"
                   class="font-display font-semibold text-[1.625rem] lg:text-[2.125rem] leading-none tracking-[0.2em] rtl:tracking-normal text-ink no-underline transition-colors duration-500 group-data-over/header:text-on-deep">
                    {{ __('diwan.brand.name') }}
                </a>

                {{-- End: saved, bag, account. --}}
                <div class="flex items-center justify-end gap-0.5 lg:gap-1.5">
                    <div x-data="dropdown" @click.outside="close" @keydown.escape="dismiss" class="relative" id="wishlistDropdownContainer">
                        <button type="button" id="wishlistDropdownBtn" x-ref="button" @click="toggle" :aria-expanded="open"
                                aria-expanded="false" aria-controls="wishlistDropdownPanel"
                                class="{{ $util }}" aria-label="{{ __('diwan.util.saved_items') }}">
                            <x-icon name="bookmark" class="wishlist-header-icon size-4.5" />
                            <span class="wishlist-counter-badge {{ $count }}" hidden>0</span>
                        </button>

                        <div id="wishlistDropdownPanel" x-show="open" x-cloak
                             class="absolute top-full end-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-paper border border-rule z-30">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-rule">
                                <span class="text-xs uppercase tracking-label text-muted">{{ __('diwan.util.saved') }}</span>
                                <span class="wishlist-counter-badge font-mono text-xs text-muted [&[hidden]]:hidden" hidden>0</span>
                            </div>
                            <div class="max-h-96 overflow-y-auto" id="wishlistDropdownContent">
                                <p class="m-0 p-4 text-sm text-muted">{{ __('diwan.wishlist.loading') }}</p>
                            </div>

                            {{-- The dropdown is a peek; the page is where pieces
                                 get filed into rooms and counted. --}}
                            @auth
                                <div class="px-4 py-3 border-t border-rule">
                                    <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-1.5 text-sm text-accent-ink no-underline hover:underline">
                                        {{ __('diwan.rooms.title') }}
                                        <x-icon name="arrow-right" class="size-3.5" />
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>

                    {{-- Bag. `data-bag` is the hook the cart script uses to find
                         its counter; `data-bag-count` is the counter itself. --}}
                    <a href="{{ auth()->check() ? route('cart.index') : route('login') }}" class="{{ $util }}" data-bag
                       aria-label="{{ $cartCount > 0 ? __('diwan.util.bag_count', ['count' => $cartCount]) : __('diwan.util.bag_empty') }}">
                        <x-icon name="bag" class="size-4.5" />
                        <span class="{{ $count }}" data-bag-count @if($cartCount === 0) hidden @endif>{{ $cartCount }}</span>
                    </a>

                    @auth
                        <div x-data="dropdown" @click.outside="close" @keydown.escape="dismiss" class="relative hidden md:block">
                            <button type="button" x-ref="button" @click="toggle" :aria-expanded="open" aria-expanded="false"
                                    class="{{ $util }}"
                                    aria-label="{{ __('diwan.account.menu_for', ['name' => auth()->user()->name]) }}">
                                <x-icon name="person" class="size-4.5" />
                            </button>
                            <div x-show="open" x-cloak
                                 class="absolute top-full end-0 mt-2 w-60 py-2 bg-paper border border-rule z-30">
                                <div class="px-4 pt-1 pb-3 mb-1 border-b border-rule">
                                    <span class="block text-xs uppercase tracking-label text-muted">{{ __('diwan.account.signed_in_as') }}</span>
                                    <span class="text-sm">{{ auth()->user()->name }}</span>
                                </div>
                                @if(auth()->user()->isAdmin())
                                    <a class="{{ $menuItem }}" href="{{ route('admin.dashboard') }}">{{ __('diwan.account.dashboard') }}</a>
                                @endif
                                <a class="{{ $menuItem }}" href="{{ route('orders.index') }}">{{ __('diwan.orders.title') }}</a>
                                <a class="{{ $menuItem }}" href="{{ route('rooms.index') }}">{{ __('diwan.rooms.title') }}</a>
                                <a class="{{ $menuItem }}" href="{{ route('profile.edit') }}">{{ __('diwan.account.profile') }}</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="{{ $menuItem }} text-danger">{{ __('diwan.account.sign_out') }}</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="{{ $util }} hidden md:inline-flex"
                           aria-label="{{ __('diwan.account.sign_in') }}">
                            <x-icon name="person" class="size-4.5" />
                        </a>
                    @endauth
                </div>
            </div>

            {{-- The category row. Desktop only; a phone gets the menu. --}}
            <nav aria-label="{{ __('diwan.nav.primary') }}" class="hidden lg:block border-t border-rule transition-colors duration-500 group-data-over/header:border-transparent">
                <ul class="{{ $page }} flex items-center justify-center gap-10 m-0 list-none">
                    <li>
                        <button type="button" class="{{ $cat }}" id="megaTrigger" x-ref="trigger"
                                @click="toggle" :aria-expanded="open" aria-expanded="false" aria-controls="megaPanel">
                            {{ __('diwan.nav.browse') }}
                            <x-icon name="chevron-down" class="size-3" />
                        </button>
                    </li>
                    @foreach($navLinks as [$name, $label])
                        <li>
                            <a class="{{ $cat }}" href="{{ route($name) }}"
                               @if(request()->routeIs($name)) aria-current="page" @endif>{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>

        {{-- The panel. Real categories, real counts — nothing invented. --}}
        <div id="megaPanel" x-show="open" x-cloak
             class="absolute inset-x-0 top-full z-20 bg-paper border-b border-rule">
            <div class="{{ $page }} grid grid-cols-4 gap-10 py-10">
                @php
                    $groupTitle = 'm-0 mb-4 font-label text-xs font-medium uppercase tracking-label text-muted';
                    $groupLink = 'flex justify-between gap-4 py-1.5 text-ink no-underline hover:text-accent-ink';
                    $groups = [
                        __('diwan.catalogue.price') => [
                            [route('furnish.products', ['prices' => ['under_100']]), __('diwan.catalogue.under', ['amount' => 100])],
                            [route('furnish.products', ['prices' => ['100_300']]), __('diwan.catalogue.between', ['from' => 100, 'to' => 300])],
                            [route('furnish.products', ['prices' => ['above_300']]), __('diwan.catalogue.above', ['amount' => 300])],
                        ],
                        __('diwan.nav.by_room') => [
                            [route('furnish.lookbook'), __('diwan.lookbook.title')],
                            [route('furnish.products', ['sort' => 'date_new']), __('diwan.catalogue.sorts.new')],
                            [route('furnish.products'), __('diwan.nav.shop_all')],
                        ],
                        __('diwan.nav.help') => [
                            [route('furnish.about'), __('diwan.about.title')],
                            [route('furnish.contact'), __('diwan.contact.measure')],
                            [route('furnish.testimonials'), __('diwan.testimonials.title')],
                        ],
                    ];
                @endphp

                <div>
                    <h3 class="{{ $groupTitle }}">{{ __('diwan.nav.by_piece') }}</h3>
                    <ul class="m-0 p-0 list-none">
                        @foreach($navCategories as $category)
                            <li>
                                <a class="{{ $groupLink }}" href="{{ route('furnish.category', $category->slug) }}">
                                    <span>{{ $category->name }}</span>
                                    <span class="font-mono text-xs text-muted">{{ $category->products_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @foreach($groups as $title => $links)
                    <div>
                        <h3 class="{{ $groupTitle }}">{{ $title }}</h3>
                        <ul class="m-0 p-0 list-none">
                            @foreach($links as [$href, $label])
                                <li><a class="{{ $groupLink }}" href="{{ $href }}">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Behind the panel, over the page. Inside the header so it shares the
             component; the header has no transform while the panel is open, so
             `fixed` still means the viewport. --}}
        <div x-show="open" x-cloak @click="close" class="fixed inset-0 z-10 bg-scrim"></div>
    </header>

    <main id="content">
        @if(session('success') || session('status'))
            <div class="{{ $page }} pt-6">
                <p role="status" class="flex items-center gap-3 m-0 px-4 py-3 bg-ok-wash text-ok text-sm">
                    <x-icon name="check-lg" class="size-4" />
                    <span>{{ session('success') ?? session('status') }}</span>
                </p>
            </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
            <div class="{{ $page }} pt-6">
                <p role="alert" class="flex items-center gap-3 m-0 px-4 py-3 bg-danger-wash text-danger text-sm">
                    <x-icon name="exclamation-triangle" class="size-4" />
                    <span>{{ session('error') ?? (isset($errors) ? $errors->first() : '') }}</span>
                </p>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- ══ Footer ══════════════════════════════════════════════════════════
         A statement, not a sitemap (design.md § Nav and footer): the letter,
         one closing line with the room control, one row of the links a
         visitor actually looks for at the bottom of a page, then the tape.
         Categories and the account live in the header; repeating them here
         in columns is the generated-footer fingerprint the system bans. --}}
    @php
        $footLink = 'inline-flex items-center min-h-11 text-on-deep-muted no-underline underline-offset-4 transition-colors duration-300 hover:text-on-deep hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass';
    @endphp
    <footer class="relative overflow-hidden bg-deep text-on-deep">
        <div class="{{ $page }}">

            {{-- The letter: one line to write on, nothing boxed. --}}
            <div class="grid gap-10 lg:grid-cols-2 lg:items-end pt-20 lg:pt-28 pb-16 border-b border-deep-rule" data-reveal>
                <div>
                    <h2 class="m-0 text-on-deep font-display font-medium rtl:font-semibold text-display-s leading-tight">{{ __('diwan.newsletter.title') }}</h2>
                    <p class="m-0 mt-4 max-w-[44ch] text-sm leading-body text-on-deep-muted">{{ __('diwan.newsletter.body') }}</p>
                </div>

                <form method="POST" action="{{ route('newsletter.subscribe') }}"
                      class="group/nl min-w-0 flex items-center gap-4 border-0 border-b border-on-deep-muted transition-colors duration-300 focus-within:border-brass">
                    @csrf
                    <label for="newsletterEmail" class="sr-only">{{ __('diwan.newsletter.email') }}</label>
                    <input id="newsletterEmail" type="email" name="email" required dir="ltr"
                           placeholder="{{ __('diwan.newsletter.placeholder') }}" autocomplete="email"
                           class="flex-1 min-w-0 h-16 p-0 border-0 bg-transparent font-display text-2xl text-on-deep placeholder:text-on-deep-muted outline-none">
                    <button type="submit"
                            class="inline-flex items-center gap-3 shrink-0 h-16 p-0 border-0 bg-transparent font-label text-xs font-medium uppercase tracking-label text-on-deep cursor-pointer transition-colors hover:text-brass focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brass">
                        {{ __('diwan.newsletter.submit') }}
                        <x-icon name="arrow-right" class="size-4 transition-transform duration-300 ease-out group-hover/nl:translate-x-1 rtl:group-hover/nl:-translate-x-1" />
                    </button>
                </form>
            </div>

            {{-- The closing line, and the way around in one row. --}}
            <div class="grid gap-10 lg:grid-cols-12 lg:items-end py-16 lg:py-20" data-reveal-group>
                <div class="lg:col-span-5" data-reveal>
                    <p class="m-0 font-display text-2xl leading-snug text-on-deep max-w-[22ch]">{{ __('diwan.brand.tagline') }}</p>

                    {{-- The fit system is the shop's promise, so its control
                         is here too: the visitor's room, or the way to set it. --}}
                    <button type="button" data-open-dialog="roomModal"
                            class="inline-flex items-center gap-3 min-h-11 mt-8 px-4 py-3 border border-deep-rule bg-deep-2 text-sm text-on-deep cursor-pointer transition-colors hover:border-brass focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brass">
                        <x-icon name="rulers" class="size-4 text-brass" />
                        @if($room)
                            <span>{{ __('diwan.footer.room') }}</span>
                            <span class="font-mono text-on-deep-muted" dir="ltr">{{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}</span>
                        @else
                            <span>{{ __('diwan.room.set_prompt') }}</span>
                        @endif
                    </button>
                </div>

                <nav class="lg:col-span-7" aria-label="{{ __('diwan.nav.help') }}" data-reveal>
                    <ul class="m-0 p-0 list-none flex flex-wrap gap-x-6 lg:justify-end text-sm">
                        <li><a class="{{ $footLink }}" href="{{ route('furnish.products') }}">{{ __('diwan.nav.shop_all') }}</a></li>
                        <li><a class="{{ $footLink }}" href="{{ route('furnish.about') }}">{{ __('diwan.nav.story') }}</a></li>
                        <li><a class="{{ $footLink }}" href="{{ route('furnish.contact') }}">{{ __('diwan.nav.contact') }}</a></li>
                        <li><a class="{{ $footLink }}" href="{{ route('furnish.page', 'shipping') }}">{{ __('diwan.pages.shipping.title') }}</a></li>
                        <li><a class="{{ $footLink }}" href="{{ route('furnish.page', 'privacy') }}">{{ __('diwan.pages.privacy.title') }}</a></li>
                        <li><a class="{{ $footLink }}" href="{{ route('furnish.page', 'terms') }}">{{ __('diwan.pages.terms.title') }}</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        {{-- The tape measure: the shop's promise, drawn rather than said.
             Six metres in centimetres, with a brass mark at the visitor's own
             room width — or, before they have given one, at the 175 cm person
             every product drawing stands beside. The tape reads left to right
             in both languages, as a tape does. --}}
        @php
            $tapeSpan = 600;
            $markCm = $room['width'] ?? 175;
            $markLabel = $room
                ? __('diwan.footer.ruler_room', ['cm' => $room['width']])
                : __('diwan.footer.ruler_person');
        @endphp
        <div class="{{ $page }} pb-14">
            <div class="relative pt-12" dir="ltr">
                {{-- The mark. --}}
                <span x-data="rulerMark" data-cm="{{ $markCm }}" data-span="{{ $tapeSpan }}"
                      class="absolute top-0 bottom-0 left-0 z-10 flex flex-col items-center -translate-x-1/2 pointer-events-none motion-safe:transition-[left] motion-safe:duration-[1200ms] motion-safe:ease-out">
                    <span class="whitespace-nowrap px-2.5 py-1 rounded-pill bg-brass text-deep font-mono text-[0.6875rem]">{{ $markLabel }}</span>
                    <span class="block w-px flex-1 bg-brass"></span>
                </span>

                {{-- The ticks: every 5 cm, longer every 10, tallest every 50. --}}
                <div data-draw aria-hidden="true">
                    <svg viewBox="0 0 {{ $tapeSpan }} 28" preserveAspectRatio="none" class="block w-full h-7 overflow-visible">
                        <line x1="0" y1="0" x2="{{ $tapeSpan }}" y2="0" class="stroke-on-deep-muted" stroke-width="1" vector-effect="non-scaling-stroke" />
                        @for($cm = 0; $cm <= $tapeSpan; $cm += 5)
                            <line x1="{{ $cm }}" y1="0" x2="{{ $cm }}" y2="{{ $cm % 50 === 0 ? 28 : ($cm % 10 === 0 ? 16 : 9) }}"
                                  class="{{ $cm % 50 === 0 ? 'stroke-on-deep' : 'stroke-on-deep-muted' }}" stroke-width="1" vector-effect="non-scaling-stroke" />
                        @endfor
                    </svg>

                    {{-- The figures, each centred on its tick. --}}
                    <div class="flex justify-between mt-2 font-mono text-[0.625rem] text-on-deep-muted">
                        @for($cm = 0; $cm <= $tapeSpan; $cm += 50)
                            <span class="w-0 flex justify-center {{ $cm % 100 !== 0 ? 'max-sm:invisible' : '' }}">{{ $cm }}</span>
                        @endfor
                    </div>
                </div>
            </div>

            <p class="m-0 mt-6 text-xs text-on-deep-muted">{{ __('diwan.footer.ruler_note') }}</p>
        </div>

        <div class="relative border-t border-deep-rule bg-deep">
            <div class="{{ $page }} flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between py-6 text-xs text-on-deep-muted">
                <p class="m-0">&copy; {{ date('Y') }} {{ __('diwan.brand.name') }} · {{ __('diwan.brand.colophon') }}</p>

                <div class="flex items-center gap-6">
                    @foreach($alternate as $code => $meta)
                        <a href="{{ route('locale.switch', $code) }}" lang="{{ $code }}" hreflang="{{ $code }}"
                           class="inline-flex items-center min-h-11 text-on-deep-muted no-underline hover:text-on-deep"
                           aria-label="{{ __('diwan.nav.language') }}: {{ $meta['name'] }}">{{ $meta['native'] }}</a>
                    @endforeach

                    <a href="#top" class="group/f inline-flex items-center min-h-11 gap-2 text-on-deep-muted no-underline hover:text-on-deep">
                        {{ __('diwan.footer.back_to_top') }}
                        <span class="inline-flex items-center justify-center size-8 rounded-pill border border-deep-rule transition duration-300 group-hover/f:border-brass group-hover/f:-translate-y-0.5">
                            <x-icon name="chevron-down" class="size-3 rotate-180" />
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    {{-- ══ My room ═════════════════════════════════════════════════════════ --}}
    <x-room-dialog :room="$room" />

    {{-- ══ Menu, on a phone — a drawer from the inline end ═════════════════ --}}
    <dialog id="mobileMenu" aria-labelledby="mobileMenuLabel"
            class="m-0 ms-auto h-dvh max-h-none w-[min(22rem,88vw)] max-w-none p-0 border-0 bg-paper text-ink">
        <div class="flex flex-col min-h-full p-6">
            <div class="flex items-center justify-between mb-6">
                <span id="mobileMenuLabel" class="text-xs uppercase tracking-label text-muted">{{ __('diwan.nav.menu') }}</span>
                <button type="button" data-close-dialog class="{{ $util }} -me-2" aria-label="{{ __('diwan.nav.close') }}">
                    <x-icon name="x-lg" class="size-4" />
                </button>
            </div>

            <nav aria-label="{{ __('diwan.nav.mobile') }}">
                <ul class="m-0 p-0 list-none">
                    <li><a class="{{ $drawerLink }}" href="{{ route('furnish.index') }}">{{ __('diwan.nav.home') }}</a></li>
                    @foreach($navLinks as [$name, $label])
                        <li><a class="{{ $drawerLink }}" href="{{ route($name) }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="mt-auto pt-8 flex flex-col gap-3">
                {{-- On a phone the room control is here as well as in the
                     banner: the fit system is not a desktop feature. --}}
                <x-ui.button variant="ghost" :block="true" data-open-dialog="roomModal">
                    <x-icon name="rulers" class="size-4" />
                    @if($room)
                        <span class="font-mono" dir="ltr">{{ $room['width'] }}&times;{{ $room['length'] }} {{ __('diwan.room.unit') }}</span>
                    @else
                        {{ __('diwan.room.set_prompt') }}
                    @endif
                </x-ui.button>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-sm text-accent-ink">{{ __('diwan.account.dashboard') }}</a>
                    @endif
                    <a href="{{ route('orders.index') }}" class="text-sm text-accent-ink">{{ __('diwan.orders.title') }}</a>
                    <a href="{{ route('profile.edit') }}" class="text-sm text-accent-ink">{{ __('diwan.account.profile') }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button type="submit" variant="ghost" :block="true">{{ __('diwan.account.sign_out') }}</x-ui.button>
                    </form>
                @else
                    <x-ui.button :href="route('login')" :block="true">{{ __('diwan.account.sign_in') }}</x-ui.button>
                @endauth
            </div>
        </div>
    </dialog>

    {{-- ══ Search ══════════════════════════════════════════════════════════ --}}
    <dialog id="searchModal" aria-labelledby="searchModalLabel"
            class="mx-auto mt-[10vh] mb-auto w-[min(44rem,calc(100vw-2rem))] max-w-none p-0 border-0 bg-paper text-ink">
        <div class="p-6 sm:p-10">
            <div class="flex items-center justify-between mb-6">
                <span id="searchModalLabel" class="text-xs uppercase tracking-label text-muted">{{ __('diwan.util.search') }}</span>
                <button type="button" data-close-dialog class="{{ $util }} -me-2" aria-label="{{ __('diwan.nav.close') }}">
                    <x-icon name="x-lg" class="size-4" />
                </button>
            </div>

            <div class="flex items-center gap-4 border-b border-ink">
                <x-icon name="search" class="size-5 text-muted" />
                <label for="searchInput" class="sr-only">{{ __('diwan.search.label') }}</label>
                <input type="search" id="searchInput" autocomplete="off" autofocus
                       placeholder="{{ __('diwan.search.placeholder') }}"
                       class="flex-1 min-w-0 h-14 p-0 border-0 bg-transparent font-display text-2xl text-ink outline-none">
                <span id="searchSpinner" hidden class="text-xs uppercase tracking-label text-muted [&[hidden]]:hidden">{{ __('diwan.search.searching') }}</span>
            </div>

            <div class="mt-6" id="searchResults"></div>
        </div>
    </dialog>

    @include('partials.app-data')

    @stack('scripts')
</body>

</html>
