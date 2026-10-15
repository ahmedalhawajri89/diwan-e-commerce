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

    /** How many photographs there are of each kind of piece. */
    public const PER_TYPE = 3;

    /** The kind of piece a product name names, e.g. "sofa", or null. */
    public static function typeFor(string $productName): ?string
    {
        foreach (self::BY_TYPE as $noun => $type) {
            if (str_contains($productName, $noun)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * The file a product's name calls for, or null when nothing matches.
     *
     * Each kind has several photographs, and $nth — the product's place among
     * pieces of the same kind — walks through them in turn, so two armchairs
     * never share a picture until every armchair photograph has been used.
     */
    public static function imageFor(string $productName, int $nth = 0): ?string
    {
        $type = self::typeFor($productName);

        if ($type === null) {
            return null;
        }

        $n = ($nth % self::PER_TYPE) + 1;

        return "assets/images/product-{$type}-{$n}.jpg";
    }

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'image_url' => 'assets/images/product-sofa-1.jpg',
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
            $type = $name ? self::typeFor($name) : null;

            if ($type === null) {
                return;
            }

            // How many of this kind already have a picture decides which one
            // this piece gets next.
            $nth = ProductImage::where('image_url', 'like', "assets/images/product-{$type}-%")->count();

            $image->image_url = self::imageFor($name, $nth);
        });
    }
}
