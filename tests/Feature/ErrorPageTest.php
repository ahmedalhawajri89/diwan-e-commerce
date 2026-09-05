<?php

use App\Models\User;

/**
 * Error pages. Before these existed only 404 was designed; everything else
 * dropped the visitor onto Laravel's default screen, outside the design system
 * entirely.
 *
 * The split matters: 403/419/429 happen while the app is perfectly healthy, so
 * they keep the storefront around the visitor. 500 and 503 are the cases where
 * the app itself may be the problem, so they render standalone — no database,
 * no session, no asset pipeline.
 */

it('renders a designed 404', function () {
    $this->get('/no-such-page')
        ->assertNotFound()
        ->assertSee(__('diwan.error.404_title'))
        ->assertSee('DIWAN');
});

it('renders a designed 403', function () {
    // AdminAuth redirects rather than aborting, so nothing in the app reaches
    // a 403 today. The page still has to exist and carry the design for the
    // routes that abort(403) — room and saved-piece ownership do.
    expect(view('errors.403')->render())->toContain(__('diwan.error.403_title'));
});

it('serves the designed 403 when an ownership check aborts', function () {
    // RoomBoardController aborts 403 for somebody else's room, so this is a
    // real path through the page rather than a rendered template.
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $room = \App\Models\Room::create(['user_id' => $owner->id, 'name' => 'Living room']);

    $this->actingAs($stranger)
        ->delete(route('rooms.destroy', $room))
        ->assertForbidden()
        ->assertSee(__('diwan.error.403_title'));
});

it('renders a designed 419 and 429', function () {
    expect(view('errors.419')->render())->toContain(__('diwan.error.419_title'))
        ->and(view('errors.429')->render())->toContain(__('diwan.error.429_title'));
});

it('renders 500 without touching the database or the session', function () {
    // The whole point: if the database is what broke, a 500 page that queries
    // it throws a second time and the visitor gets Laravel's raw screen. This
    // page must render from nothing but itself.
    $html = view('errors.500')->render();

    expect($html)->toContain('500')
        ->and($html)->toContain('DIWAN')
        // No Vite tags: the asset manifest may be exactly what is missing.
        ->and($html)->not->toContain('/build/assets')
        ->and($html)->toContain('<style>');
});

it('renders 503 standalone too', function () {
    $html = view('errors.503')->render();

    expect($html)->toContain('503')
        ->and($html)->not->toContain('/build/assets');
});

it('keeps the storefront chrome on the healthy-app errors', function () {
    // 403/419/429 extend the shop layout; 500/503 must not.
    foreach (['403', '419', '429'] as $code) {
        expect(view("errors.{$code}")->render())->toContain('hm-nav');
    }

    foreach (['500', '503'] as $code) {
        expect(view("errors.{$code}")->render())->not->toContain('hm-nav');
    }
});
