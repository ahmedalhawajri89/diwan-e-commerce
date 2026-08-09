<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class FurnishController extends Controller
{
    public function index()
    {
        // Fetch 6 top wishlisted products for "Trending Now"
        $featuredProducts = Product::with(['images', 'category', 'variants'])
            ->where('status', 'active')
            ->withCount('wishlistItems')
            ->orderByDesc('wishlist_items_count')
            ->orderByDesc('created_at') // fallback sorting
            ->take(6)
            ->get();

        return view('furnish.index', compact('featuredProducts'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->where('status', 'active')->firstOrFail();
        return view('furnish.product_details', compact('product'));
    }

    public function about()
    {
        return view('furnish.about');
    }

    public function products(Request $request)
    {
        $query = Product::with(['category', 'variants', 'images'])->where('status', 'active');

        // Text Search
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('category', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', "%{$searchTerm}%");
                  })
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by Categories
        if ($request->filled('categories')) {
            $query->whereIn('category_id', $request->categories);
        }

        // Filter by Price Ranges
        if ($request->filled('prices')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where(function ($subQ) use ($request) {
                    foreach ($request->prices as $priceRange) {
                        if ($priceRange == 'under_100') {
                            $subQ->orWhere('price', '<', 100);
                        } elseif ($priceRange == '100_300') {
                            $subQ->orWhereBetween('price', [100, 300]);
                        } elseif ($priceRange == 'above_300') {
                            $subQ->orWhere('price', '>', 300);
                        }
                    }
                });
            });
        }

        // Filter by Colors
        if ($request->filled('colors')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->whereIn('color', $request->colors);
            });
        }

        // Sorting
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'alphabetical_az':
                    $query->orderBy('name', 'asc');
                    break;
                case 'alphabetical_za':
                    $query->orderBy('name', 'desc');
                    break;
                case 'date_new':
                    $query->latest();
                    break;
                case 'date_old':
                    $query->oldest();
                    break;
                // Price sorting requires joining variants, which can be tricky with Eloquent if there are multiple variants.
                // For simplicity, we can sort by the latest added as default for 'best_selling' or use collection sorting if needed.
            }
        } else {
            $query->latest(); // Default sort
        }

        $products = $query->paginate(12)->withQueryString();
        
        $categories = Category::all();
        $availableColors = ProductVariant::whereNotNull('color')->distinct()->pluck('color');

        return view('furnish.products', compact('products', 'categories', 'availableColors'));
    }

    public function testimonials()
    {
        return view('furnish.testimonials');
    }

    public function contact()
    {
        return view('furnish.contact');
    }

    public function lookbook()
    {
        $products = Product::where('status', 'active')->with('images')->inRandomOrder()->take(9)->get();
        $looks = [];

        if ($products->count() >= 3) {
            $looks[] = [
                'id' => 1,
                'title' => 'Modern Living Space',
                'description' => 'A cozy and minimalist living room setup perfect for relaxation.',
                'image' => asset('assets/images/couch-with-cushions-glass-table.jpg'),
                'hotspots' => [
                    ['top' => '40', 'left' => '30', 'product' => $products->get(0)],
                    ['top' => '65', 'left' => '70', 'product' => $products->get(1)],
                    ['top' => '50', 'left' => '50', 'product' => $products->get(2)],
                ]
            ];
        }

        if ($products->count() >= 6) {
            $looks[] = [
                'id' => 2,
                'title' => 'Minimalist Workspace',
                'description' => 'Boost your productivity with clean lines and ergonomic design.',
                'image' => asset('assets/images/login-bg.jpg'),
                'hotspots' => [
                    ['top' => '30', 'left' => '45', 'product' => $products->get(3)],
                    ['top' => '70', 'left' => '60', 'product' => $products->get(4)],
                    ['top' => '45', 'left' => '20', 'product' => $products->get(5)],
                ]
            ];
        }

        if ($products->count() >= 9) {
            $looks[] = [
                'id' => 3,
                'title' => 'Elegant Dining',
                'description' => 'Gather around a table that makes every meal special.',
                'image' => asset('assets/images/about.jpg'),
                'hotspots' => [
                    ['top' => '55', 'left' => '50', 'product' => $products->get(6)],
                    ['top' => '60', 'left' => '25', 'product' => $products->get(7)],
                    ['top' => '40', 'left' => '75', 'product' => $products->get(8)],
                ]
            ];
        }

        return view('furnish.lookbook', compact('looks'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $products = Product::where('status', 'active')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhereHas('category', function($subQ) use ($query) {
                      $subQ->where('name', 'like', "%{$query}%");
                  });
            })
            ->with(['images' => function($q) {
                $q->limit(1);
            }])
            ->take(5)
            ->get()
            ->map(function ($product) {
                $image = $product->images->first();
                $imageUrl = asset('assets/images/product-img-1.jpg'); // default
                if ($image) {
                    if (str_starts_with($image->image_url, 'http')) {
                        $imageUrl = $image->image_url;
                    } elseif (str_starts_with($image->image_url, 'assets/')) {
                        $imageUrl = asset($image->image_url);
                    } else {
                        $imageUrl = asset('storage/' . $image->image_url);
                    }
                }
                
                return [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => number_format($product->price ?? 99.99, 2),
                    'image' => $imageUrl,
                    'category' => $product->category ? $product->category->name : 'Uncategorized'
                ];
            });

        return response()->json($products);
    }
}
