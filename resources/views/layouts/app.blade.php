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

    // Live counts beside each row. They are the panel's only "metric", and they
    // are real — design.md forbids the invented kind.
    $counts = [
        'products'   => \App\Models\Product::count(),
        'categories' => \App\Models\Category::count(),
        'orders'     => \App\Models\Order::where('status', 'pending')->count(),
        'reviews'    => \App\Models\Review::where('status', 'pending')->count(),
        'users'      => \App\Models\User::count(),
        'messages'   => \App\Models\Contact::count(),
        'newsletter' => \App\Models\NewsletterSubscriber::count(),
    ];
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $current['dir'] ?? 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? __('admin.panel') }} — {{ __('diwan.brand.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(($current['font'] ?? 'latin') === 'arabic')
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600&family=IBM+Plex+Sans+Arabic:wght@400;500;600&family=Geist+Mono:wght@400;500&display=swap">
    @else
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Geist:wght@400;500;600&family=Geist+Mono:wght@400;500&display=swap">
    @endif

    @vite(['tokens.css', 'resources/scss/furnish/style.scss', 'resources/js/furnish/main.js'])
</head>

<body>
    <a href="#content" class="hm-skip">{{ __('diwan.common.skip') }}</a>

    <div class="hm-app">
        {{-- ══ N3 · Side-rail ══════════════════════════════════════════ --}}
        <nav class="hm-rail-nav" aria-label="{{ __('admin.panel') }}">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="hm-rail-nav__brand">
                    {{ __('diwan.brand.name') }}
                    <span class="hm-rail-nav__tag">{{ __('admin.panel') }}</span>
                </a>

                <button type="button" class="hm-rail-nav__toggle" id="railToggle"
                        aria-expanded="false" aria-controls="railPanel" style="margin-top: var(--space-sm);">
                    <i class="bi bi-list" aria-hidden="true"></i>
                    {{ __('admin.menu') }}
                </button>
            </div>

            <div id="railPanel" class="hm-rail-nav__panel d-flex flex-column"
                 style="gap: var(--space-lg); flex: 1 1 auto;">

                @php
                    $groups = [
                        'catalogue' => [
                            ['route' => 'admin.dashboard',        'icon' => 'bi-grid',        'label' => 'overview',   'count' => null],
                            ['route' => 'admin.products.index',   'icon' => 'bi-box-seam',    'label' => 'products',   'count' => $counts['products']],
                            ['route' => 'admin.categories.index', 'icon' => 'bi-tags',        'label' => 'categories', 'count' => $counts['categories']],
                        ],
                        'sales' => [
                            ['route' => 'admin.orders.index',     'icon' => 'bi-receipt',     'label' => 'orders',     'count' => $counts['orders']],
                            ['route' => 'admin.reviews.index',    'icon' => 'bi-star',        'label' => 'reviews',    'count' => $counts['reviews']],
                        ],
                        'people' => [
                            ['route' => 'admin.users.index',      'icon' => 'bi-person',      'label' => 'users',      'count' => $counts['users']],
                            ['route' => 'admin.contacts.index',   'icon' => 'bi-envelope',    'label' => 'messages',   'count' => $counts['messages']],
                            ['route' => 'admin.newsletters.index','icon' => 'bi-send',        'label' => 'newsletter', 'count' => $counts['newsletter']],
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
                                        <i class="bi {{ $link['icon'] }}" aria-hidden="true"></i>
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
                    <p class="hm-label" style="margin-bottom: var(--space-3xs);">{{ __('admin.signed_in') }}</p>
                    <p style="font-size: var(--text-sm); margin-bottom: var(--space-sm);">{{ auth()->user()?->name }}</p>

                    <a href="{{ route('furnish.index') }}" class="hm-link hm-link--sm d-block mb-2">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
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
                <p class="hm-note hm-note--ok" role="status" style="margin-bottom: var(--space-lg);">
                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                    <span>{{ session('success') ?? session('status') }}</span>
                </p>
            @endif

            @if(session('error'))
                <p class="hm-note hm-note--danger" role="alert" style="margin-bottom: var(--space-lg);">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <span>{{ session('error') }}</span>
                </p>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="hm-note hm-note--danger" role="alert" style="margin-bottom: var(--space-lg);">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // The rail collapses to a disclosure below 60rem. The button only
            // exists at that width, so bail out cleanly when it is not there.
            const toggle = document.getElementById('railToggle');
            const panel = document.getElementById('railPanel');

            if (toggle && panel) {
                const mq = window.matchMedia('(max-width: 60rem)');
                const sync = () => {
                    const collapsed = mq.matches;
                    panel.hidden = collapsed;
                    toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                };
                sync();
                mq.addEventListener('change', sync);

                toggle.addEventListener('click', () => {
                    const open = toggle.getAttribute('aria-expanded') === 'true';
                    toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
                    panel.hidden = open;
                });
            }

            // design.md reserves the modal for genuinely irreversible actions.
            // Deleting a product or a user is one, so it keeps the confirm.
            document.querySelectorAll('.delete-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: this.dataset.confirmTitle || @json(__('admin.action.confirm')),
                        text: this.dataset.confirmMessage || @json(__('admin.action.confirm_delete')),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: @json(__('admin.action.yes_delete')),
                        cancelButtonText: @json(__('admin.action.cancel')),
                        confirmButtonColor: getComputedStyle(document.documentElement)
                            .getPropertyValue('--color-danger').trim(),
                        cancelButtonColor: getComputedStyle(document.documentElement)
                            .getPropertyValue('--color-neutral').trim(),
                    }).then(result => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
