<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        // A catalogue that already exists is left alone. Without this, every
        // re-seed appended another 5 categories and 30 products to it.
        if (Product::query()->exists()) {
            $this->command?->info('StoreSeeder skipped: the catalogue already has products.');

            return;
        }

        // Create 5 main categories
        $categories = Category::factory()->count(5)->create();

        // For each category, create 6 products
        foreach ($categories as $category) {
            $products = Product::factory()->count(6)->create([
                'category_id' => $category->id,
            ]);

            // For each product, create 1 variant and 1 image
            foreach ($products as $product) {
                ProductVariant::factory()->create([
                    'product_id' => $product->id,
                ]);

                ProductImage::factory()->create([
                    'product_id' => $product->id,
                    'is_primary' => true,
                ]);
            }
        }
    }
}
