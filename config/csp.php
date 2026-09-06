<?php

/**
 * Content-Security-Policy switches.
 *
 * The policy itself lives in App\Http\Middleware\ContentSecurityPolicy, where
 * each directive is next to the reason it is shaped the way it is. Only the
 * two operational choices are here.
 */

return [

    /*
     | Report without enforcing.
     |
     | Ship a policy change in report-only first on anything with real traffic:
     | the browser reports what it would have blocked instead of breaking the
     | page for whoever was mid-checkout. Flip it back once the reports are
     | quiet.
     |
     | Enforcing by default, because the policy below was verified against the
     | rendered pages rather than guessed at — but the switch exists so a
     | change to it does not have to be.
     */
    'report_only' => (bool) env('CSP_REPORT_ONLY', false),

    /*
     | Where violation reports go. Empty means the directive is omitted
     | entirely rather than pointed at a URL that 404s.
     */
    'report_uri' => env('CSP_REPORT_URI'),

];
