<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Content-Security-Policy, and the per-request nonce the inline scripts
 * are signed with.
 *
 * Two earlier passes made this possible rather than theoretical. The app had
 * 408 inline `style` attributes and 11 `on*` handlers; both are gone, so
 * `script-src` can be locked to a nonce instead of surrendering to
 * `'unsafe-inline'` — which is the single directive that decides whether an
 * injected <script> runs.
 *
 * What still needs an escape hatch, and why:
 *
 *   `style-src-attr 'unsafe-inline'` — four `style` attributes remain, each
 *   carrying a value a stylesheet cannot know: the scale figure's computed
 *   geometry, a lookbook hotspot's coordinates, a swatch's real colour. They
 *   are enumerated and guarded in tests/Unit/DesignSystemTest. A `style`
 *   attribute cannot take a nonce — the spec has no such thing — so this is
 *   the floor, not a compromise that can be tightened later.
 *
 *   `script-src` also lists cdn.jsdelivr.net, for SweetAlert2. Self-hosting
 *   it would remove the third party entirely; that is a separate change.
 *
 * The nonce is shared three ways so one value covers every inline script:
 *   - Laravel's Vite helper, for its module tag and its prefetch snippet;
 *   - the views, through `$cspNonce`, for this project's own scripts;
 *   - PHPFlasher, which reads it from a request header it defines.
 */
class ContentSecurityPolicy
{
    /**
     * Flasher looks at these before generating a nonce of its own, and it
     * checks for BOTH — setting only the script header leaves it falling back
     * to a value this policy has never heard of, and its toast script is then
     * blocked in exactly the case the policy is supposed to permit.
     */
    private const FLASHER_SCRIPT_NONCE = 'X-PHPFlasher-Script-Nonce';

    private const FLASHER_STYLE_NONCE = 'X-PHPFlasher-Style-Nonce';

    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Str::random(24);

        // Laravel signs both the module tag and the `Vite::prefetch` snippet.
        Vite::useCspNonce($nonce);

        // Flasher reads these off the *request*, so they have to be set
        // before the response is rendered rather than after.
        $request->headers->set(self::FLASHER_SCRIPT_NONCE, $nonce);
        $request->headers->set(self::FLASHER_STYLE_NONCE, $nonce);

        view()->share('cspNonce', $nonce);

        $response = $next($request);

        // A policy on a PDF or a JSON payload protects nothing and costs a
        // header on every asset response.
        if (! $this->isHtml($response)) {
            return $response;
        }

        $header = config('csp.report_only')
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';

        $response->headers->set($header, $this->policy($nonce));

        // Not CSP, but the same job and no reason to need a second middleware.
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }

    private function policy(string $nonce): string
    {
        $directives = [
            "default-src 'self'",

            // `strict-dynamic` is deliberately omitted: it would let a nonced
            // script load further scripts from anywhere, and this app's
            // scripts do not need to.
            "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net",

            // `'unsafe-inline'` here is SweetAlert2's doing, and it was found
            // by loading the page under the policy rather than by reading the
            // markup: the library injects a <style> element at runtime, which
            // a nonce cannot reach because this app never writes that tag.
            // Blocked, its dialogs render unstyled.
            //
            // The cost is bounded and much smaller than the script equivalent
            // — an injected style can restyle a page, not run code — and it is
            // removable: SweetAlert2 has two call sites, an admin delete
            // confirmation and a fallback error toast. Replacing them with
            // this project's own modal and note components would drop the
            // third-party origin from `script-src` AND let this line become
            // `style-src 'self' https://fonts.googleapis.com`.
            "style-src 'self' 'unsafe-inline'",

            // Stated separately from the line above so it is clear this is a
            // different decision. Four `style` attributes carry values a
            // stylesheet cannot know, and the spec has no nonce for an
            // attribute — so this one is a floor, not a temporary allowance.
            "style-src-attr 'unsafe-inline'",

            // Typefaces are bundled and same-origin now. `fonts.googleapis.com`
            // and `fonts.gstatic.com` were here until the fonts were
            // self-hosted; a policy that lists an origin nothing loads is a
            // permission granted for no reason.
            "font-src 'self' data:",

            // `data:` covers the SVG data URIs Bootstrap uses for form marks.
            "img-src 'self' data:",

            // The search box and the wishlist both fetch same-origin.
            "connect-src 'self'",

            "form-action 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
        ];

        if ($uri = config('csp.report_uri')) {
            $directives[] = 'report-uri '.$uri;
        }

        return implode('; ', $directives);
    }

    private function isHtml(Response $response): bool
    {
        return str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }
}
