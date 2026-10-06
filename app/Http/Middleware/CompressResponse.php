<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Gzip the pages and JSON the application writes, for a browser that asks.
 *
 * A storefront page is about 300 KB of HTML, most of it repeated class names
 * and srcsets, and gzips to about a seventh of that. A web server in front
 * (nginx `gzip on`) does this better and also covers the built assets; this
 * is for the hosts and the `artisan serve` that do not. When something before
 * us has already encoded the body, it is left alone.
 */
class CompressResponse
{
    /** Below this, the header costs more than the saving. */
    private const MIN_BYTES = 1024;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->compressible($request, $response)) {
            return $response;
        }

        $body = $response->getContent();
        $packed = gzencode($body, 6);

        if ($packed === false) {
            return $response;
        }

        $response->setContent($packed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Content-Length', (string) strlen($packed));
        $response->setVary(array_unique([...$response->getVary(), 'Accept-Encoding']));

        return $response;
    }

    private function compressible(Request $request, Response $response): bool
    {
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return false;
        }

        if (! function_exists('gzencode') || $response->headers->has('Content-Encoding')) {
            return false;
        }

        if (! str_contains((string) $request->headers->get('Accept-Encoding'), 'gzip')) {
            return false;
        }

        $type = (string) $response->headers->get('Content-Type');
        if (! preg_match('#^(text/html|application/json|text/plain|application/xml|text/xml)#', $type)) {
            return false;
        }

        $body = $response->getContent();

        return is_string($body) && strlen($body) >= self::MIN_BYTES;
    }
}
