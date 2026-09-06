<?php

use App\Models\Role;
use App\Models\User;

/**
 * The admin panel's front door. Every route under /admin must be closed to a
 * signed-in customer, not merely absent from their navigation.
 */
function customer(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'customer']);
    $user->roles()->attach($role);

    return $user;
}

function operator(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin']);
    $user->roles()->attach($role);

    return $user;
}

it('keeps a signed-in customer out of every admin page', function () {
    $paths = [
        '/admin/dashboard', '/admin/products', '/admin/categories',
        '/admin/orders', '/admin/users', '/admin/reviews',
        '/admin/contacts', '/admin/newsletters',
    ];

    foreach ($paths as $path) {
        $this->actingAs(customer())->get($path)
            ->assertRedirect(route('furnish.index'));
    }
});

it('no longer exposes the contact messages outside the admin prefix', function () {
    // These routes carried admin.* names but lived in web.php under the plain
    // `auth` group, so any signed-in customer could read every message sent
    // through the contact form, and delete them.
    $this->actingAs(customer())->get('/contacts')->assertNotFound();

    expect(route('admin.contacts.index', absolute: false))->toBe('/admin/contacts');
});

it('lets an operator through', function () {
    $this->actingAs(operator())->get('/admin/dashboard')->assertOk();
    $this->actingAs(operator())->get('/admin/contacts')->assertOk();
});

it('turns a guest away from the panel', function () {
    $this->get('/admin/dashboard')->assertRedirect(route('login'));
});
