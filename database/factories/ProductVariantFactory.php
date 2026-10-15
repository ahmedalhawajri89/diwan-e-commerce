<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Real-world dimension ranges per furniture type, in centimetres:
     * [minW, maxW, minD, maxD, minH, maxH]. A random box would make the fit
     * system nonsense — a stool that is 210cm wide teaches the tester nothing.
     */
    private const DIMENSIONS = [
        'Sofa' => [180, 240,  85, 100,  70,  90],
        'Armchair' => [70, 100,  75,  95,  75,  95],
        'Chair' => [45,  60,  50,  62,  80,  95],
        'Stool' => [35,  45,  35,  45,  45,  70],
        'Table' => [120, 200,  75, 100,  72,  78],
        'Desk' => [110, 160,  60,  80,  72,  76],
        'Bed' => [140, 200, 190, 215,  40, 120],
        'Cabinet' => [80, 140,  40,  50, 120, 200],
        'Shelf' => [60, 120,  28,  40, 150, 220],
        'Lamp' => [25,  45,  25,  45, 120, 175],
    ];

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

    /**
     * Dimensions inferred from the parent product's name.
     *
     * Left off `definition()` on purpose: the fit system has to render its
     * "not recorded" state, and it can only be exercised if some rows really
     * do lack dimensions.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (ProductVariant $variant) {
            // An explicit value always wins. Passing width_cm to create() —
            // including passing null on purpose — must not be overwritten by
            // this hook; a factory that ignores what the caller asked for is a
            // trap, and it silently broke the first tests written against it.
            if (array_key_exists('width_cm', $variant->getAttributes())) {
                return;
            }

            // Roughly one in seven stays unmeasured so the honest fallback is
            // visible in a seeded catalogue.
            if ($this->faker->numberBetween(1, 7) === 1) {
                return;
            }

            // The stored English name, not the `name` accessor: in Arabic (the
            // shop's default) that returns «كنبة عصرية», which matches no kind
            // below, and a whole catalogue was once seeded with no measurements.
            $name = Product::find($variant->product_id)?->getRawOriginal('name') ?? '';
            $range = null;

            foreach (self::DIMENSIONS as $type => $bounds) {
                if (str_contains($name, $type)) {
                    $range = $bounds;
                    break;
                }
            }

            if (! $range) {
                return;
            }

            [$minW, $maxW, $minD, $maxD, $minH, $maxH] = $range;
            $variant->width_cm = $this->faker->numberBetween($minW, $maxW);
            $variant->depth_cm = $this->faker->numberBetween($minD, $maxD);
            $variant->height_cm = $this->faker->numberBetween($minH, $maxH);
        });
    }
}
