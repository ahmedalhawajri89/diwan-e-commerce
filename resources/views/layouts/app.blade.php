{{--
  The admin panel: a side rail of real counts on the inline-start edge, the
  work on the rest of the page. Same bundle and tokens as the shop, so the two
  cannot drift apart.

  Destructive actions are confirmed in the native <dialog> at the foot of this
  file (see initDeleteConfirm in resources/js/furnish/admin.js). Nothing here
  loads from a third-party origin.
--}}
@php
    $locales   = config('localization.supported');
    $current   = $locales[app()->getLocale()] ?? $locales[config('app.fallback_locale')];
    $alternate = collect($locales)->except(app()->getLocale())->take(1);

    // $counts arrives from AdminNavComposer, bound to this layout in
    // AppServiceProvider. They are the panel's only metric, and they are real.
    $groups = [
        'catalogue' => [
            ['route' => 'admin.dashboard',         'icon' => 'grid',     'label' => 'overview',   'count' => null],
            ['route' => 'admin.products.index',    'icon' => 'box-seam', 'label' => 'products',   'count' => $counts['products']],
            ['route' => 'admin.categories.index',  'icon' => 'tags',     'label' => 'categories', 'count' => $counts['categories']],
        ],
        'sales' => [
            ['route' => 'admin.orders.index',      'icon' => 'receipt',  'label' => 'orders',     'count' => $counts['orders']],
            ['route' => 'admin.reviews.index',     'icon' => 'star',     'label' => 'reviews',    'count' => $counts['reviews']],
        ],
        'people' => [
            ['route' => 'admin.users.index',       'icon' => 'person',   'label' => 'users',      'count' => $counts['users']],
            ['route' => 'admin.contacts.index',    'icon' => 'envelope', 'label' => 'messages',   'count' => $counts['messages']],
            ['route' => 'admin.newsletters.index', 'icon' => 'send',     'label' => 'newsletter', 'count' => $counts['newsletter']],
        ],
    ];

    $railLink = 'flex items-center gap-3 px-3 py-2 rounded-control text-sm text-on-deep-muted no-underline '
              . 'transition-colors hover:bg-deep-2 hover:text-on-deep aria-[current=page]:bg-deep-2 aria-[current=page]:text-on-deep';
    $footLink = 'block py-1 text-sm text-on-deep-muted no-underline hover:text-on-deep';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? __('admin.panel') }} — {{ __('diwan.brand.name') }}</title>

    {{-- Typefaces are bundled; see resources/css/fonts.css. --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-paper text-ink font-body">
    @include('partials.icon-sprite')

    <a href="#content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:start-3 focus:z-50 focus:px-4 focus:py-2 focus:bg-ink focus:text-paper">
        {{ __('diwan.common.skip') }}
    </a>

    <div class="min-h-svh lg:grid lg:grid-cols-[16rem_1fr]">

        {{-- ══ Side rail ═══════════════════════════════════════════════════ --}}
        <nav x-data="disclosure" aria-label="{{ __('admin.panel') }}"
             class="bg-deep text-on-deep lg:sticky lg:top-0 lg:h-svh lg:overflow-y-auto">
            <div class="flex items-center justify-between gap-4 px-5 h-16 lg:h-20">
                <a href="{{ route('admin.dashboard') }}" class="flex items-baseline gap-2 text-on-deep no-underline">
                    <span class="font-display font-semibold text-xl tracking-[0.2em] rtl:tracking-normal">{{ __('diwan.brand.name') }}</span>
                    <span class="font-label text-[0.6875rem] uppercase tracking-label text-brass">{{ __('admin.panel') }}</span>
                </a>

                <button type="button" @click="toggle" :aria-expanded="open" aria-expanded="false" aria-controls="railPanel"
                        class="lg:hidden inline-flex items-center gap-2 h-9 px-3 border border-deep-rule rounded-control bg-transparent text-sm text-on-deep cursor-pointer">
                    <x-icon name="list" class="size-4" />
                    {{ __('admin.menu') }}
                </button>
            </div>

            <div id="railPanel" x-show="open" x-cloak class="lg:block! px-3 pb-6">
                @foreach($groups as $group => $links)
                    <div class="mt-5">
                        <h2 class="m-0 mb-2 px-3 font-label text-[0.6875rem] font-medium uppercase tracking-label text-on-deep-muted">{{ __('admin.group.'.$group) }}</h2>
                        <ul class="m-0 p-0 list-none grid gap-0.5">
                            @foreach($links as $link)
                                <li>
                                    <a href="{{ route($link['route']) }}" class="{{ $railLink }}"
                                       @if(request()->routeIs(str_replace('.index', '.*', $link['route']))) aria-current="page" @endif>
                                        <x-icon :name="$link['icon']" class="size-4" />
                                        <span class="flex-1">{{ __('admin.nav.'.$link['label']) }}</span>
                                        @if($link['count'] !== null)
                                            <span class="font-mono text-xs text-on-deep-muted">{{ $link['count'] }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                <div class="mt-8 mx-3 pt-5 border-0 border-t border-deep-rule">
                    <p class="m-0 font-label text-[0.6875rem] uppercase tracking-label text-on-deep-muted">{{ __('admin.signed_in') }}</p>
                    <p class="m-0 mt-1 mb-4 text-sm">{{ auth()->user()?->name }}</p>

                    <a href="{{ route('furnish.index') }}" class="{{ $footLink }} inline-flex items-center gap-2">
                        <x-icon name="arrow-left" class="size-3.5" />{{ __('admin.back') }}
                    </a>
                    <a href="{{ route('profile.edit') }}" class="{{ $footLink }}">{{ __('admin.profile') }}</a>
                    @foreach($alternate as $code => $meta)
                        <a href="{{ route('locale.switch', $code) }}" class="{{ $footLink }}" lang="{{ $code }}" hreflang="{{ $code }}">{{ $meta['native'] }}</a>
                    @endforeach

                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf
                        <button type="submit"
                                class="w-full h-10 border border-deep-rule rounded-control bg-transparent text-sm text-on-deep cursor-pointer hover:border-on-deep-muted">{{ __('admin.sign_out') }}</button>
                    </form>
                </div>
            </div>
        </nav>

        {{-- ══ Content ═════════════════════════════════════════════════════ --}}
        <main id="content" class="min-w-0 px-5 py-8 sm:px-8 lg:px-12 lg:py-12">
            <div class="max-w-6xl grid gap-3 empty:hidden mb-8">
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

            <div class="max-w-6xl">
                {{ $slot }}
            </div>
        </main>
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
