<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoReviewSeeder;
use Database\Seeders\StoreSeeder;

/**
 * `db:seed` used to be safe exactly once. A second run appended another five
 * categories and thirty products, a copy of every role, and failed on the
 * demo accounts' unique email. A seeder a developer is afraid to re-run is a
 * seeder that stops being run.
 */
it('leaves an existing database as it found it when seeded again', function () {
    $this->seed(DatabaseSeeder::class);

    $before = [
        'roles' => Role::count(),
        'users' => User::count(),
        'categories' => Category::count(),
        'products' => Product::count(),
    ];

    $this->seed(DatabaseSeeder::class);

    expect([
        'roles' => Role::count(),
        'users' => User::count(),
        'categories' => Category::count(),
        'products' => Product::count(),
    ])->toBe($before);
});

it('creates no account with a published password outside a developer machine', function () {
    app()->detectEnvironment(fn () => 'production');

    // Run directly: `db:seed` itself stops to ask for confirmation in production.
    app(AdminUserSeeder::class)->run();

    expect(User::whereIn('email', ['admin@fu.local', 'customer@fu.local'])->exists())->toBeFalse();
});

it('writes no invented review outside a developer machine', function () {
    app()->detectEnvironment(fn () => 'production');

    app(DemoReviewSeeder::class)->run();

    expect(Review::count())->toBe(0);
});

it('builds a demo catalogue with real words in both languages', function () {
    $this->seed(DatabaseSeeder::class);

    $product = Product::with('category')->first();

    expect(Product::count())->toBe(30)
        ->and(Category::count())->toBe(6)
        ->and(Product::whereNull('name_ar')->orWhereNull('description_ar')->count())->toBe(0)
        ->and(Product::where('description', 'like', '%dolor%')->count())->toBe(0)
        ->and(Review::where('status', 'approved')->count())->toBeGreaterThan(0);

    // A piece sits in a room it belongs in: no sofa filed under the office.
    $sofas = Product::where('name', 'like', '%Sofa')->with('category')->get();
    expect($sofas->pluck('category.slug')->unique()->values()->all())->toBe(['living-room']);
});

it('measures the demo catalogue when the shop speaks Arabic, as it does by default', function () {
    // The factory reads a piece's kind from its English name. Through the
    // `name` accessor it once read «كنبة عصرية» instead, and a live shop was
    // seeded with not one measured piece: no fit, no way-in check, no demo.
    app()->setLocale('ar');
    $this->seed(StoreSeeder::class);

    $variants = ProductVariant::all();
    $measured = $variants->filter(fn ($v) => $v->hasDimensions())->count();

    // Roughly one piece in seven is left unmeasured on purpose; the rest are not.
    expect($measured)->toBeGreaterThan(intdiv($variants->count(), 2));
});
