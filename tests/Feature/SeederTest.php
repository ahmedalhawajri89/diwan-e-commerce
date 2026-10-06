<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;

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
