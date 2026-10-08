<?php

namespace App\Support\Ai;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Responsive;

/**
 * What the shop knows about a piece, and only that: the facts a draft may
 * use. Dimensions are included only when all three are recorded; price and
 * stock are left out, because neither belongs in a description and both
 * change. Nothing here is guessed or filled in.
 */
final class ProductFacts
{
    /** @return array<string, mixed> */
    public static function fromProduct(Product $product): array
    {
        $product->loadMissing(['category', 'variants', 'images']);
        $variant = $product->primaryVariant();

        return self::clean([
            'name' => $product->raw('name'),
            'name_ar' => $product->raw('name_ar'),
            'category' => $product->category?->raw('name'),
            'category_ar' => $product->category?->raw('name_ar'),
            'colours' => $product->variants->map(fn (ProductVariant $v) => $v->color)->filter()->unique()->values()->all(),
            'dimensions_cm' => self::dimensions($variant),
            'image' => self::imagePath($product->primaryImage()?->url),
        ]);
    }

    /**
     * From the product form before anything is saved.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function fromInput(array $input): array
    {
        $category = isset($input['category_id']) ? Category::find($input['category_id']) : null;
        $w = (int) ($input['width_cm'] ?? 0);
        $d = (int) ($input['depth_cm'] ?? 0);
        $h = (int) ($input['height_cm'] ?? 0);

        return self::clean([
            'name' => trim((string) ($input['name'] ?? '')),
            'name_ar' => trim((string) ($input['name_ar'] ?? '')),
            'category' => $category?->raw('name'),
            'category_ar' => $category?->raw('name_ar'),
            'colours' => array_values(array_filter([trim((string) ($input['color'] ?? ''))])),
            'dimensions_cm' => $w && $d && $h ? ['width' => $w, 'depth' => $d, 'height' => $h] : null,
        ]);
    }

    /** @return array{width: int, depth: int, height: int}|null */
    private static function dimensions(?ProductVariant $variant): ?array
    {
        return $variant?->hasDimensions()
            ? ['width' => (int) $variant->width_cm, 'depth' => (int) $variant->depth_cm, 'height' => (int) $variant->height_cm]
            : null;
    }

    /**
     * A local file the request can carry, smallest derived size first: the
     * API cannot reach a photograph on localhost by URL.
     */
    private static function imagePath(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        foreach ([Responsive::smallest($url), $url] as $candidate) {
            $path = public_path(ltrim((string) parse_url($candidate, PHP_URL_PATH), '/'));
            if (is_file($path) && ! str_contains($path, 'product-placeholder')) {
                return $path;
            }
        }

        return null;
    }

    /** Drop what is empty, so the model is never told "colour: (none)". */
    private static function clean(array $facts): array
    {
        return array_filter($facts, fn ($v) => $v !== null && $v !== '' && $v !== []);
    }
}
