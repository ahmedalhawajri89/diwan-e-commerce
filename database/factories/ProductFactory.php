<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\DemoCatalogue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $noun = $this->faker->randomElement(array_keys(self::ROOMS_FOR));
        $adjective = $this->faker->randomElement(array_keys(DemoCatalogue::ADJECTIVES));
        $name = $adjective.' '.$noun;

        // Real words in both languages, from the demo catalogue: a factory that
        // writes Latin filler is how "Pariatur quia ipsam" reached a product
        // page.
        return [
            'name' => $name,
            'name_ar' => DemoCatalogue::arabicName($adjective, $noun),
            'slug' => Str::slug($name.'-'.Str::random(4)),
            'description' => DemoCatalogue::TYPES[$noun][2],
            'description_ar' => DemoCatalogue::TYPES[$noun][3],
            'status' => 'active',
            // A closure, so it is evaluated only when the caller does not supply
            // a category of its own. Called eagerly it created a room through
            // firstOrCreate as a side effect of *defining* a product — even for
            // `Product::factory()->create(['category_id' => $x])`, which then
            // left an extra category sitting in the database and broke a test
            // that counted them.
            'category_id' => fn () => $this->roomFor($noun),
        ];
    }

    /**
     * Which rooms each kind of piece plausibly belongs in.
     *
     * The category used to be an unrelated `Category::factory()`, so the seeded
     * catalogue offered a bed in the dining room and a sofa in the office.
     * Nothing breaks, but every screenshot of the shop is quietly absurd, and
     * a demo catalogue is the first thing anyone judging this reads.
     *
     * Several pieces genuinely belong in more than one room — a lamp, a chair,
     * a shelf — so the choice stays random within a sensible set rather than
     * being pinned to one. A dining table is a dining table.
     */
    private const ROOMS_FOR = [
        'Sofa' => ['living-room'],
        'Armchair' => ['living-room', 'office'],
        'Chair' => ['dining-room', 'office'],
        'Stool' => ['dining-room'],
        'Table' => ['dining-room'],
        'Desk' => ['office'],
        'Bed' => ['bedroom'],
        'Cabinet' => ['storage', 'bedroom'],
        'Lamp' => ['lighting'],
        'Shelf' => ['storage', 'office'],
    ];

    /** The kinds of piece the factory makes, in its own order. */
    public static function types(): array
    {
        return array_keys(self::ROOMS_FOR);
    }

    /** The rooms a kind of piece belongs in. */
    public static function roomsFor(string $type): array
    {
        return self::ROOMS_FOR[$type];
    }

    /** The id of a room this kind of piece belongs in. */
    private function roomFor(string $noun): int
    {
        $slug = $this->faker->randomElement(self::ROOMS_FOR[$noun]);

        // firstOrCreate rather than factory(): the seeder creates the five real
        // categories, and a factory here would invent a sixth with a faker name
        // beside them.
        [$name, $nameAr, $description, $descriptionAr] = DemoCatalogue::CATEGORIES[$slug];

        return Category::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'name_ar' => $nameAr, 'description' => $description, 'description_ar' => $descriptionAr]
        )->id;
    }
}
