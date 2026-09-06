<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;

/**
 * Editing a user is the most dangerous form in the panel: it can lock people
 * out of it. Every guard here is about that.
 */
function op(): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    return $user;
}

it('shows a user and lets an operator edit them', function () {
    $target = User::factory()->create(['name' => 'Someone Ordinary']);

    $this->actingAs(op())->get(route('admin.users.show', $target))
        ->assertOk()
        ->assertSee('Someone Ordinary');

    $this->actingAs(op())->get(route('admin.users.edit', $target))->assertOk();
});

it('changes a name, an email and a set of roles', function () {
    $target = User::factory()->create();
    $customer = Role::firstOrCreate(['name' => 'customer']);
    $seller = Role::firstOrCreate(['name' => 'seller']);

    $this->actingAs(op())
        ->put(route('admin.users.update', $target), [
            'name' => 'Renamed',
            'email' => 'renamed@example.test',
            'roles' => [$customer->id, $seller->id],
        ])
        ->assertRedirect(route('admin.users.show', $target));

    $target->refresh()->load('roles');

    expect($target->name)->toBe('Renamed')
        ->and($target->email)->toBe('renamed@example.test')
        ->and($target->roles->pluck('name')->sort()->values()->all())->toBe(['customer', 'seller']);
});

it('refuses to edit a protected account at all', function () {
    // Demoting the protected administrator locks everyone out, so the form is
    // not even served.
    $protected = User::factory()->create(['is_protected' => true]);

    $this->actingAs(op())->get(route('admin.users.edit', $protected))->assertForbidden();
    $this->actingAs(op())->put(route('admin.users.update', $protected), [
        'name' => 'Hijacked', 'email' => 'x@example.test', 'roles' => [],
    ])->assertForbidden();

    expect($protected->fresh()->name)->not->toBe('Hijacked');
});

it('will not let an operator remove their own admin role', function () {
    // They would be locked out of the panel one redirect later, with no way to
    // undo it.
    $me = op();
    $customer = Role::firstOrCreate(['name' => 'customer']);

    $this->actingAs($me)
        ->put(route('admin.users.update', $me), [
            'name' => $me->name,
            'email' => $me->email,
            'roles' => [$customer->id],
        ])
        ->assertSessionHasErrors('roles');

    expect($me->fresh()->roles->pluck('name'))->toContain('admin');
});

it('lets an operator edit their own name while keeping admin', function () {
    $me = op();
    $adminRole = Role::where('name', 'admin')->first();

    $this->actingAs($me)
        ->put(route('admin.users.update', $me), [
            'name' => 'Still An Admin',
            'email' => $me->email,
            'roles' => [$adminRole->id],
        ])
        ->assertRedirect(route('admin.users.show', $me));

    expect($me->fresh()->name)->toBe('Still An Admin');
});

it('refuses a duplicate email', function () {
    $taken = User::factory()->create(['email' => 'taken@example.test']);
    $target = User::factory()->create();

    $this->actingAs(op())
        ->put(route('admin.users.update', $target), [
            'name' => $target->name, 'email' => 'taken@example.test', 'roles' => [],
        ])
        ->assertSessionHasErrors('email');
});

it('keeps a customer out of the user pages', function () {
    $customer = User::factory()->create();
    $customer->roles()->attach(Role::firstOrCreate(['name' => 'customer']));
    $target = User::factory()->create();

    $this->actingAs($customer)->get(route('admin.users.show', $target))->assertRedirect(route('furnish.index'));
    $this->actingAs($customer)->get(route('admin.users.edit', $target))->assertRedirect(route('furnish.index'));
});

it('shows a product in the panel', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Oak bench']);
    ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 300]);

    $this->actingAs(op())->get(route('admin.products.show', $product))
        ->assertOk()
        ->assertSee('Oak bench');
});
