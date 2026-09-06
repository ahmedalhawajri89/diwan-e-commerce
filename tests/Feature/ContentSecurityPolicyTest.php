<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * The Content-Security-Policy, and the property that makes it worth having.
 *
 * `script-src` is locked to a per-request nonce rather than `'unsafe-inline'`,
 * which is only possible because two earlier passes removed 408 inline `style`
 * attributes and 11 `on*` handlers. That leaves one thing to protect: every
 * inline script this app renders must carry the nonce, and nothing else may.
 *
 * A single unsigned `<script>` anywhere would force a future maintainer to add
 * `'unsafe-inline'` back to make the page work — and the policy would then be
 * decoration. That is what these guard.
 */
function policyFor(string $url): array
{
    $response = test()->get($url);
    $response->assertOk();

    $header = $response->headers->get('Content-Security-Policy');

    return [
        'header' => $header,
        'html' => $response->getContent(),
        'nonce' => preg_match("/'nonce-([^']+)'/", (string) $header, $m) ? $m[1] : null,
    ];
}

function storefrontPages(): array
{
    $category = Category::factory()->create(['slug' => 'living-room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100]);

    return [
        'home' => route('furnish.index'),
        'catalogue' => route('furnish.products'),
        'product' => route('furnish.product.show', $product->slug),
        'lookbook' => route('furnish.lookbook'),
        'login' => route('login'),
    ];
}

it('sends a policy on every page', function () {
    foreach (storefrontPages() as $name => $url) {
        $policy = policyFor($url);

        expect($policy['header'])->not->toBeNull("{$name} has no Content-Security-Policy header");
        expect($policy['nonce'])->not->toBeNull("{$name}'s policy carries no nonce");
    }
});

it('locks script-src to a nonce rather than unsafe-inline', function () {
    $header = policyFor(route('furnish.index'))['header'];

    preg_match('/script-src ([^;]+)/', $header, $m);
    $scriptSrc = $m[1] ?? '';

    // Asserted with toBeFalse rather than `not->toContain($needle, $message)`:
    // Pest reads every argument to toContain as another needle, so a message
    // passed there silently turns the check into something that cannot fail.
    expect(str_contains($scriptSrc, "'unsafe-inline'"))->toBeFalse(
        "`script-src 'unsafe-inline'` is the one directive that stops an "
        .'injected <script> from running. Sign the new script with the nonce '
        .'instead: <script nonce="{{ $cspNonce }}">. Found: '.$scriptSrc
    );

    expect(str_contains($scriptSrc, "'unsafe-eval'"))->toBeFalse(
        'Nothing in this app compiles strings into code. Found: '.$scriptSrc
    );
});

it('signs every inline script it renders', function () {
    foreach (storefrontPages() as $name => $url) {
        $policy = policyFor($url);
        $unsigned = [];

        preg_match_all('/<script([^>]*)>/', $policy['html'], $tags);

        foreach ($tags[1] as $attrs) {
            if (! preg_match('/nonce=["\']([^"\']+)["\']/', $attrs, $n)) {
                $unsigned[] = trim($attrs) ?: '(inline, no attributes)';

                continue;
            }

            expect($n[1])->toBe(
                $policy['nonce'],
                "{$name} has a script signed with a nonce the policy does not "
                .'list. PHPFlasher generates its own unless the middleware '
                .'hands it one through both of its request headers.'
            );
        }

        expect($unsigned)->toBe([], "{$name} renders an unsigned <script>");
    }
});

it('gives each request a fresh nonce', function () {
    // A reused nonce is a nonce an attacker can learn from a cached page and
    // then include in an injected tag.
    $first = policyFor(route('furnish.index'))['nonce'];
    $second = policyFor(route('furnish.index'))['nonce'];

    expect($first)->not->toBe($second);
    expect(strlen((string) $first))->toBeGreaterThanOrEqual(16);
});

it('keeps the rest of the policy closed', function () {
    $header = policyFor(route('furnish.index'))['header'];

    foreach ([
        "default-src 'self'",
        "object-src 'none'",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ] as $directive) {
        expect($header)->toContain($directive);
    }
});

it('sends the two headers that cost nothing and close real holes', function () {
    $response = $this->get(route('furnish.index'));

    expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
    expect($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin');
});

it('does not put a policy on responses that are not documents', function () {
    // A header on every image and JSON payload is bytes for nothing.
    $response = $this->get('/api/search?q=nothing');

    expect($response->headers->get('Content-Security-Policy'))->toBeNull();
});

it('loads no third-party fonts', function () {
    // The typefaces came from fonts.googleapis.com: a blocking stylesheet on a
    // second origin, the faces on a third, and Google told which page every
    // visitor was reading. They are bundled now, and the policy no longer
    // grants either origin — a permission for something nothing loads.
    $html = $this->get(route('furnish.index'))->assertOk()->getContent();

    expect(str_contains($html, 'fonts.googleapis.com'))->toBeFalse('A Google Fonts stylesheet came back.');
    expect(str_contains($html, 'fonts.gstatic.com'))->toBeFalse('A Google Fonts face came back.');

    $policy = policyFor(route('furnish.index'))['header'];

    expect(str_contains($policy, 'fonts.g'))->toBeFalse(
        'The policy still allows a font origin nothing requests.'
    );
});
