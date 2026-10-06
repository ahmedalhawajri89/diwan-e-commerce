<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Database\Factories\ProductImageFactory;
use Database\Seeders\StockImageSeeder;

/**
 * Three photographs of each kind of piece, dealt out in turn, so neighbouring
 * products of the same kind do not wear the same picture — and a photograph an
 * admin uploaded is never replaced by a stock one.
 */
it('deals each kind of piece its photographs in turn', function () {
    expect([
        ProductImageFactory::imageFor('Cozy Armchair', 0),
        ProductImageFactory::imageFor('Vintage Armchair', 1),
        ProductImageFactory::imageFor('Rustic Armchair', 2),
        ProductImageFactory::imageFor('Elegant Armchair', 3),
    ])->toBe([
        'assets/images/product-armchair-1.jpg',
        'assets/images/product-armchair-2.jpg',
        'assets/images/product-armchair-3.jpg',
        'assets/images/product-armchair-1.jpg',
    ]);

    expect(ProductImageFactory::imageFor('Something unnamed'))->toBeNull();
});

it('points every stock photograph at a file that exists', function () {
    foreach (ProductImageFactory::BY_TYPE as $type) {
        foreach (range(1, ProductImageFactory::PER_TYPE) as $n) {
            expect(public_path("assets/images/product-{$type}-{$n}.jpg"))->toBeFile();
        }
    }
});

it('re-points old stock photographs and leaves uploads alone', function () {
    $category = Category::factory()->create();

    $first = Product::factory()->create(['name' => 'Cozy Sofa', 'category_id' => $category->id]);
    $second = Product::factory()->create(['name' => 'Rustic Sofa', 'category_id' => $category->id]);
    $uploaded = Product::factory()->create(['name' => 'Classic Sofa', 'category_id' => $category->id]);

    // Written directly, as an older catalogue would hold them.
    ProductImage::query()->insert([
        ['product_id' => $first->id, 'image_url' => 'assets/images/product-sofa.jpg', 'is_primary' => true],
        ['product_id' => $second->id, 'image_url' => 'assets/images/product-sofa.jpg', 'is_primary' => true],
        ['product_id' => $uploaded->id, 'image_url' => 'products/my-own-sofa.jpg', 'is_primary' => true],
    ]);

    $this->seed(StockImageSeeder::class);

    expect(ProductImage::where('product_id', $first->id)->value('image_url'))->toBe('assets/images/product-sofa-1.jpg')
        ->and(ProductImage::where('product_id', $second->id)->value('image_url'))->toBe('assets/images/product-sofa-2.jpg')
        ->and(ProductImage::where('product_id', $uploaded->id)->value('image_url'))->toBe('products/my-own-sofa.jpg');
});
