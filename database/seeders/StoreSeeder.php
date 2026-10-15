<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Database\Factories\ProductFactory;
use Database\Factories\ProductImageFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * The demonstration catalogue: six rooms, three pieces of each kind, in
 * English and Arabic, each in one to three colours.
 *
 * It used to make five categories picked at random and force six random
 * products into each, so a sofa could be filed under "Kids" and every
 * description was Latin filler. Every word now comes from DemoCatalogue, and
 * every piece sits in a room it belongs in.
 */
class StoreSeeder extends Seeder
{
    /** How many pieces of each kind. Matches the photographs per kind. */
    private const PER_TYPE = 3;

    public function run(): void
    {
        // A catalogue that already exists is left alone. Without this, every
        // re-seed appended another catalogue to it.
        if (Product::query()->exists()) {
            $this->command?->info('StoreSeeder skipped: the catalogue already has products.');

            return;
        }

        $rooms = [];
        foreach (DemoCatalogue::CATEGORIES as $slug => [$name, $nameAr, $description, $descriptionAr]) {
            $rooms[$slug] = Category::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'name_ar' => $nameAr,
                'description' => $description,
                'description_ar' => $descriptionAr,
            ]);
        }

        $adjectives = array_keys(DemoCatalogue::ADJECTIVES);

        foreach (ProductFactory::types() as $t => $type) {
            for ($n = 0; $n < self::PER_TYPE; $n++) {
                // Walk the adjectives so no two pieces of a kind share a name.
                $adjective = $adjectives[($t + $n * 4) % count($adjectives)];
                $name = "{$adjective} {$type}";
                $room = $rooms[Arr::random(ProductFactory::roomsFor($type))];

                $product = Product::create([
                    'name' => $name,
                    'name_ar' => DemoCatalogue::arabicName($adjective, $type),
                    'slug' => Str::slug($name),
                    'description' => DemoCatalogue::TYPES[$type][2],
                    'description_ar' => DemoCatalogue::TYPES[$type][3],
                    'status' => 'active',
                    'category_id' => $room->id,
                ]);

                $this->colours($product, $type);

                // The nth photograph of this kind: each piece its own.
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => ProductImageFactory::imageFor($name, $n),
                    'is_primary' => true,
                ]);
            }
        }
    }

    /**
     * One to three colours of the same piece. The first is made by the variant
     * factory, which infers real-world dimensions from the name, or leaves
     * them unrecorded about one time in seven, so the honest fallback shows.
     * The other colours are the same piece, so they share its measurements.
     */
    private function colours(Product $product, string $type): void
    {
        [$low, $high] = DemoCatalogue::PRICES[$type];
        $price = round(random_int($low, $high), -1) - 1;   // 1499, not 1503.27

        $colours = Arr::wrap(Arr::random(DemoCatalogue::COLOURS, random_int(1, 3)));

        $first = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'color' => array_shift($colours),
            'size' => null,
            'price' => $price,
            'stock' => $this->stock(),
        ]);

        foreach ($colours as $colour) {
            ProductVariant::create([
                'product_id' => $product->id,
                'color' => $colour,
                'size' => null,
                'price' => $price,
                'stock' => $this->stock(),
                'width_cm' => $first->width_cm,
                'depth_cm' => $first->depth_cm,
                'height_cm' => $first->height_cm,
            ]);
        }
    }

    /** Mostly in stock, some running low, the odd one gone: every state shows. */
    private function stock(): int
    {
        return Arr::random([0, 2, 4, 8, 12, 18, 24, 30, 40, 40]);
    }
}
