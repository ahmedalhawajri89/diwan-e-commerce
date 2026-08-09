<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants'])->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'description' => 'required|string',
            'status' => 'required|string|in:active,inactive,draft',
            'category_id' => 'required|exists:categories,id',
            // Variant fields
            'price' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:50',
            'stock' => 'required|integer|min:0',
            // Multiple images
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        // Create base product
        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'category_id' => $validated['category_id'],
        ]);

        // Create primary variant
        $product->variants()->create([
            'price' => $validated['price'],
            'color' => $validated['color'],
            'stock' => $validated['stock'],
        ]);

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_url' => $path,
                    'is_primary' => $index === 0, // First uploaded is primary
                ]);
            }
        }

        flash()->success('Product created successfully.');
        return redirect()->route('admin.products.index');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $variant = $product->variants()->first(); // Get primary variant
        return view('admin.products.edit', compact('product', 'categories', 'variant'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,' . $product->id,
            'description' => 'required|string',
            'status' => 'required|string|in:active,inactive,draft',
            'category_id' => 'required|exists:categories,id',
            // Variant fields
            'price' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:50',
            'stock' => 'required|integer|min:0',
            // Multiple images
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        // Update base product
        $product->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'category_id' => $validated['category_id'],
        ]);

        // Update primary variant or create one if it doesn't exist
        $variant = $product->variants()->first();
        if ($variant) {
            $variant->update([
                'price' => $validated['price'],
                'color' => $validated['color'],
                'stock' => $validated['stock'],
            ]);
        } else {
            $product->variants()->create([
                'price' => $validated['price'],
                'color' => $validated['color'],
                'stock' => $validated['stock'],
            ]);
        }

        // Handle additional image uploads
        if ($request->hasFile('images')) {
            $hasPrimary = $product->images()->where('is_primary', true)->exists();
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_url' => $path,
                    'is_primary' => !$hasPrimary && $index === 0,
                ]);
                $hasPrimary = true;
            }
        }

        flash()->success('Product updated successfully.');
        return redirect()->route('admin.products.index');
    }

    public function destroy(Product $product)
    {
        // Delete associated images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_url);
        }
        $product->delete();
        flash()->success('Product deleted successfully.');
        return redirect()->route('admin.products.index');
    }

    public function destroyImage($id)
    {
        $image = ProductImage::findOrFail($id);
        
        // Delete file from storage
        if (Storage::disk('public')->exists($image->image_url)) {
            Storage::disk('public')->delete($image->image_url);
        }
        
        $image->delete();

        // If the deleted image was primary, set another one as primary
        if ($image->is_primary) {
            $product = $image->product;
            $newPrimary = $product->images()->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
    }
}
