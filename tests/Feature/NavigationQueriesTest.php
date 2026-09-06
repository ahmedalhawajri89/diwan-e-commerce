<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\View\Composers\StorefrontNavComposer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The navigation used to be assembled by a `@php` block at the top of each
 * layout. These cover what moving it into a composer was supposed to buy:
 * fewer queries per page, a cache that is actually invalidated, and a bag
 * count that is never cached because it is personal.
 */

/** Run something and return the SQL it executed. */
function queriesDuring(Closure $work): array
{
    DB::flushQueryLog();
    DB::enableQueryLog();

    try {
        $work();

        return DB::getRawQueryLog();
    } finally {
        DB::disableQueryLog();
    }
}

function makeCatalogue(): Category
{
    $category = Category::factory()->create(['name' => 'Living room', 'slug' => 'living-room']);
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100]);

    return $category;
}

it('queries the category row once and then serves it from cache', function () {
    makeCatalogue();
    Cache::forget(StorefrontNavComposer::CATEGORIES_KEY);

    $cold = queriesDuring(fn () => $this->get(route('furnish.index'))->assertOk());
    $warm = queriesDuring(fn () => $this->get(route('furnish.index'))->assertOk());

    $countsCategories = fn (array $log) => count(array_filter(
        $log,
        fn ($q) => str_contains($q['raw_query'], 'categories')
            && str_contains($q['raw_query'], 'products_count')
    ));

    expect($countsCategories($cold))->toBe(1, 'the first request should build the category row');
    expect($countsCategories($warm))->toBe(0, 'the second should not touch the table at all');
});

it('drops the cached row when a category changes', function () {
    $category = makeCatalogue();

    $this->get(route('furnish.index'))->assertOk();
    expect(Cache::has(StorefrontNavComposer::CATEGORIES_KEY))->toBeTrue();

    $category->update(['name' => 'Renamed']);

    expect(Cache::has(StorefrontNavComposer::CATEGORIES_KEY))->toBeFalse();
    $this->get(route('furnish.index'))->assertOk()->assertSee('Renamed');
});

it('drops the cached row when a product leaves the catalogue', function () {
    // The case a TTL alone gets wrong: no category row is touched, but the
    // count beside one of them is now a lie.
    $category = makeCatalogue();

    $this->get(route('furnish.index'))->assertOk();
    expect(StorefrontNavComposer::categories()->first()->products_count)->toBe(1);

    Product::where('category_id', $category->id)->first()->update(['status' => 'inactive']);

    expect(Cache::has(StorefrontNavComposer::CATEGORIES_KEY))->toBeFalse();
    expect(StorefrontNavComposer::categories()->first()->products_count)->toBe(0);
});

it('counts the bag without caching it', function () {
    makeCatalogue();
    $product = Product::first();

    [$one, $two] = User::factory()->count(2)->create();

    $variant = $product->variants()->first();

    $cart = Cart::create(['user_id' => $one->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 3,
        'price' => $variant->price,
    ]);

    $this->actingAs($one)->get(route('furnish.index'))->assertOk()->assertSee('>3<', false);

    // A second visitor must not inherit the first one's total. This is why the
    // bag is not part of the cached block.
    $this->actingAs($two)->get(route('furnish.index'))->assertOk()->assertDontSee('>3<', false);
});

it('answers the admin question once per request rather than twice', function () {
    $user = User::factory()->create();
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    $log = queriesDuring(function () use ($user) {
        $fresh = User::find($user->id);
        expect($fresh->isAdmin())->toBeTrue();
        expect($fresh->isAdmin())->toBeTrue();
        expect($fresh->isAdmin())->toBeTrue();
    });

    $roleQueries = count(array_filter($log, fn ($q) => str_contains($q['raw_query'], 'user_roles')));

    expect($roleQueries)->toBe(1, 'isAdmin() is memoised, so three calls are one query');
});

it('reads an already loaded roles relation instead of querying again', function () {
    $user = User::factory()->create();
    $user->roles()->attach(Role::firstOrCreate(['name' => 'Admin']));

    $loaded = User::with('roles')->find($user->id);

    $log = queriesDuring(fn () => expect($loaded->isAdmin())->toBeTrue());

    expect($log)->toBe([], 'a preloaded relation should be answered from memory');
});

it('does not treat an ordinary customer as an admin', function () {
    $user = User::factory()->create();
    $user->roles()->attach(Role::firstOrCreate(['name' => 'customer']));

    expect($user->isAdmin())->toBeFalse();
    expect(User::factory()->create()->isAdmin())->toBeFalse();
});
