{{--
  The admin panel: a side rail of real counts on the inline-start edge that
  folds to icons, a top bar for the person and the ways out, and the work on
  the rest of the page. Same bundle and tokens as the shop, so the two
  cannot drift apart.

  Destructive actions are confirmed in the native <dialog> at the foot of this
  file (see initDeleteConfirm in resources/js/furnish/admin.js). Nothing here
  loads from a third-party origin.
--}}
@php
    $locales   = config('localization.supported');
    $current   = $locales[app()->getLocale()] ?? $locales[config('app.fallback_locale')];
    $alternate = collect($locales)->except(app()->getLocale())->take(1);

    // $counts, $who, $initials and $railFolded arrive from AdminNavComposer,
    // bound to this layout in AppServiceProvider. The counts are the rail's
    // only metric, and they are real. `attention` marks the rows whose count
    // is work waiting: folded, they show as a dot on the icon.
    $groups = [
        'catalogue' => [
            ['route' => 'admin.dashboard',         'icon' => 'grid',     'label' => 'overview',   'count' => null],
            ['route' => 'admin.products.index',    'icon' => 'box-seam', 'label' => 'products',   'count' => $counts['products']],
            ['route' => 'admin.categories.index',  'icon' => 'tags',     'label' => 'categories', 'count' => $counts['categories']],
        ],
        'sales' => [
            ['route' => 'admin.orders.index',      'icon' => 'receipt',  'label' => 'orders',     'count' => $counts['orders'],  'attention' => true],
            ['route' => 'admin.reviews.index',     'icon' => 'star',     'label' => 'reviews',    'count' => $counts['reviews'], 'attention' => true],
        ],
        'people' => [
            ['route' => 'admin.users.index',       'icon' => 'person',   'label' => 'users',      'count' => $counts['users']],
            ['route' => 'admin.contacts.index',    'icon' => 'envelope', 'label' => 'messages',   'count' => $counts['messages']],
            ['route' => 'admin.newsletters.index', 'icon' => 'send',     'label' => 'newsletter', 'count' => $counts['newsletter']],
            ['route' => 'admin.activity.index',    'icon' => 'clock-history', 'label' => 'activity', 'count' => null],
        ],
    ];

    // Folded, a label fades out where it stands; the icon stays.
    $fade = 'whitespace-nowrap transition-opacity duration-200 lg:group-data-folded/shell:opacity-0 lg:group-data-folded/shell:pointer-events-none';

    $railLink = 'group/link relative flex items-center gap-3 h-10 px-[0.9375rem] lg:group-data-folded/shell:w-12 rounded-control text-sm text-on-deep-muted no-underline '
              . 'transition-colors hover:bg-deep-2 hover:text-on-deep aria-[current=page]:bg-deep-2 aria-[current=page]:text-on-deep '
              . 'focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-brass';

    // The name of a folded icon, beside it on hover or focus.
    $tip = 'hidden lg:block absolute start-full top-1/2 ms-3 px-2.5 py-1.5 rounded-control bg-ink text-paper text-xs whitespace-nowrap pointer-events-none z-50 '
         . 'opacity-0 -translate-y-1/2 -translate-x-1 rtl:translate-x-1 transition-[opacity,translate] duration-200 ease-out '
         . 'lg:group-data-folded/shell:group-hover/link:opacity-100 lg:group-data-folded/shell:group-hover/link:translate-x-0 '
         . 'lg:group-data-folded/shell:group-focus-visible/link:opacity-100 lg:group-data-folded/shell:group-focus-visible/link:translate-x-0';

    $bar = 'inline-flex items-center justify-center gap-2 h-10 min-w-10 px-2.5 rounded-control border-0 bg-transparent text-sm text-neutral no-underline cursor-pointer '
         . 'transition-colors hover:bg-paper-2 hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus';
    $menuItem = 'flex items-center gap-3 w-full min-h-10 px-3 rounded-control border-0 bg-transparent text-sm text-ink text-start no-underline cursor-pointer '
              . 'transition-colors hover:bg-paper-2 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    @include('partials.favicon')

    <title>{{ $title ?? __('admin.panel') }} — {{ __('diwan.brand.name') }}</title>

    {{-- Typefaces are bundled; see resources/css/fonts.css. --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.speculation')
</head>

<body class="bg-paper text-ink font-body">
    @include('partials.icon-sprite')

    <a href="#content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:start-3 focus:z-[60] focus:px-4 focus:py-2 focus:bg-ink focus:text-paper">
        {{ __('diwan.common.skip') }}
    </a>

    <div x-data="adminShell" @keydown.escape="closeDrawer"
         @if($railFolded) data-folded @endif :data-folded="folded" :data-drawer="drawer"
         data-fold-label="{{ __('admin.fold') }}" data-unfold-label="{{ __('admin.unfold') }}"
         class="group/shell min-h-svh lg:flex">

        {{-- Behind the drawer on a phone; a tap closes it. --}}
        <div @click="closeDrawer" aria-hidden="true"
             class="lg:hidden fixed inset-0 z-40 bg-scrim opacity-0 pointer-events-none transition-opacity duration-300 group-data-drawer/shell:opacity-100 group-data-drawer/shell:pointer-events-auto"></div>

        {{-- ══ Side rail ═══════════════════════════════════════════════════
             Desktop: sticky beside the page, 16rem, or 4.5rem of icons when
             folded (the one width design.md lets animate). Phone: a drawer
             from the inline-start edge, moved by transform alone. --}}
        <nav id="rail" aria-label="{{ __('admin.panel') }}"
             class="flex flex-col bg-deep text-on-deep overflow-x-hidden overflow-y-auto
                    max-lg:fixed max-lg:inset-y-0 max-lg:start-0 max-lg:z-50 max-lg:w-72 max-lg:-translate-x-full max-lg:rtl:translate-x-full
                    max-lg:transition-transform max-lg:duration-300 max-lg:ease-out max-lg:group-data-drawer/shell:translate-x-0
                    lg:sticky lg:top-0 lg:h-svh lg:w-64 lg:shrink-0 lg:transition-[width] lg:duration-300 lg:ease-out
                    lg:group-data-folded/shell:w-[4.5rem] lg:group-data-folded/shell:overflow-visible">
            <div class="flex items-center gap-3 h-16 px-[0.9375rem] shrink-0 border-0 border-b border-deep-rule">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 min-w-0 text-on-deep no-underline" aria-label="{{ __('diwan.brand.name') }} — {{ __('admin.panel') }}">
                    {{-- The monogram stays when the rail folds. --}}
                    <span class="inline-flex items-center justify-center size-9 shrink-0 -ms-[0.5625rem] rounded-control bg-brass text-deep font-display font-semibold text-lg leading-none" aria-hidden="true">{{ mb_substr(__('diwan.brand.name'), 0, 1) }}</span>
                    <span class="{{ $fade }} flex items-baseline gap-2" aria-hidden="true">
                        <span class="font-display font-semibold text-xl tracking-[0.2em] rtl:tracking-normal">{{ __('diwan.brand.name') }}</span>
                        <span class="font-label text-[0.6875rem] uppercase tracking-label text-brass">{{ __('admin.panel') }}</span>
                    </span>
                </a>

                <button type="button" @click="closeDrawer" aria-label="{{ __('admin.close_menu') }}"
                        class="lg:hidden ms-auto inline-flex items-center justify-center size-10 rounded-control border-0 bg-transparent text-on-deep cursor-pointer hover:bg-deep-2">
                    <x-icon name="x-lg" class="size-4" />
                </button>
            </div>

            <div id="railPanel" class="flex flex-col grow px-3 pb-6">
                @foreach($groups as $group => $links)
                    <div class="mt-6">
                        <h2 class="relative m-0 mb-2 px-[0.9375rem] h-4 font-label text-[0.6875rem] font-medium uppercase tracking-label text-on-deep-muted">
                            <span class="{{ $fade }} block">{{ __('admin.group.'.$group) }}</span>
                            {{-- Folded, the group's name becomes a short rule. --}}
                            <span class="absolute inset-x-[1.375rem] top-1/2 h-px bg-deep-rule opacity-0 transition-opacity duration-200 lg:group-data-folded/shell:opacity-100" aria-hidden="true"></span>
                        </h2>
                        <ul class="m-0 p-0 list-none grid gap-0.5">
                            @foreach($links as $link)
                                @php $waiting = ($link['attention'] ?? false) && $link['count'] > 0; @endphp
                                <li>
                                    <a href="{{ route($link['route']) }}" class="{{ $railLink }}"
                                       @if(request()->routeIs(str_replace('.index', '.*', $link['route']))) aria-current="page" @endif>
                                        <span class="relative shrink-0">
                                            <x-icon :name="$link['icon']" class="size-[1.125rem]" />
                                            @if($waiting)
                                                <span class="absolute -top-1 -end-1 size-2 rounded-pill bg-brass ring-2 ring-deep opacity-0 scale-50 transition-[opacity,scale] duration-200 lg:group-data-folded/shell:opacity-100 lg:group-data-folded/shell:scale-100" aria-hidden="true"></span>
                                            @endif
                                        </span>
                                        <span class="{{ $fade }} flex-1 min-w-0">{{ __('admin.nav.'.$link['label']) }}</span>
                                        @if($link['count'] !== null)
                                            <span class="{{ $fade }} font-mono text-xs {{ $waiting ? 'px-1.5 rounded-pill bg-brass text-deep' : 'text-on-deep-muted' }}" @if($link['label'] === 'orders') data-rail-count="orders" @endif>{{ $link['count'] }}</span>
                                        @endif
                                        <span class="{{ $tip }}" aria-hidden="true">{{ __('admin.nav.'.$link['label']) }}@if($link['count'] !== null) <span class="font-mono opacity-70">· {{ $link['count'] }}</span>@endif</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </nav>

        <div class="flex flex-col flex-1 min-w-0">
            {{-- ══ Top bar ══════════════════════════════════════════════════
                 The fold, the page's name, and everything about the person
                 and the panel's ways out. --}}
            <header class="sticky top-0 z-30 flex items-center gap-2 h-16 px-3 sm:px-5 lg:px-8 bg-paper border-0 border-b border-rule">
                <button type="button" @click="openDrawer" data-drawer-button :aria-expanded="drawer" aria-expanded="false" aria-controls="rail"
                        class="{{ $bar }} lg:hidden" aria-label="{{ __('admin.menu') }}">
                    <x-icon name="list" class="size-5" />
                </button>
                <button type="button" @click="fold" :aria-pressed="folded" aria-pressed="{{ $railFolded ? 'true' : 'false' }}" :aria-label="foldLabel"
                        aria-label="{{ $railFolded ? __('admin.unfold') : __('admin.fold') }}" aria-controls="rail" class="{{ $bar }} max-lg:hidden">
                    <x-icon name="layout-sidebar" class="size-[1.125rem] rtl:-scale-x-100" />
                </button>

                <span class="hidden sm:block w-px h-6 mx-1 bg-rule" aria-hidden="true"></span>
                <p class="m-0 min-w-0 truncate text-sm">
                    <span class="text-muted">{{ __('admin.panel') }}</span>
                    <span class="mx-1.5 text-rule-2" aria-hidden="true">/</span>
                    <span class="font-medium" dir="auto">{{ $title ?? __('admin.panel') }}</span>
                </p>

                <div class="flex items-center gap-1 ms-auto">
                    <a href="{{ route('furnish.index') }}" class="{{ $bar }}" target="_blank" rel="noopener">
                        <x-icon name="box-arrow-up-right" class="size-4 rtl:-scale-x-100" />
                        <span class="hidden md:inline">{{ __('admin.view_shop') }}</span>
                    </a>
                    @foreach($alternate as $code => $meta)
                        <a href="{{ route('locale.switch', $code) }}" class="{{ $bar }}" lang="{{ $code }}" hreflang="{{ $code }}">
                            <x-icon name="translate" class="size-4" />
                            <span class="hidden md:inline">{{ $meta['native'] }}</span>
                        </a>
                    @endforeach

                    {{-- The bell: the unread count, the latest notices, and word of a
                         new one as it arrives (notifications.js polls the feed). --}}
                    <div x-data="notifications" @click.outside="close" @keydown.escape="dismiss" class="relative"
                         data-feed="{{ route('admin.notifications.feed') }}"
                         data-read="{{ route('admin.notifications.read', ['notification' => '__ID__']) }}"
                         data-read-all="{{ route('admin.notifications.read-all') }}"
                         data-unread="{{ $unreadAlerts }}"
                         data-label="{{ __('admin.alerts_page.bell') }}"
                         data-label-unread="{{ __('admin.alerts_page.bell_unread') }}"
                         data-dismiss="{{ __('admin.alerts_page.dismiss') }}">
                        <button type="button" x-ref="button" @click="toggle" :aria-expanded="open" aria-expanded="false" aria-controls="alertsPanel"
                                aria-label="{{ $unreadAlerts ? __('admin.alerts_page.bell_unread', ['count' => $unreadAlerts]) : __('admin.alerts_page.bell') }}"
                                class="{{ $bar }} relative">
                            <x-icon name="bell" class="size-[1.125rem]" />
                            <span x-ref="badge" @if(! $unreadAlerts) hidden @endif
                                  class="absolute top-1 end-1 inline-flex items-center justify-center min-w-[1.125rem] h-[1.125rem] px-1 rounded-pill bg-danger text-paper font-mono text-[0.625rem] leading-none ring-2 ring-paper">{{ $unreadAlerts > 99 ? '99+' : $unreadAlerts }}</span>
                        </button>

                        <div id="alertsPanel" x-show="open" x-cloak
                             x-transition:enter="transition-[opacity,translate] duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition-opacity duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="absolute top-full end-0 z-40 mt-2 w-[min(24rem,calc(100vw-1.5rem))] bg-paper border border-rule">
                            <div class="flex items-center justify-between gap-3 px-4 py-3 border-0 border-b border-rule">
                                <p class="m-0 font-display text-lg">{{ __('admin.alerts_page.title') }}</p>
                                <button type="button" x-ref="markAll" @click="readAll" @if(! $unreadAlerts) hidden @endif
                                        class="inline-flex items-center gap-1.5 min-h-9 px-2 border-0 bg-transparent text-xs text-accent-ink cursor-pointer hover:underline underline-offset-4">
                                    <x-icon name="check-lg" class="size-3.5" />{{ __('admin.alerts_page.mark_all') }}
                                </button>
                            </div>
                            <ul x-ref="list" class="m-0 p-0 list-none max-h-[min(26rem,60vh)] overflow-y-auto divide-y divide-rule"></ul>
                            <p x-ref="empty" class="m-0 px-4 py-8 text-center text-sm leading-body text-muted">{{ __('admin.alerts_page.empty') }}</p>
                            <a href="{{ route('admin.notifications.index') }}"
                               class="flex items-center justify-center gap-2 min-h-11 border-0 border-t border-rule text-sm text-ink no-underline hover:bg-paper-2">
                                {{ __('admin.alerts_page.see_all') }}<x-icon name="arrow-right" class="size-3.5" />
                            </a>
                        </div>

                        {{-- Word of a new notice, at the inline end below the bar. --}}
                        <div x-ref="toasts" x-show="closed" aria-live="polite" class="pointer-events-none fixed top-20 end-4 z-50 grid gap-2"></div>
                    </div>

                    <span class="w-px h-6 mx-1 bg-rule" aria-hidden="true"></span>

                    {{-- The account: initials, name and role; the menu drops below. --}}
                    <div x-data="dropdown" @click.outside="close" @keydown.escape="dismiss" :data-open="open" class="group/account relative">
                        <button type="button" x-ref="button" @click="toggle" :aria-expanded="open" aria-expanded="false" aria-controls="accountMenu"
                                aria-label="{{ __('admin.account_menu', ['name' => $who?->name]) }}"
                                class="flex items-center gap-2.5 h-11 ps-1 pe-2 sm:pe-3 rounded-pill border border-rule bg-paper cursor-pointer transition-colors hover:border-ink group-data-open/account:border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                            <span class="inline-flex items-center justify-center size-9 shrink-0 rounded-pill bg-deep text-brass font-label text-xs font-semibold" aria-hidden="true">{{ $initials }}</span>
                            <span class="hidden sm:block text-start leading-tight" aria-hidden="true">
                                <span class="block text-sm text-ink max-w-40 truncate">{{ $who?->name }}</span>
                                <span class="block text-[0.6875rem] text-muted">{{ __('admin.role') }}</span>
                            </span>
                            <x-icon name="chevron-down" class="size-3 text-muted transition-transform duration-200 group-data-open/account:rotate-180" />
                        </button>

                        <div id="accountMenu" x-show="open" x-cloak
                             x-transition:enter="transition-[opacity,translate] duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition-opacity duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="absolute top-full end-0 z-40 mt-2 w-72 p-2 bg-paper border border-rule">
                            <div class="flex items-center gap-3 p-3 mb-2 bg-paper-2">
                                <span class="inline-flex items-center justify-center size-10 shrink-0 rounded-pill bg-deep text-brass font-label text-sm font-semibold" aria-hidden="true">{{ $initials }}</span>
                                <span class="min-w-0">
                                    <span class="block text-xs text-muted">{{ __('admin.signed_in') }}</span>
                                    <span class="block text-sm font-medium truncate">{{ $who?->name }}</span>
                                    <span class="block font-mono text-[0.6875rem] text-muted truncate" dir="ltr">{{ $who?->email }}</span>
                                </span>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="{{ $menuItem }}"><x-icon name="person-gear" class="size-4 text-muted" />{{ __('admin.profile') }}</a>
                            <a href="{{ route('furnish.index') }}" class="{{ $menuItem }}"><x-icon name="shop" class="size-4 text-muted" />{{ __('admin.shop') }}</a>
                            @foreach($alternate as $code => $meta)
                                <a href="{{ route('locale.switch', $code) }}" class="{{ $menuItem }}" lang="{{ $code }}" hreflang="{{ $code }}"><x-icon name="translate" class="size-4 text-muted" />{{ $meta['native'] }}</a>
                            @endforeach
                            <div class="h-px my-2 bg-rule" aria-hidden="true"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="{{ $menuItem }} text-danger hover:bg-danger-wash">
                                    <x-icon name="box-arrow-right" class="size-4 rtl:-scale-x-100" />{{ __('admin.sign_out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

        {{-- ══ Content ═════════════════════════════════════════════════════ --}}
        <main id="content" class="min-w-0 grow px-5 sm:px-8 {{ ($canvas ?? 'paper') === 'linen' ? 'py-6 lg:px-8 bg-paper-2' : 'py-8 lg:px-12 lg:py-10' }}">
            <div class="max-w-[96rem] mx-auto grid gap-3 empty:hidden mb-8">
                @if(session('success') || session('status'))
                    <x-ui.note tone="ok">{{ session('success') ?? session('status') }}</x-ui.note>
                @endif

                @if(session('error'))
                    <x-ui.note tone="danger">{{ session('error') }}</x-ui.note>
                @endif

                @if(isset($errors) && $errors->any())
                    <x-ui.note tone="danger">
                        @foreach($errors->all() as $error)
                            {{ $error }}@if(! $loop->last)<br>@endif
                        @endforeach
                    </x-ui.note>
                @endif
            </div>

            <div class="max-w-[96rem] mx-auto">
                {{ $slot }}
            </div>
        </main>
        </div>
    </div>

    {{-- ══ Confirming an irreversible action ═══════════════════════════════
         Filled and opened by initDeleteConfirm (admin.js) when a
         form.delete-form is submitted. Without JavaScript the form simply
         submits; the server still authorises it. --}}
    <dialog id="confirmDialog" aria-labelledby="confirmTitle" aria-describedby="confirmMessage"
            class="m-auto w-[min(28rem,calc(100vw-2rem))] max-w-none p-0 border-0 bg-paper text-ink">
        <div class="p-8">
            <span class="inline-flex items-center justify-center size-11 rounded-pill bg-danger-wash text-danger">
                <x-icon name="exclamation-triangle" class="size-5" />
            </span>
            <h2 id="confirmTitle" class="m-0 mt-5 font-display text-2xl">{{ __('admin.action.confirm') }}</h2>
            <p id="confirmMessage" class="m-0 mt-2 text-sm leading-body text-muted">{{ __('admin.action.confirm_delete') }}</p>

            <div class="flex flex-wrap justify-end gap-3 mt-8">
                <x-ui.button variant="ghost" data-close-dialog autofocus>{{ __('admin.action.cancel') }}</x-ui.button>
                <button type="button" id="confirmAccept"
                        class="inline-flex items-center justify-center gap-2 min-h-12 px-7 border-0 rounded-control bg-danger text-paper font-label text-xs font-medium uppercase tracking-label cursor-pointer hover:opacity-90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus">
                    <x-icon name="trash" class="size-4" />{{ __('admin.action.yes_delete') }}
                </button>
            </div>
        </div>
    </dialog>

    @stack('scripts')
</body>

</html>
