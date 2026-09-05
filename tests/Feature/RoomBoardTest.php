<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Room;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;

/**
 * Layer 4 of the fit system: saved pieces grouped into named rooms, each room
 * stating what its contents occupy against its own floor.
 */

function saver(): User
{
    return User::factory()->create();
}

function savedPiece(User $user, int $w = 200, int $d = 100, ?Room $room = null): WishlistItem
{
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'status' => 'active']);
    ProductVariant::factory()->create([
        'product_id' => $product->id,
        'price' => 100,
        'width_cm' => $w ?: null,
        'depth_cm' => $d ?: null,
        'height_cm' => 75,
    ]);

    $wishlist = Wishlist::firstOrCreate(['user_id' => $user->id]);

    return WishlistItem::create([
        'wishlist_id' => $wishlist->id,
        'product_id' => $product->id,
        'room_id' => $room?->id,
    ]);
}

it('shows the saved pieces page', function () {
    $user = saver();
    savedPiece($user);

    $this->actingAs($user)->get(route('rooms.index'))->assertOk();
});

it('turns a guest away', function () {
    $this->get(route('rooms.index'))->assertRedirect(route('login'));
});

it('creates a room with its own dimensions', function () {
    $user = saver();

    $this->actingAs($user)
        ->post(route('rooms.store'), ['name' => 'Living room', 'width_cm' => 350, 'length_cm' => 480])
        ->assertRedirect(route('rooms.index'));

    $room = Room::where('user_id', $user->id)->first();

    expect($room->name)->toBe('Living room')
        ->and($room->isMeasured())->toBeTrue()
        ->and($room->floorArea())->toBe(16.8);
});

it('lets a room be named before it is measured', function () {
    $user = saver();

    $this->actingAs($user)
        ->post(route('rooms.store'), ['name' => 'Study'])
        ->assertRedirect(route('rooms.index'));

    $room = Room::where('user_id', $user->id)->first();

    expect($room->isMeasured())->toBeFalse()
        ->and($room->floorArea())->toBeNull()
        ->and($room->dimensions())->toBeNull();
});

it('refuses half a measurement', function () {
    // One number is not a floor. Asking for the other is better than guessing.
    $user = saver();

    $this->actingAs($user)
        ->post(route('rooms.store'), ['name' => 'Half', 'width_cm' => 350])
        ->assertSessionHasErrors('length_cm');
});

it('refuses two rooms with the same name for one person', function () {
    $user = saver();
    Room::create(['user_id' => $user->id, 'name' => 'Living room']);

    $this->actingAs($user)
        ->post(route('rooms.store'), ['name' => 'Living room'])
        ->assertSessionHasErrors('name');
});

it('lets two people each have a room of the same name', function () {
    $a = saver();
    $b = saver();
    Room::create(['user_id' => $a->id, 'name' => 'Living room']);

    $this->actingAs($b)
        ->post(route('rooms.store'), ['name' => 'Living room'])
        ->assertSessionHasNoErrors();

    expect(Room::where('name', 'Living room')->count())->toBe(2);
});

it('totals what a room holds against its own floor', function () {
    $user = saver();
    $room = Room::create(['user_id' => $user->id, 'name' => 'Living room', 'width_cm' => 350, 'length_cm' => 480]);

    savedPiece($user, 200, 100, $room);   // 2.00 m²
    savedPiece($user, 150, 80, $room);    // 1.20 m²

    $room->load('items.product.variants');
    $totals = $room->totals();

    expect(round($totals['footprint'], 2))->toBe(3.20)
        ->and(round($totals['share'], 3))->toBe(round(3.2 / 16.8, 3))
        ->and($totals['unknown'])->toBe(0);
});

it('counts unmeasured pieces instead of guessing them', function () {
    $user = saver();
    $room = Room::create(['user_id' => $user->id, 'name' => 'Study', 'width_cm' => 300, 'length_cm' => 300]);

    savedPiece($user, 200, 100, $room);
    savedPiece($user, 0, 0, $room);   // no dimensions recorded

    $room->load('items.product.variants');
    $totals = $room->totals();

    expect(round($totals['footprint'], 2))->toBe(2.00)
        ->and($totals['unknown'])->toBe(1);
});

it('gives no share for an unmeasured room', function () {
    $user = saver();
    $room = Room::create(['user_id' => $user->id, 'name' => 'Attic']);
    savedPiece($user, 200, 100, $room);

    $room->load('items.product.variants');

    expect($room->totals()['share'])->toBeNull();
});

it('moves a saved piece into a room and back out', function () {
    $user = saver();
    $room = Room::create(['user_id' => $user->id, 'name' => 'Living room', 'width_cm' => 350, 'length_cm' => 480]);
    $item = savedPiece($user);

    $this->actingAs($user)->put(route('rooms.assign', $item), ['room_id' => $room->id]);
    expect($item->fresh()->room_id)->toBe($room->id);

    $this->actingAs($user)->put(route('rooms.assign', $item), ['room_id' => null]);
    expect($item->fresh()->room_id)->toBeNull();
});

it('will not file a piece into somebody else\'s room', function () {
    $mine = saver();
    $theirs = saver();
    $theirRoom = Room::create(['user_id' => $theirs->id, 'name' => 'Their room']);
    $item = savedPiece($mine);

    $this->actingAs($mine)
        ->put(route('rooms.assign', $item), ['room_id' => $theirRoom->id])
        ->assertForbidden();

    expect($item->fresh()->room_id)->toBeNull();
});

it('will not let one person move another person\'s saved piece', function () {
    $owner = saver();
    $stranger = saver();
    $item = savedPiece($owner);

    $this->actingAs($stranger)
        ->put(route('rooms.assign', $item), ['room_id' => null])
        ->assertForbidden();
});

it('will not let one person edit or delete another person\'s room', function () {
    $owner = saver();
    $stranger = saver();
    $room = Room::create(['user_id' => $owner->id, 'name' => 'Living room']);

    $this->actingAs($stranger)->put(route('rooms.update', $room), ['name' => 'Hijacked'])->assertForbidden();
    $this->actingAs($stranger)->delete(route('rooms.destroy', $room))->assertForbidden();

    expect($room->fresh()->name)->toBe('Living room');
});

it('releases pieces back to the saved list when a room is deleted', function () {
    // The room goes; the pieces stay saved. Deleting a folder should not throw
    // away what was in it.
    $user = saver();
    $room = Room::create(['user_id' => $user->id, 'name' => 'Living room']);
    $item = savedPiece($user, 200, 100, $room);

    $this->actingAs($user)->delete(route('rooms.destroy', $room))->assertRedirect(route('rooms.index'));

    expect(WishlistItem::find($item->id))->not->toBeNull()
        ->and($item->fresh()->room_id)->toBeNull();
});
