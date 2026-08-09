<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'color' => $this->faker->randomElement(['Black', 'White', 'Wood', 'Grey', 'Brown', 'Navy', 'Beige']),
            'size' => $this->faker->randomElement(['Standard', 'Large', 'Small', 'Medium']),
            'price' => $this->faker->randomFloat(2, 20, 999),
            'stock' => $this->faker->numberBetween(5, 100),
        ];
    }
}
