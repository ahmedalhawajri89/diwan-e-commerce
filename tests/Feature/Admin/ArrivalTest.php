<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;

/**
 * How a piece arrives: said in the product form, kept honest (no leftover
 * carried size, no part bigger than the piece), and counted in how whole a
 * piece's page is.
 */
function arrivalAdmin(): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

function arrivalForm(array $more = []): array
{
    return $more + [
        'name' => 'Linen sofa', 'slug' => 'linen-sofa', 'description' => 'A sofa.',
        'status' => 'active', 'category_id' => Category::factory()->create()->id,
        'price' => 900, 'stock' => 3,
        'width_cm' => 220, 'depth_cm' => 95, 'height_cm' => 85,
    ];
}

it('keeps the largest part of a piece that comes apart', function () {
    $this->actingAs(arrivalAdmin())
        ->post(route('admin.products.store'), arrivalForm(['arrives' => 'parts', 'carry_w_cm' => 210, 'carry_d_cm' => 80, 'carry_h_cm' => 60]))
        ->assertRedirect(route('admin.products.index'));

    expect(ProductVariant::sole()->only(['arrives', 'carry_w_cm', 'carry_d_cm', 'carry_h_cm']))
        ->toBe(['arrives' => 'parts', 'carry_w_cm' => 210, 'carry_d_cm' => 80, 'carry_h_cm' => 60]);
});

it('forgets a carried size once the piece is said to arrive whole, or not said', function () {
    $admin = arrivalAdmin();
    $this->actingAs($admin)->post(route('admin.products.store'), arrivalForm(['arrives' => 'flat', 'carry_w_cm' => 200, 'carry_d_cm' => 40, 'carry_h_cm' => 15]));
    $product = Product::sole();

    $this->actingAs($admin)->put(route('admin.products.update', $product), arrivalForm([
        'category_id' => $product->category_id, 'arrives' => 'assembled', 'carry_w_cm' => 200, 'carry_d_cm' => 40, 'carry_h_cm' => 15,
    ]))->assertRedirect();

    expect(ProductVariant::sole()->only(['arrives', 'carry_w_cm']))->toBe(['arrives' => 'assembled', 'carry_w_cm' => null]);

    $this->actingAs($admin)->put(route('admin.products.update', $product), arrivalForm(['category_id' => $product->category_id, 'arrives' => '']));
    expect(ProductVariant::sole()->arrives)->toBeNull();
});

it('refuses a part bigger than the piece, and a way of arriving it does not know', function () {
    $admin = arrivalAdmin();

    $this->actingAs($admin)
        ->post(route('admin.products.store'), arrivalForm(['arrives' => 'parts', 'carry_w_cm' => 230, 'carry_d_cm' => 80, 'carry_h_cm' => 60]))
        ->assertSessionHasErrors(['carry_w_cm' => __('admin.products.carry_too_big')]);

    $this->actingAs($admin)
        ->post(route('admin.products.store'), arrivalForm(['arrives' => 'by-drone']))
        ->assertSessionHasErrors('arrives');

    expect(Product::count())->toBe(0);
});

it('shows the choice in the form, with the carried size beside it', function () {
    $admin = arrivalAdmin();
    $this->actingAs($admin)->post(route('admin.products.store'), arrivalForm(['arrives' => 'flat', 'carry_w_cm' => 200, 'carry_d_cm' => 40, 'carry_h_cm' => 15]));

    $this->actingAs($admin)->get(route('admin.products.edit', Product::sole()))
        ->assertOk()
        ->assertSee(__('admin.products.arrival.flat'))
        ->assertSee('name="arrives" value="flat" checked', false)
        ->assertSee('name="carry_d_cm"', false);
});

it('counts how a piece arrives in how whole its page is', function () {
    $variant = ProductVariant::factory()->create([
        'product_id' => Product::factory()->create(['category_id' => Category::factory()->create()->id])->id,
        'width_cm' => 200, 'depth_cm' => 90, 'height_cm' => 80, 'arrives' => 'parts',
    ]);
    $product = $variant->product->load(['images', 'variants']);

    // Comes apart, but the size of its largest part is not on file yet.
    expect($product->completeness()['arrives'])->toBeFalse();

    $variant->update(['carry_w_cm' => 190, 'carry_d_cm' => 80, 'carry_h_cm' => 40]);
    expect($product->fresh()->load(['images', 'variants'])->completeness()['arrives'])->toBeTrue();
});
