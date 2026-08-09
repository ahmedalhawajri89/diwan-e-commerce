<?php

namespace Database\Factories;

use App\Models\ProductImage;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        $randomNum = $this->faker->numberBetween(1, 8);

        return [
            'product_id' => Product::factory(),
            'image_url' => "assets/images/product-img-{$randomNum}.jpg",
            'is_primary' => true,
        ];
    }
}
