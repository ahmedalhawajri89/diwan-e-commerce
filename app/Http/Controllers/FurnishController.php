<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Support\Fit;
use App\Support\RecentlyViewed;
use App\Support\Schema;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
            ->take(4)
            ->get();

        // "Shop by room": every room with something live in it, each shown
        // with a photograph of one of its own pieces, its real count and its
        // own description. An empty room is not a destination, so it is left
        // out.
        $activeProducts = fn ($q) => $q->where('status', 'active');

        $categoryTiles = Category::withCount(['products' => $activeProducts])
            ->with(['products' => fn ($q) => $activeProducts($q)->with('images')->latest()->limit(1)])
            ->whereHas('products', $activeProducts)
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->take(6)
            ->get();

        // Just in: the newest pieces, leaving out the ones already shown as
        // most saved, so the two rows never repeat each other.
        $newest = Product::with(self::CARD_RELATIONS)
            ->where('status', 'active')
            ->whereNotIn('id', $featuredProducts->modelKeys())
            ->latest()
            ->orderByDesc('id')
            ->take(8)
            ->get();

        // The fit demo needs a piece with all three measurements on file, and
        // the largest one: a stool fits almost anywhere, so dragging the walls
        // around it would never change the answer.
        $measured = fn ($q) => $q->whereNotNull('width_cm')->whereNotNull('depth_cm')->whereNotNull('height_cm');

        $fitDemo = Product::with(['variants', 'images'])
            ->where('status', 'active')
            ->whereHas('variants', $measured)
            ->orderByDesc(
                ProductVariant::selectRaw('max(width_cm * depth_cm)')
                    ->whereColumn('product_id', 'products.id')
            )
            ->first();

        // Proof reads from the real table. No approved review means the
        // section does not render — it does not get a plausible-sounding fake.
        $quotes = Review::with(['user', 'product'])
            ->where('status', 'approved')
            ->whereNotNull('comment')
            ->latest()
            ->orderByDesc('id')
            ->take(3)
            ->get();

        $schema = Schema::store();

        return view('furnish.index', compact('featuredProducts', 'categoryTiles', 'newest', 'fitDemo', 'quotes', 'schema'));
    }

    public function show(Request $request, $slug)
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

        // The colour the shopper chose, by ?variant=. Each colour is a plain
        // link, so choosing one works without JavaScript and can be shared.
        // An id that is not this piece's falls back to the default colour.
        $variant = $product->variants->firstWhere('id', (int) $request->query('variant'))
            ?? $product->primaryVariant();

        // The same room first, newest first, topped up from other rooms when
        // this one is small. Deterministic: the row was shuffled on every
        // load, so a shopper who came back for "that chair under the sofa"
        // found a different chair there.
        $related = Product::with(self::CARD_RELATIONS)
            ->where('status', 'active')
            ->whereKeyNot($product->id)
            ->orderByRaw('category_id = ? desc', [$product->category_id])
            ->latest()
            ->take(4)
            ->get();

        // What this visitor looked at before: not this piece, and not anything
        // already in the related row.
        $recent = RecentlyViewed::products([$product->id, ...$related->modelKeys()]);
        RecentlyViewed::push($product->id);

        // Structured data. This is where the fit system reaches a search
        // engine: real width, depth and height as machine-readable values,
        // which is a rich result almost no furniture site can produce.
        // One trail, drawn on the page and published as BreadcrumbList, so
        // what a visitor sees and what a search engine reads cannot disagree.
        $trail = ([
            __('diwan.nav.home') => route('furnish.index'),
            __('diwan.nav.catalogue') => route('furnish.products'),
        ] + ($product->category ? [$product->category->name => route('furnish.category', $product->category->slug)] : [])
          + [$product->name => route('furnish.product.show', $product->slug)]);

        $schema = [
            Schema::product($product),
            Schema::breadcrumbs($trail),
        ];

        return view('furnish.product_details', compact('product', 'variant', 'related', 'recent', 'schema', 'trail'));
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
                // Both languages, whichever the visitor is reading: an Arabic
                // reader may well type the English name they saw on a label.
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('name_ar', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($sub) => $sub->where('name', 'like', "%{$term}%")->orWhere('name_ar', 'like', "%{$term}%"))
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('description_ar', 'like', "%{$term}%");
            });
        }

        if ($request->filled('categories')) {
            $query->whereIn('category_id', (array) $request->categories);
        }

        if ($request->filled('prices')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    foreach ((array) $request->prices as $range) {
                        // Bands that fit what a furniture shop charges. The old
                        // three (under 100 to over 300) put nearly every piece
                        // in the last one. Their values still work, so a
                        // saved link still filters.
                        match ($range) {
                            'under_500' => $sub->orWhere('price', '<', 500),
                            '500_1500' => $sub->orWhereBetween('price', [500, 1500]),
                            'above_1500' => $sub->orWhere('price', '>', 1500),
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

        // A piece's price is its cheapest colour's, the one its card shows.
        $price = ProductVariant::selectRaw('min(price)')->whereColumn('product_variants.product_id', 'products.id');

        match ($request->input('sort')) {
            'price_low' => $query->orderBy($price),
            'price_high' => $query->orderByDesc($price),
            'alphabetical_az' => $query->orderBy('name'),
            'alphabetical_za' => $query->orderByDesc('name'),
            'date_old' => $query->oldest(),
            default => $query->latest(),
        };
        $query->orderBy('id');   // a stable order between equal prices and pages

        $room = RoomController::current();
        $fitOnly = $request->boolean('fit_only') && $room;

        if ($fitOnly) {
            // The fit filter compares against the visitor's room, which lives
            // in their session, so SQL cannot express it. It used to run on
            // the twelve rows of one page AFTER paginating: page one could say
            // "nothing matches" while page two held pieces that fit, and the
            // pager — inside the non-empty branch — was hidden with it. Now
            // every matching piece is assessed first and the fitting ones are
            // paged. The catalogue is small enough for this to be cheap.
            $fitting = $query->get()
                ->filter(fn ($product) => Fit::assess($product->primaryVariant(), $room)['status'] === Fit::FITS)
                ->values();

            $page = LengthAwarePaginator::resolveCurrentPage();
            $products = new LengthAwarePaginator(
                $fitting->forPage($page, 12)->values(),
                $fitting->count(),
                12,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $products = $query->paginate(12)->withQueryString();
        }

        // Why the grid is empty, when it is: one sentence that names the cause
        // rather than "Nothing matches those filters" for every case.
        $empty = match (true) {
            $products->isNotEmpty() => null,
            $products->total() > 0 => 'past_end',
            $fitOnly => 'fit',
            $request->filled('q') => 'query',
            default => 'filters',
        };

        return view('furnish.products', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            // value => the name the visitor reads, in their language.
            'availableColors' => ProductVariant::whereNotNull('color')->distinct()->orderBy('color')->pluck('color')
                ->mapWithKeys(fn ($colour) => [$colour => ProductVariant::labelFor($colour)]),
            'fitOnly' => $fitOnly,
            'empty' => $empty,
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
        // Each room is listed with pieces from its own category. The
        // photographs set the scene; they are not of these pieces, so nothing
        // is pinned onto them — a marker on a sofa that opens a stool would
        // be the same lie as an invented dimension.
        $scenes = [
            ['title' => 'lookbook.scene_living', 'image' => 'assets/images/hero-living-room.jpg', 'category' => 'living-room'],
            ['title' => 'lookbook.scene_work',   'image' => 'assets/images/product-desk-2.jpg',  'category' => 'office'],
            ['title' => 'lookbook.scene_dining', 'image' => 'assets/images/product-table-2.jpg', 'category' => 'dining-room'],
        ];

        $categories = Category::whereIn('slug', array_column($scenes, 'category'))->get()->keyBy('slug');

        $looks = [];

        foreach ($scenes as $scene) {
            $category = $categories->get($scene['category']);

            $pieces = $category
                ? Product::with(['images', 'variants'])
                    ->where('status', 'active')
                    ->where('category_id', $category->id)
                    ->latest()
                    ->take(4)
                    ->get()
                : collect();

            // A room with nothing to buy in it teaches the visitor nothing.
            if ($pieces->isEmpty()) {
                continue;
            }

            $looks[] = [
                'title' => $scene['title'],
                'image' => asset($scene['image']),
                'category' => $category,
                'pieces' => $pieces,
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
                    ->orWhere('name_ar', 'like', "%{$query}%")
                    ->orWhereHas('category', fn ($sub) => $sub->where('name', 'like', "%{$query}%")->orWhere('name_ar', 'like', "%{$query}%"));
            })
            // One more than the five shown: the sixth says there is more.
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
}
