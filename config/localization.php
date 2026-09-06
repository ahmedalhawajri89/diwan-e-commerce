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
    | `font` names the token suffix used in tokens.css — a locale whose script
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
    | The session key the chosen locale is stored under.
    */
    'session_key' => 'locale',

];
