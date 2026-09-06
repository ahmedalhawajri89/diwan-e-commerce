<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Support\Fit;
use App\Support\Schema;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class FurnishController extends Controller
{
    /**
     * Everything a product card needs, loaded once.
     *
     * The card reads category, images, and variants; loading them here is the
     * difference between 3 queries and 3 + 3N.
     */
    private const CARD_RELATIONS = ['category', 'images', 'variants'];

    public function index()
    {
        $featuredProducts = Product::with(self::CARD_RELATIONS)
            ->where('status', 'active')
            ->withCount('wishlistItems')
            ->orderByDesc('wishlist_items_count')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        // Proof reads from the real table. No approved reviews means the
        // section does not render — it does not get plausible-sounding fakes.
        $reviews = Review::with(['user', 'product'])
            ->where('status', 'approved')
            ->latest()
            ->take(3)
            ->get();

        $schema = Schema::store();

        return view('furnish.index', compact('featuredProducts', 'reviews', 'schema'));
    }

    public function show($slug)
    {
        $product = Product::with([
            'category',
            'images',
            'variants',
            'reviews' => fn ($q) => $q->where('status', 'approved')->with('user')->latest(),
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $related = $this->randomProducts(
            fn ($query) => $query
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id),
            4
        );

        // Structured data. This is where the fit system reaches a search
        // engine: real width, depth and height as machine-readable values,
        // which is a rich result almost no furniture site can produce.
        $schema = [
            Schema::product($product),
            Schema::breadcrumbs([
                __('diwan.nav.catalogue') => route('furnish.products'),
                $product->category?->name ?? __('diwan.nav.catalogue') => $product->category
                    ? route('furnish.category', $product->category->slug)
                    : route('furnish.products'),
                $product->name => route('furnish.product.show', $product->slug),
            ]),
        ];

        return view('furnish.product_details', compact('product', 'related', 'schema'));
    }

    public function about()
    {
        return view('furnish.about');
    }

    public function products(Request $request)
    {
        $query = Product::with(self::CARD_RELATIONS)->where('status', 'active');

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($sub) => $sub->where('name', 'like', "%{$term}%"))
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('categories')) {
            $query->whereIn('category_id', (array) $request->categories);
        }

        if ($request->filled('prices')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    foreach ((array) $request->prices as $range) {
                        match ($range) {
                            'under_100' => $sub->orWhere('price', '<', 100),
                            '100_300' => $sub->orWhereBetween('price', [100, 300]),
                            'above_300' => $sub->orWhere('price', '>', 300),
                            default => null,
                        };
                    }
                });
            });
        }

        if ($request->filled('colors')) {
            $query->whereHas('variants', fn ($q) => $q->whereIn('color', (array) $request->colors));
        }

        match ($request->input('sort')) {
            'alphabetical_az' => $query->orderBy('name'),
            'alphabetical_za' => $query->orderByDesc('name'),
            'date_new' => $query->latest(),
            'date_old' => $query->oldest(),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        // The fit filter runs after pagination on purpose. It compares against
        // the visitor's own room, which lives in the session, so it cannot be
        // expressed as SQL without denormalising every room into the database.
        $room = RoomController::current();
        $fitOnly = $request->boolean('fit_only') && $room;

        if ($fitOnly) {
            $products->setCollection(
                $products->getCollection()->filter(
                    fn ($product) => Fit::assess($product->primaryVariant(), $room)['status'] === Fit::FITS
                )->values()
            );
        }

        return view('furnish.products', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'availableColors' => ProductVariant::whereNotNull('color')->distinct()->orderBy('color')->pluck('color'),
            'fitOnly' => $fitOnly,
        ]);
    }

    /**
     * A category's own page.
     *
     * The catalogue filter already narrows by category, but `?categories[]=3`
     * is not a link anybody shares and not a URL a search engine keeps. This is
     * the same grid at an address that means something.
     */
    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $products = Product::with(self::CARD_RELATIONS)
            ->where('status', 'active')
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(12);

        $siblings = Category::where('id', '!=', $category->id)
            ->withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get();

        return view('furnish.category', compact('category', 'products', 'siblings'));
    }

    public function testimonials()
    {
        $reviews = Review::with(['user', 'product.images'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(12);

        return view('furnish.testimonials', compact('reviews'));
    }

    public function contact()
    {
        return view('furnish.contact');
    }

    /**
     * Shipping, privacy and terms.
     *
     * One controller action for all three because they differ only in which
     * copy file they read. The copy itself is policy — a delivery window, a
     * returns period, a data-retention promise — and design.md's honesty rules
     * forbid inventing any of it. Each page states plainly which clauses are
     * still awaiting the owner's numbers.
     */
    public function page(string $page)
    {
        abort_unless(in_array($page, ['shipping', 'privacy', 'terms'], true), 404);

        return view('furnish.page', ['page' => $page]);
    }

    public function lookbook()
    {
        $products = $this->randomProducts(
            fn ($query) => $query,
            9,
            ['images', 'variants']
        );

        // Each look is built only when there are real products to pin to it —
        // a room with empty markers teaches the visitor nothing.
        $looks = [];
        $scenes = [
            ['title' => 'lookbook.scene_living', 'image' => 'assets/images/hero-living-room.jpg'],
            ['title' => 'lookbook.scene_work',   'image' => 'assets/images/product-desk.jpg'],
            ['title' => 'lookbook.scene_dining', 'image' => 'assets/images/product-table.jpg'],
        ];
        $spots = [
            [['top' => 40, 'left' => 30], ['top' => 65, 'left' => 70], ['top' => 50, 'left' => 50]],
            [['top' => 30, 'left' => 45], ['top' => 70, 'left' => 60], ['top' => 45, 'left' => 20]],
            [['top' => 55, 'left' => 50], ['top' => 60, 'left' => 25], ['top' => 40, 'left' => 75]],
        ];

        foreach ($scenes as $i => $scene) {
            $slice = $products->slice($i * 3, 3);

            if ($slice->count() < 3) {
                break;
            }

            $looks[] = [
                'id' => $i + 1,
                'title' => $scene['title'],
                'image' => asset($scene['image']),
                'hotspots' => $slice->values()->map(fn ($product, $n) => [
                    'top' => $spots[$i][$n]['top'],
                    'left' => $spots[$i][$n]['left'],
                    'product' => $product,
                ])->all(),
            ];
        }

        return view('furnish.lookbook', compact('looks'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (blank($query)) {
            return response()->json([]);
        }

        $products = Product::with(['images', 'variants', 'category'])
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhereHas('category', fn ($sub) => $sub->where('name', 'like', "%{$query}%"));
            })
            ->take(6)
            ->get()
            ->map(function (Product $product) {
                $variant = $product->primaryVariant();

                return [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $variant ? number_format((float) $variant->price, 2) : null,
                    'image' => $product->imageUrl(),
                    'category' => $product->category?->name,
                ];
            });

        return response()->json($products);
    }

    /**
     * A random handful of products, without asking the database to sort the
     * whole table to get them.
     *
     * `inRandomOrder()` compiles to `ORDER BY RAND()`, which assigns a random
     * value to every matching row and then sorts all of them — a filesort over
     * the full result set to return four items. It is invisible on a seeded
     * catalogue of twenty-nine and it is the first query to hurt on a real one.
     *
     * Two queries instead: one over the primary key, which the index answers
     * without touching the rows, then a keyed fetch of the few that were
     * picked. The shuffle happens in PHP, where four items out of thousands
     * costs nothing.
     *
     * @param  \Closure  $constrain  narrows the candidate set
     * @return Collection<int, Product>
     */
    private function randomProducts(\Closure $constrain, int $count, array $with = self::CARD_RELATIONS)
    {
        $ids = $constrain(Product::query()->where('status', 'active'))->pluck('id');

        if ($ids->isEmpty()) {
            return Product::whereRaw('1 = 0')->get();
        }

        return Product::with($with)
            ->whereIn('id', $ids->shuffle()->take($count))
            ->get()
            ->shuffle();   // whereIn returns in key order; shuffle again so the row order is random too
    }
}
