<?php

use App\Models\Address;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;

/**
 * Regression cover for the three admin faults found in the audit.
 *
 * Each test here failed before its fix, which is the only reason to write it.
 */

function admin(): User
{
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'admin']);
    $user->roles()->attach($role);

    return $user;
}

it('lets an admin move an order into processing', function () {
    // The controller validated `processing`; the column's enum did not allow
    // it. The value passed validation and was then rejected by the database,
    // so the most ordinary transition an order has returned a 500.
    $customer = User::factory()->create();
    $address = Address::create([
        'user_id' => $customer->id,
        'country' => 'Jordan',
        'city' => 'Amman',
        'street' => 'Rainbow Street 12',
    ]);
    $order = Order::create([
        'user_id' => $customer->id,
        'shipping_address_id' => $address->id,
        'total_price' => 499.00,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $this->actingAs(admin())
        ->put(route('admin.orders.update', $order), [
            'status' => 'processing',
            'payment_status' => 'refunded',
        ])
        ->assertRedirect(route('admin.orders.show', $order->id));

    // Both values were outside the old enums. Reading them back proves the
    // column accepted them rather than silently truncating.
    expect($order->fresh()->status)->toBe('processing')
        ->and($order->fresh()->payment_status)->toBe('refunded');
});

it('reports a protected account as undeletable', function () {
    // `is_protected` had no cast, so it arrived from the database as int 1 and
    // `$this->is_protected === true` was always false. Every "protected"
    // account was deletable by anyone who could reach the admin panel.
    $protected = User::factory()->create(['is_protected' => true]);
    $ordinary = User::factory()->create(['is_protected' => false]);

    expect($protected->isProtected())->toBeTrue()
        ->and($protected->canBeDeleted())->toBeFalse()
        ->and($ordinary->isProtected())->toBeFalse()
        ->and($ordinary->canBeDeleted())->toBeTrue();
});

it('refuses to delete the protected system administrator', function () {
    $protected = User::factory()->create(['is_protected' => true]);

    $this->actingAs(admin())
        ->delete(route('admin.users.destroy', $protected))
        ->assertRedirect(route('admin.users.index'));

    expect(User::find($protected->id))->not->toBeNull();
});

it('refuses to let an admin delete their own account', function () {
    $me = admin();

    $this->actingAs($me)
        ->delete(route('admin.users.destroy', $me))
        ->assertRedirect(route('admin.users.index'));

    expect(User::find($me->id))->not->toBeNull();
});

it('still deletes an ordinary account', function () {
    $victim = User::factory()->create(['is_protected' => false]);

    $this->actingAs(admin())
        ->delete(route('admin.users.destroy', $victim))
        ->assertRedirect(route('admin.users.index'));

    expect(User::find($victim->id))->toBeNull();
});

it('does not register admin routes that have no controller method', function () {
    // Route::resource used to register users.show / users.edit / users.update
    // against a controller with neither, plus categories.show and
    // products.show. Every one threw on request.
    // categories.show was a stub returning null and is still absent. The
    // others were narrowed out at the time and have since been built, so they
    // belong in the list below rather than here.
    foreach (['admin.categories.show'] as $name) {
        expect(Route::has($name))->toBeFalse("route {$name} should not exist");
    }

    // The ones the panel actually links to must survive the narrowing.
    foreach (['admin.users.index', 'admin.users.destroy', 'admin.users.show',
              'admin.users.edit', 'admin.users.update', 'admin.products.show',
              'admin.products.edit', 'admin.categories.edit', 'admin.orders.show'] as $name) {
        expect(Route::has($name))->toBeTrue("route {$name} must exist");
    }
});

it('will not let a protected account delete itself from its own profile', function () {
    // The admin panel refused this, but the profile route did not — so the
    // protected system administrator could simply close their own account and
    // leave the install with no guaranteed way back in.
    $protected = User::factory()->create([
        'is_protected' => true,
        'password' => bcrypt('correct-horse'),
    ]);

    $this->actingAs($protected)
        ->delete(route('profile.destroy'), ['password' => 'correct-horse'])
        ->assertRedirect(route('profile.edit'));

    expect(User::find($protected->id))->not->toBeNull()
        ->and(auth()->check())->toBeTrue();
});

it('still lets an ordinary account close itself', function () {
    $user = User::factory()->create([
        'is_protected' => false,
        'password' => bcrypt('correct-horse'),
    ]);

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'correct-horse'])
        ->assertRedirect('/');

    expect(User::find($user->id))->toBeNull();
});
