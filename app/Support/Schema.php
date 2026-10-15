<?php

namespace App\Support;

use App\Models\Product;

/**
 * Schema.org structured data.
 *
 * This is the one place the shop's differentiator reaches a search engine. Every
 * furniture site publishes a name, a price and a photograph; almost none publish
 * `width`, `depth` and `height` as machine-readable values, because almost none
 * record them. This one does, so the fit system is worth more here than
 * anywhere else on the page.
 *
 * The honesty rules apply with full force. A `Product` node that claims an
 * `aggregateRating` nobody left, an `availability` nobody checked, or a
 * dimension nobody measured is an invented metric that a search engine will
 * repeat to people who never visit the site. Every field below is emitted only
 * when the data behind it is real, and omitted otherwise — a missing property
 * costs a rich result; a false one costs the truth.
 */
final class Schema
{
    /**
     * A product, with its measurements when they exist.
     *
     * @return array<string, mixed>
     */
    public static function product(Product $product): array
    {
        $variant = $product->primaryVariant();
        $price = $product->price;

        $node = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'url' => route('furnish.product.show', $product->slug),
            'image' => [$product->imageUrl()],
        ];

        if (filled($product->description)) {
            $node['description'] = str($product->description)->stripTags()->limit(300)->value();
        }

        if ($product->category) {
            $node['category'] = $product->category->name;
        }

        if ($variant?->sku) {
            $node['sku'] = $variant->sku;
        }

        // Dimensions — the reason this file exists. QuantitativeValue with a
        // UN/CEFACT unit code is what makes a number a measurement rather than
        // a string, and CMT is centimetres.
        foreach (['width' => 'width_cm', 'depth' => 'depth_cm', 'height' => 'height_cm'] as $property => $column) {
            if ($variant?->{$column}) {
                $node[$property] = [
                    '@type' => 'QuantitativeValue',
                    'value' => (float) $variant->{$column},
                    'unitCode' => 'CMT',
                ];
            }
        }

        // `depth` is the schema.org spelling; a piece of furniture's third
        // dimension is what a shopper calls depth, so no translation is needed.

        if ($price !== null) {
            $node['offers'] = array_filter([
                '@type' => 'Offer',
                'url' => route('furnish.product.show', $product->slug),
                'price' => number_format($price, 2, '.', ''),
                'priceCurrency' => config('shop.currency'),

                // `product_variants.stock` is NOT NULL DEFAULT 0, so a count is
                // always a real count and 0 genuinely means none left — there
                // is no "not tracked" state to be careful about here. Checked
                // against the migration rather than assumed: the first version
                // of this method guarded a nullable column that does not exist.
                //
                // A product with no variant at all has no price either, so it
                // never reaches this block and never claims to be purchasable.
                'availability' => $variant->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ]);
        }

        // Only approved reviews count, and only when there are some. A rating
        // aggregated from nothing is the invented metric design.md forbids.
        $approved = $product->reviews->where('status', 'approved');

        if ($approved->isNotEmpty()) {
            $node['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($approved->avg('rating'), 1),
                'reviewCount' => $approved->count(),
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        return $node;
    }

    /**
     * The shop itself, for the home page.
     *
     * No `telephone`, no `address`, no `sameAs` — this application holds none
     * of them, and a Store node with an invented phone number is worse than no
     * Store node.
     *
     * @return array<string, mixed>
     */
    public static function store(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Store',
            'name' => __('diwan.brand.name'),
            'description' => __('diwan.brand.tagline'),
            'url' => route('furnish.index'),
            'image' => asset('assets/images/hero-living-room.jpg'),
        ];
    }

    /**
     * A breadcrumb trail.
     *
     * @param  array<string, string>  $crumbs  label => url, in order
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $crumbs): array
    {
        $items = [];
        $position = 1;

        foreach ($crumbs as $name => $url) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'item' => $url,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}
