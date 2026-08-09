<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $adjectives = ['Modern', 'Classic', 'Vintage', 'Rustic', 'Minimalist', 'Cozy', 'Elegant', 'Luxury', 'Compact'];
        $nouns = ['Sofa', 'Chair', 'Table', 'Desk', 'Bed', 'Cabinet', 'Lamp', 'Shelf', 'Armchair', 'Stool'];
        
        $name = $this->faker->randomElement($adjectives) . ' ' . $this->faker->randomElement($nouns);

        return [
            'name' => $name,
            'slug' => Str::slug($name . '-' . Str::random(4)),
            'description' => $this->faker->paragraph(3),
            'status' => 'active',
            'category_id' => Category::factory(),
        ];
    }
}
