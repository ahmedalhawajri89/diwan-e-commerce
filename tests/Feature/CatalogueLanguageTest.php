<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;

/**
 * The interface was bilingual and the catalogue was not: an Arabic page read
 * "Cozy Stool" under an Arabic heading. A piece now carries its Arabic name
 * and description, shown on the Arabic site, with the English as the fallback.
 */
function bilingualPiece(array $overrides = []): Product
{
    $category = Category::create([
        'name' => 'Living Room', 'name_ar' => 'غرفة المعيشة', 'slug' => 'living-room',
    ]);

    $product = Product::factory()->create(array_merge([
        'category_id' => $category->id,
        'name' => 'Cozy Sofa',
        'name_ar' => 'كنبة مريحة',
        'description' => 'A sofa for the main wall.',
        'description_ar' => 'كنبة للجدار الرئيسي.',
    ], $overrides));

    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 1499]);

    return $product;
}

it('names a piece in Arabic on the Arabic site', function () {
    $product = bilingualPiece();

    $this->get(route('furnish.product.show', ['locale' => 'ar', 'slug' => $product->slug]))
        ->assertSee('كنبة مريحة')
        ->assertSee('كنبة للجدار الرئيسي.')
        ->assertSee('غرفة المعيشة');

    $this->get(route('furnish.product.show', ['locale' => 'en', 'slug' => $product->slug]))
        ->assertSee('Cozy Sofa')
        ->assertDontSee('كنبة مريحة');
});

it('falls back to the English name rather than an empty line', function () {
    $product = bilingualPiece(['name_ar' => null]);

    $this->withSession(['locale' => 'ar'])
        ->get(route('furnish.product.show', $product->slug))
        ->assertSee('Cozy Sofa');
});

it('finds a piece by its Arabic name', function () {
    bilingualPiece();

    $this->getJson(route('api.search', ['q' => 'مريحة']))
        ->assertOk()
        ->assertJsonFragment(['slug' => Product::first()->slug]);
});

it('keeps the English name in the English field when the panel is in Arabic', function () {
    // Through the locale-aware accessor, an Arabic-speaking operator would see
    // the Arabic name in the English field, and saving would overwrite it.
    $product = bilingualPiece();
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    $html = $this->actingAs($admin)
        ->withSession(['locale' => 'ar'])
        ->get(route('admin.products.edit', $product))
        ->getContent();

    preg_match('/<input[^>]*name="name"[^>]*>/', $html, $name);
    preg_match('/<input[^>]*name="name_ar"[^>]*>/', $html, $nameAr);

    expect($name[0])->toContain('value="Cozy Sofa"')
        ->and($nameAr[0])->toContain('value="كنبة مريحة"');
});
