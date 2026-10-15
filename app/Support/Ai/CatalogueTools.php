<?php

namespace App\Support\Ai;

use App\Http\Controllers\RoomController;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Fit;
use App\Support\Passage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * What the shopping assistant may look at: the live catalogue, read only.
 *
 * Every fact it hands the model comes from a row: a price is a variant's
 * price, a size is its recorded dimensions or nothing, a fit is
 * App\Support\Fit's verdict on the visitor's room. The model is told to say
 * nothing about a piece these results do not say.
 */
final class CatalogueTools
{
    public const LIMIT = 8;

    /** The tool definitions, in the SDK's camelCase shape. */
    public static function definitions(): array
    {
        return [
            [
                'name' => 'search_catalogue',
                'description' => 'Search the shop\'s live catalogue. Returns up to 8 pieces with their real price, colours, recorded dimensions (or null when not measured) and, when a room is known, whether the piece fits it. Call this before recommending anything.',
                'strict' => true,
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'words' => ['type' => ['string', 'null'], 'description' => 'Words to look for in the name, category or description, in English or Arabic, e.g. "sofa", "كنبة", "oak table". Null to browse.'],
                        'category' => ['type' => ['string', 'null'], 'description' => 'A room or category name, e.g. "Living room", "Bedroom", "غرفة النوم". Null for any.'],
                        'colour' => ['type' => ['string', 'null'], 'description' => 'A colour in English, e.g. "grey", "beige". Null for any.'],
                        'max_price' => ['type' => ['number', 'null'], 'description' => 'Highest price the visitor will pay. Null for no limit.'],
                        'min_price' => ['type' => ['number', 'null'], 'description' => 'Lowest price. Null for none.'],
                        'room_width_cm' => ['type' => ['integer', 'null'], 'description' => 'Width of the visitor\'s room in centimetres, if they said it in this conversation (3 m = 300). Null to use the room saved on the site, if any.'],
                        'room_length_cm' => ['type' => ['integer', 'null'], 'description' => 'Length of the visitor\'s room in centimetres. Null as above.'],
                        'only_fitting' => ['type' => 'boolean', 'description' => 'True to return only pieces that fit the room.'],
                    ],
                    'required' => ['words', 'category', 'colour', 'max_price', 'min_price', 'room_width_cm', 'room_length_cm', 'only_fitting'],
                    'additionalProperties' => false,
                ],
            ],
            [
                'name' => 'get_piece',
                'description' => 'The full record of one piece by its id: description, every colour with its price and stock state, and dimensions.',
                'strict' => true,
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => ['id' => ['type' => 'integer']],
                    'required' => ['id'],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array{width:int,length:int}|null  $savedRoom
     * @return array{room: array|null, results: list<array>}
     */
    public static function search(array $input, ?array $savedRoom): array
    {
        $room = ($input['room_width_cm'] ?? null) && ($input['room_length_cm'] ?? null)
            ? ['width' => max(50, min(3000, (int) $input['room_width_cm'])), 'length' => max(50, min(3000, (int) $input['room_length_cm']))]
            : $savedRoom;

        $query = Product::with(['category', 'variants'])->where('status', 'active')->has('variants');

        if (filled($input['words'] ?? null)) {
            $terms = collect(preg_split('/\s+/u', mb_strtolower(trim($input['words']))))->filter(fn ($t) => mb_strlen($t) >= 2)->take(5);
            $query->where(function (Builder $q) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%'.addcslashes($term, '%_\\').'%';
                    $q->orWhere('name', 'like', $like)->orWhere('name_ar', 'like', $like)
                        ->orWhere('description', 'like', $like)->orWhere('description_ar', 'like', $like)
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like)->orWhere('name_ar', 'like', $like));
                }
            });
        }

        if (filled($input['category'] ?? null)) {
            $like = '%'.addcslashes(trim($input['category']), '%_\\').'%';
            $ids = Category::where('name', 'like', $like)->orWhere('name_ar', 'like', $like)->orWhere('slug', 'like', $like)->pluck('id');
            $query->whereIn('category_id', $ids);
        }

        if (filled($input['colour'] ?? null)) {
            $query->whereHas('variants', fn ($v) => $v->where('color', 'like', '%'.addcslashes(trim($input['colour']), '%_\\').'%'));
        }

        $min = $input['min_price'] ?? null;
        $max = $input['max_price'] ?? null;
        if ($min !== null || $max !== null) {
            $query->whereHas('variants', function ($v) use ($min, $max) {
                if ($min !== null) {
                    $v->where('price', '>=', (float) $min);
                }
                if ($max !== null) {
                    $v->where('price', '<=', (float) $max);
                }
            });
        }

        $results = $query->limit(40)->get()
            ->map(fn (Product $p) => self::summary($p, $room))
            ->when(($input['only_fitting'] ?? false) && $room, fn (Collection $c) => $c->filter(fn ($r) => $r['fit'] === Fit::FITS))
            ->sortBy(fn ($r) => [$r['fit'] === Fit::FITS ? 0 : ($r['fit'] === Fit::TIGHT ? 1 : 2), $r['price']])
            ->take(self::LIMIT)
            ->values()
            ->all();

        return ['room' => $room, 'results' => $results];
    }

    /** @return array<string, mixed>|null */
    public static function piece(int $id, ?array $room): ?array
    {
        $product = Product::with(['category', 'variants'])->where('status', 'active')->find($id);

        if (! $product) {
            return null;
        }

        return self::summary($product, $room) + [
            'description' => $product->description,
            'colours_detail' => $product->variants->map(fn (ProductVariant $v) => [
                'colour' => $v->colourLabel(),
                'price' => (float) $v->price,
                'in_stock' => $v->stock > 0,
                'dimensions_cm' => $v->hasDimensions() ? ['width' => $v->width_cm, 'depth' => $v->depth_cm, 'height' => $v->height_cm] : null,
            ])->values()->all(),
        ];
    }

    /** One piece as the model sees it, in the visitor's language. */
    private static function summary(Product $product, ?array $room): array
    {
        $variant = $product->primaryVariant();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category?->name,
            'price' => (float) $variant->price,
            'colours' => $product->variants->map(fn (ProductVariant $v) => $v->colourLabel())->filter()->unique()->values()->all(),
            'in_stock' => $product->variants->sum('stock') > 0,
            'dimensions_cm' => $variant->hasDimensions() ? ['width' => $variant->width_cm, 'depth' => $variant->depth_cm, 'height' => $variant->height_cm] : null,
            'fit' => $room ? Fit::assess($variant, $room)['status'] : null,
            // Whether it can reach the room, by the visitor's way in (door, lift
            // or stairs, balcony), when they have given one.
            'gets_in' => ($wayIn = RoomController::route()) ? Passage::assess($variant, $wayIn)['status'] : null,
        ];
    }
}
