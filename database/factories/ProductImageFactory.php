<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    /**
     * The photograph for each kind of piece.
     *
     * Keyed by the nouns ProductFactory builds names from, because the picture
     * has to match the thing. This used to pick one of eight files at random,
     * which put an office chair on "Cozy Stool" and the same sofa on four
     * unrelated products — and a shop whose promise is measurement cannot show
     * you a photograph of something else.
     *
     * The photographs are in public/assets/images, named for what they show.
     * Provenance and licence for each are recorded in assets/images/CREDITS.md.
     */
    public const BY_TYPE = [
        'Sofa' => 'sofa',
        'Armchair' => 'armchair',
        'Chair' => 'chair',
        'Stool' => 'stool',
        'Table' => 'table',
        'Desk' => 'desk',
        'Bed' => 'bed',
        'Cabinet' => 'cabinet',
        'Lamp' => 'lamp',
        'Shelf' => 'shelf',
    ];

    /** The file a product's name calls for, or null when nothing matches. */
    public static function imageFor(string $productName): ?string
    {
        foreach (self::BY_TYPE as $noun => $file) {
            if (str_contains($productName, $noun)) {
                return "assets/images/product-{$file}.jpg";
            }
        }

        return null;
    }

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'image_url' => 'assets/images/product-sofa.jpg',
            'is_primary' => true,
        ];
    }

    public function configure(): static
    {
        // Resolved after making rather than in definition(), because the name
        // lives on the product and definition() runs before the relation is
        // available. An unrecognised noun keeps the default rather than
        // inventing a match.
        return $this->afterMaking(function (ProductImage $image) {
            if ($image->product_id === null) {
                return;
            }

            $name = Product::find($image->product_id)?->name;

            if ($name && $file = self::imageFor($name)) {
                $image->image_url = $file;
            }
        });
    }
}
