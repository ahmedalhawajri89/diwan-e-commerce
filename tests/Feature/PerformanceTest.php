<?php

use App\Http\Controllers\FurnishController;
use App\Jobs\DeriveImage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Support\RecentlyViewed;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

/**
 * What keeps the shop quick: pages sent compressed, the home page's shelves
 * read once, uploads answered before their photographs are encoded, and a
 * page fetched ahead of a hover not mistaken for a visit.
 */
it('sends a page gzipped to a browser that asks, and plain to one that does not', function () {
    $plain = $this->get(route('furnish.index'));
    $packed = $this->withHeader('Accept-Encoding', 'gzip, br')->get(route('furnish.index'));

    expect($plain->headers->get('Content-Encoding'))->toBeNull()
        ->and($packed->headers->get('Content-Encoding'))->toBe('gzip')
        ->and($packed->headers->get('Vary'))->toContain('Accept-Encoding')
        // Each request mints its own CSP nonce, so compare the page, not the bytes.
        ->and(gzdecode($packed->getContent()))->toStartWith('<!doctype html>')->toContain('</html>');
});

it('reads the home page shelves once, and again after the catalogue changes', function () {
    $category = Category::factory()->create();
    $first = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'name' => 'First piece']);
    ProductVariant::factory()->create(['product_id' => $first->id]);

    $this->get(route('furnish.index'))->assertSee('First piece');
    expect(Cache::has(FurnishController::SHELVES_KEY))->toBeTrue();

    // An edit to the catalogue clears them, so a new piece shows at once.
    $second = Product::factory()->create(['category_id' => $category->id, 'status' => 'active', 'name' => 'Second piece']);
    ProductVariant::factory()->create(['product_id' => $second->id]);

    expect(Cache::has(FurnishController::SHELVES_KEY))->toBeFalse();
    $this->get(route('furnish.index'))->assertSee('Second piece');
});

it('answers a product save at once and encodes its photographs on the queue', function () {
    Queue::fake();
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));
    $category = Category::factory()->create();

    $this->actingAs($admin)->post(route('admin.products.store'), [
        'name' => 'Queued chair',
        'slug' => 'queued-chair',
        'description' => 'A chair whose photograph is encoded later.',
        'category_id' => $category->id,
        'status' => 'active',
        'price' => 120,
        'stock' => 4,
        'images' => [UploadedFile::fake()->image('chair.jpg', 1200, 1500)],
    ])->assertRedirect(route('admin.products.index'));

    $stored = Product::where('name', 'Queued chair')->first()->images->first()->image_url;

    Queue::assertPushed(DeriveImage::class, fn ($job) => $job->path === $stored);
    Storage::disk('public')->assertExists($stored);
});

it('does not count a page fetched ahead of a hover as a piece looked at', function () {
    $product = Product::factory()->create(['category_id' => Category::factory()->create()->id, 'status' => 'active']);
    ProductVariant::factory()->create(['product_id' => $product->id]);

    $this->withHeader('Sec-Purpose', 'prefetch')->get(route('furnish.product.show', $product->slug))->assertOk();
    expect(session(RecentlyViewed::SESSION_KEY, []))->toBe([]);

    $this->withHeader('Sec-Purpose', '')->get(route('furnish.product.show', $product->slug));
    expect(session(RecentlyViewed::SESSION_KEY))->toContain($product->id);
});

it('fetches ahead only pages that change nothing', function () {
    $html = $this->get(route('furnish.index'))->getContent();

    preg_match('/<script type="speculationrules"[^>]*>(.*?)<\/script>/s', $html, $m);
    $rules = json_decode($m[1] ?? '', true);

    expect($rules)->not->toBeNull();
    $excluded = collect($rules['prefetch'][0]['where']['and'])->pluck('not.href_matches')->filter()->all();
    expect($excluded)->toContain('/locale/*', '/logout', '/checkout*', '/cart*');
});
