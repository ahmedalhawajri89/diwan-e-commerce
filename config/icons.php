<?php

/**
 * The icon set.
 *
 * Every glyph the application draws, declared rather than discovered. The
 * sprite in resources/views/partials/icon-sprite.blade.php is built from this
 * list by `php artisan icons:build`, and a guard in DesignSystemTest checks
 * the two agree.
 *
 * Declared, because a scan cannot find them all. Three sources name an icon at
 * runtime and would be invisible to any regex over the views:
 *
 *   * App\Support\Fit::icon() maps a fit verdict to a glyph.
 *   * The admin side-rail's link map in layouts/app.blade.php.
 *   * window.diwanIcon() in the wishlist script, for buttons built after load.
 *
 * A scanner that missed one of those would ship a blank square in production
 * and pass every test. The list is short; keeping it by hand is cheaper than
 * pretending it can be inferred.
 *
 * To add an icon: put its Bootstrap Icons name here, run `php artisan
 * icons:build`, and commit the regenerated sprite.
 */

return [

    /*
     | Navigation and chrome
     */
    'arrow-left',
    'arrow-right',
    'chevron-down',
    'chevron-left',    // the RTL mirror of chevron-right; see components/icon
    'chevron-right',
    'list',
    'search',
    'x',
    'x-lg',

    /*
     | Commerce
     */
    'bag',
    'bag-plus',
    'bookmark',
    'bookmark-fill',   // toggled at runtime by the wishlist script
    'cash-coin',
    'person',
    'receipt',

    /*
     | The fit system — see App\Support\Fit::icon()
     */
    'check-lg',
    'dash-lg',
    'exclamation-triangle',
    'rulers',
    'slash-circle',

    /*
     | Admin side-rail — see the link map in layouts/app.blade.php
     */
    'box-seam',
    'envelope',
    'eye',
    'grid',
    'pencil',
    'plus-lg',
    'send',
    'shield-lock',
    'star',
    'tags',
    'trash',

    /*
     | Admin overview — what is waiting, and which way a figure moved
     */
    'arrow-down-right',
    'arrow-up-right',
    'funnel',
    'telephone',
    'translate',

    /*
     | Admin rail — the account card's actions
     */
    'box-arrow-right',
    'box-arrow-up-right',
    'layout-sidebar',
    'person-gear',
    'shop',

    /*
     | Product gallery — enlarge a photograph
     */
    'arrows-angle-expand',

];
