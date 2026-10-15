<?php

use App\Http\Controllers\RoomController;

/**
 * The way into the visitor's home: kept in the session like the room, every
 * measurement optional, and only the way that was chosen kept.
 */
it('keeps the way in, with the lift measured and a blank kept as unknown', function () {
    $this->post(route('room.route.store'), [
        'access' => 'lift', 'door_w' => '90', 'door_h' => '205',
        'lift_door_w' => '80', 'lift_door_h' => '200', 'lift_w' => '110', 'lift_d' => '140', 'lift_h' => '',
    ])->assertRedirect()->assertSessionHas('success', __('diwan.route.saved'));

    expect(session(RoomController::ROUTE_KEY))->toBe([
        'access' => 'lift',
        'door' => ['w' => 90, 'h' => 205],
        'lift' => ['door_w' => 80, 'door_h' => 200, 'w' => 110, 'd' => 140, 'h' => null],
        'stairs' => null,
        'balcony' => null,
        'inner' => null,
    ]);
});

it('keeps only the way chosen, and a balcony only when one was given', function () {
    $this->post(route('room.route.store'), [
        'access' => 'stairs', 'stairs_w' => '100', 'stairs_landing' => '110',
        'lift_w' => '110', 'lift_d' => '140',            // left over from choosing the lift first
        'balcony_w' => '120', 'balcony_h' => '210',
    ]);

    expect(session(RoomController::ROUTE_KEY))->toMatchArray([
        'lift' => null,
        'stairs' => ['w' => 100, 'landing' => 110],
        'balcony' => ['w' => 120, 'h' => 210],
    ]);

    $this->post(route('room.route.store'), ['access' => 'ground']);
    expect(session(RoomController::ROUTE_KEY))->toMatchArray(['access' => 'ground', 'lift' => null, 'stairs' => null, 'balcony' => null]);
});

it('refuses a measurement no door could have, and a way it does not know', function () {
    $this->post(route('room.route.store'), ['access' => 'lift', 'door_w' => '9'])->assertSessionHasErrors('door_w');
    $this->post(route('room.route.store'), ['access' => 'rope'])->assertSessionHasErrors('access');

    expect(session(RoomController::ROUTE_KEY))->toBeNull();
});

it('reopens the dialog with what went wrong', function () {
    $this->post(route('room.route.store'), ['access' => 'lift', 'door_w' => '9']);

    $this->get(route('furnish.index'))
        ->assertSee('data-open-on-load', false)
        ->assertSee(__('diwan.route.invalid'));

    // And not on a page whose form went through.
    $this->get(route('furnish.index'))->assertDontSee('data-open-on-load', false);
});

it('forgets the way in on request', function () {
    $this->post(route('room.route.store'), ['access' => 'ground', 'door_w' => '90', 'door_h' => '205']);
    $this->delete(route('room.route.destroy'))->assertSessionHas('success', __('diwan.route.cleared'));

    expect(session(RoomController::ROUTE_KEY))->toBeNull();
});

it('offers the way in from the room dialog, with the saved measurements filled in', function () {
    $this->get(route('furnish.index'))
        ->assertSee('id="routeModal"', false)
        ->assertSee(__('diwan.route.from_room'))
        ->assertDontSee(__('diwan.route.clear'));

    $this->post(route('room.route.store'), ['access' => 'stairs', 'door_w' => '87', 'stairs_w' => '96']);

    $this->get(route('furnish.index'))
        ->assertSee('name="door_w" inputmode="numeric" data-digits dir="ltr" value="87"', false)
        ->assertSee('name="access" value="stairs" checked', false)
        ->assertSee(__('diwan.route.clear'));
});

it('keeps a villa’s inner stairs only when the visitor says there are some', function () {
    $this->post(route('room.route.store'), ['access' => 'ground', 'door_w' => '100', 'door_h' => '220', 'has_inner' => '1', 'inner_w' => '85', 'inner_landing' => '90']);
    expect(session(RoomController::ROUTE_KEY)['inner'])->toBe(['w' => 85, 'landing' => 90]);

    // Measurements left in the form, box unticked: no inner stairs on the way.
    $this->post(route('room.route.store'), ['access' => 'ground', 'inner_w' => '85', 'inner_landing' => '90']);
    expect(session(RoomController::ROUTE_KEY)['inner'])->toBeNull();

    $this->post(route('room.route.store'), ['access' => 'ground', 'has_inner' => '1', 'inner_w' => '12'])->assertSessionHasErrors('inner_w');
});
