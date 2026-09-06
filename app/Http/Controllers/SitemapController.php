<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * The sitemap, generated rather than maintained.
 *
 * A hand-written sitemap.xml is a file someone forgets. This reads the same
 * tables the catalogue does, so a product that goes live is in the sitemap the
 * moment the cache turns over, and a product that is delisted leaves it.
 *
 * Only `active` products and only real routes. A sitemap that lists a page
 * returning 404 is a sitemap a crawler stops trusting.
 */
class SitemapController extends Controller
{
    /** Long enough to be free on a crawl, short enough that a new piece appears the same day. */
    private const TTL = 3600;

    public function index(): Response
    {
        $xml = Cache::remember('diwan.sitemap', self::TTL, fn () => $this->build());

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',

            // The pages behind a login hold one person's data and are useless
            // in an index. Not a security measure — the middleware is that —
            // but there is no reason to spend a crawl budget on a redirect.
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /orders',
            'Disallow: /profile',
            'Disallow: /wishlist',
            'Disallow: /admin',

            // Filter permutations are the catalogue with a query string. The
            // canonical tag already says so; this saves the crawl.
            'Disallow: /products?*',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }

    private function build(): string
    {
        $urls = [];

        // Static pages, with the priority each one actually deserves rather
        // than 1.0 on everything — which tells a crawler nothing.
        foreach ([
            ['furnish.index', [], '1.0', 'weekly'],
            ['furnish.products', [], '0.9', 'daily'],
            ['furnish.lookbook', [], '0.7', 'monthly'],
            ['furnish.about', [], '0.5', 'yearly'],
            ['furnish.testimonials', [], '0.5', 'weekly'],
            ['furnish.contact', [], '0.4', 'yearly'],
        ] as [$name, $params, $priority, $frequency]) {
            $urls[] = ['loc' => route($name, $params), 'priority' => $priority, 'changefreq' => $frequency];
        }

        foreach (['shipping', 'privacy', 'terms'] as $page) {
            $urls[] = ['loc' => route('furnish.page', $page), 'priority' => '0.3', 'changefreq' => 'yearly'];
        }

        Category::orderBy('name')->get()->each(function (Category $category) use (&$urls) {
            $urls[] = [
                'loc' => route('furnish.category', $category->slug),
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $category->updated_at?->toAtomString(),
            ];
        });

        Product::where('status', 'active')
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->each(function (Product $product) use (&$urls) {
                $urls[] = [
                    'loc' => route('furnish.product.show', $product->slug),
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod' => $product->updated_at?->toAtomString(),
                ];
            });

        return $this->render($urls);
    }

    /** @param  list<array<string, string|null>>  $urls */
    private function render(array $urls): string
    {
        $locales = array_keys(config('localization.supported'));

        $xml = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
            .' xmlns:xhtml="http://www.w3.org/1999/xhtml">';

        foreach ($urls as $url) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>'.e($url['loc']).'</loc>';

            if (! empty($url['lastmod'])) {
                $xml[] = '    <lastmod>'.e($url['lastmod']).'</lastmod>';
            }

            $xml[] = '    <changefreq>'.$url['changefreq'].'</changefreq>';
            $xml[] = '    <priority>'.$url['priority'].'</priority>';

            // The shop speaks two languages from the same URLs, so each entry
            // declares its alternates rather than the sitemap listing every
            // page twice.
            foreach ($locales as $locale) {
                $xml[] = '    <xhtml:link rel="alternate" hreflang="'.$locale
                    .'" href="'.e(route('locale.switch', $locale)).'" />';
            }

            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return implode("\n", $xml)."\n";
    }
}
