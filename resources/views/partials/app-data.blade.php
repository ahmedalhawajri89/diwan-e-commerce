{{-- Everything the front-end scripts need from the server, in one place:
     the server writes JSON, the modules read it, and neither knows about
     the other. A data block rather than `window.X = {{ ... }}`: JSON in a
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
            'currency' => __('diwan.common.currency'),
            'noPrice' => __('diwan.product.no_price'),
            'addFailed' => __('diwan.bag.add_failed'),
            'sessionExpired' => __('diwan.common.session_expired'),
            'loadFailed' => __('diwan.wishlist.load_failed'),
            'retry' => __('diwan.common.retry'),
            'savedCount' => __('diwan.util.saved_count', ['count' => '__COUNT__']),
            'savedNone' => __('diwan.util.saved_items'),
            'bagCount' => __('diwan.util.bag_count', ['count' => '__COUNT__']),
            'bagEmpty' => __('diwan.util.bag_empty'),
            'searchCount' => __('diwan.search.count', ['count' => '__COUNT__', 'query' => '__QUERY__']),
        ],
    ];
@endphp

<script id="diwan-data" type="application/json" nonce="{{ $cspNonce ?? '' }}">{!! json_encode($bootstrapData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
