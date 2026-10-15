<?php

use App\Http\Controllers\RoomController;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;

/**
 * A signed-in customer's room and way in are kept with their account, so
 * a living room measured on a laptop is there on the phone.
 */
$route = ['access' => 'stairs', 'door_w' => '90', 'door_h' => '205', 'stairs_w' => '100', 'stairs_landing' => '110'];

it('keeps what a signed-in customer measures with their account', function () use ($route) {
    $customer = User::factory()->create();

    $this->actingAs($customer)->post(route('room.store'), ['width' => '350', 'length' => '480']);
    $this->actingAs($customer)->post(route('room.route.store'), $route);

    $customer->refresh();
    expect($customer->room)->toBe(['width' => 350, 'length' => 480])
        ->and($customer->way_in['stairs'])->toBe(['w' => 100, 'landing' => 110]);

    $this->actingAs($customer)->delete(route('room.route.destroy'));
    expect($customer->fresh()->way_in)->toBeNull();
});

it('brings the account\'s measurements to a new device on sign-in', function () {
    $customer = User::factory()->create(['password' => 'secret-pass-1']);
    $customer->forceFill(['room' => ['width' => 300, 'length' => 400], 'way_in' => ['access' => 'ground', 'door' => ['w' => 85, 'h' => 200], 'lift' => null, 'stairs' => null, 'balcony' => null]])->save();

    // A fresh session: nothing measured on this device.
    $this->post(route('login'), ['email' => $customer->email, 'password' => 'secret-pass-1'])->assertRedirect();

    expect(session(RoomController::SESSION_KEY))->toBe(['width' => 300, 'length' => 400])
        ->and(session(RoomController::ROUTE_KEY)['door'])->toBe(['w' => 85, 'h' => 200]);
});

it('keeps what was measured before signing in, when the account had nothing', function () {
    $customer = User::factory()->create(['password' => 'secret-pass-1']);

    $this->post(route('room.store'), ['width' => '420', 'length' => '510']);
    $this->post(route('login'), ['email' => $customer->email, 'password' => 'secret-pass-1']);

    expect($customer->fresh()->room)->toBe(['width' => 420, 'length' => 510]);
});

it('leaves a visitor without an account measuring in the session only', function () {
    $this->post(route('room.store'), ['width' => '350', 'length' => '480'])->assertRedirect();

    expect(session(RoomController::SESSION_KEY))->toBe(['width' => 350, 'length' => 480]);
});

it('does not log an administrator measuring their own room as a change to an account', function () {
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::firstOrCreate(['name' => 'admin']));

    $this->actingAs($admin)->post(route('room.store'), ['width' => '350', 'length' => '480']);

    expect(ActivityLog::where('entity_type', User::class)->where('action', 'updated')->count())->toBe(0);
});
