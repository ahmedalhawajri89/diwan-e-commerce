<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported locales
    |--------------------------------------------------------------------------
    |
    | The single source of truth for which languages the storefront speaks.
    | SetLocale middleware validates against these keys, the language switcher
    | renders from them, and the layout reads `dir` and `font` from the match.
    |
    | `font` names the token suffix used in resources/css/app.css — a locale whose script
    | Playfair Display cannot set needs its own display/body pairing.
    |
    */

    'supported' => [
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'dir' => 'ltr',
            'font' => 'latin',
        ],
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'dir' => 'rtl',
            'font' => 'arabic',
        ],
    ],

    /*
    | The shop's own language: where a visitor with no choice of their own
    | lands, and what `x-default` names. Fixed, unlike app.locale, which each
    | request sets to the language of the page it serves.
    */
    'default' => env('APP_LOCALE', 'ar'),

    /*
    | The session key the chosen locale is stored under.
    */
    'session_key' => 'locale',

];
