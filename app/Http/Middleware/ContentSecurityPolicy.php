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
 * Nothing is loaded from a third-party origin. SweetAlert2 (from a CDN) and
 * PHPFlasher used to be the exceptions; the shop's own <dialog> and toast
 * replaced both, so `script-src` names only this origin and the nonce, and
 * `style-src` no longer needs `'unsafe-inline'`.
 *
 * The nonce is shared two ways so one value covers every inline script and
 * style:
 *   - Laravel's Vite helper, for its module tag and its prefetch snippet;
 *   - the views, through `$cspNonce`, for this project's own blocks.
 */
class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = Str::random(24);

        // Laravel signs both the module tag and the `Vite::prefetch` snippet.
        Vite::useCspNonce($nonce);

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
        [$dev, $devSocket] = $this->viteDevServer();

        $directives = [
            "default-src 'self'",

            // `strict-dynamic` is deliberately omitted: it would let a nonced
            // script load further scripts from anywhere, and this app's
            // scripts do not need to.
            "script-src 'self' 'nonce-{$nonce}'{$dev}",

            // Stylesheets from this origin, and the one inline <style> block
            // the app writes (the standalone 500 page), signed with the nonce.
            //
            // While the Vite dev server runs it injects <style> elements from
            // JavaScript, which no nonce can reach, so development gets
            // `'unsafe-inline'` instead — and *instead* is the point: a
            // browser ignores `'unsafe-inline'` whenever a nonce is present,
            // so listing both would still block every injected style.
            $dev
                ? "style-src 'self' 'unsafe-inline'{$dev}"
                : "style-src 'self' 'nonce-{$nonce}'",

            // Stated separately from the line above so it is clear this is a
            // different decision. Four `style` attributes carry values a
            // stylesheet cannot know, and the spec has no nonce for an
            // attribute — so this one is a floor, not a temporary allowance.
            "style-src-attr 'unsafe-inline'",

            // Typefaces are bundled and same-origin now. `fonts.googleapis.com`
            // and `fonts.gstatic.com` were here until the fonts were
            // self-hosted; a policy that lists an origin nothing loads is a
            // permission granted for no reason.
            "font-src 'self' data:{$dev}",

            // `data:` covers the SVG data URIs Bootstrap uses for form marks.
            "img-src 'self' data:{$dev}",

            // The search box and the wishlist both fetch same-origin.
            "connect-src 'self'{$dev}{$devSocket}",

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

    /**
     * The Vite dev server's origin, while `npm run dev` is running.
     *
     * With `public/hot` present, `@vite` points every stylesheet and module at
     * the dev server on another port — a different origin, so `'self'` does
     * not cover it and the page rendered with no CSS and no JS at all. The
     * socket origin is for hot reload, which talks over a WebSocket.
     *
     * In production there is no hot file, both strings are empty, and the
     * policy is byte-for-byte what it was.
     *
     * Returned with a leading space so the directives can append it as-is.
     *
     * @return array{0: string, 1: string}
     */
    private function viteDevServer(): array
    {
        if (! Vite::isRunningHot()) {
            return ['', ''];
        }

        $url = parse_url(trim((string) file_get_contents(Vite::hotFile())));

        if (empty($url['host'])) {
            return ['', ''];
        }

        // CSP has no syntax for an IPv6 literal such as [::1], so a browser
        // drops that source silently. vite.config.js pins the dev server to
        // 127.0.0.1 for exactly this reason.
        $host = $url['host'].(isset($url['port']) ? ':'.$url['port'] : '');
        $secure = ($url['scheme'] ?? 'http') === 'https';

        return [
            ' '.($secure ? 'https' : 'http').'://'.$host,
            ' '.($secure ? 'wss' : 'ws').'://'.$host,
        ];
    }

    private function isHtml(Response $response): bool
    {
        return str_contains((string) $response->headers->get('Content-Type'), 'text/html');
    }
}
