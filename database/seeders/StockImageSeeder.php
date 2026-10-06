<?php

namespace Database\Seeders;

use App\Models\ProductImage;
use Database\Factories\ProductImageFactory;
use Illuminate\Database\Seeder;

/**
 * Points the demo catalogue at the shop's own stock photographs.
 *
 * StoreSeeder stops as soon as products exist, so a catalogue seeded before
 * the photographs changed keeps the old file names. This re-points those rows
 * — and only those: a row whose image is not one of the stock files under
 * assets/images is a photograph an admin uploaded, and is left alone.
 *
 * Safe to run any number of times:
 *     php artisan db:seed --class=StockImageSeeder
 */
class StockImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = ProductImage::query()
            ->where('image_url', 'like', 'assets/images/product-%')
            ->where('image_url', 'not like', 'assets/images/product-placeholder%')
            ->with('product:id,name')
            ->orderBy('product_id')
            ->get()
            ->filter(fn (ProductImage $image) => $image->product
                && ProductImageFactory::typeFor($image->product->name) !== null);

        // Within each kind, in product order, the photographs are dealt out in
        // turn — the same rule the factory follows for a fresh catalogue.
        $images
            ->groupBy(fn (ProductImage $image) => ProductImageFactory::typeFor($image->product->name))
            ->each(function ($ofKind) {
                $ofKind->values()->each(function (ProductImage $image, int $nth) {
                    $file = ProductImageFactory::imageFor($image->product->name, $nth);

                    if ($file !== $image->image_url) {
                        $image->update(['image_url' => $file]);
                    }
                });
            });
    }
}
