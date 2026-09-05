<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Layer 4 of the fit system — saved pieces, grouped into named rooms.
 *
 * The header's "my room" is one measurement in the session, which is right for
 * a visitor who has not registered. Someone furnishing a flat has several rooms
 * and wants to keep them, so these are real rows on the account, and each one
 * states what its pieces add up to against its own floor.
 */
class RoomBoardController extends Controller
{
    /** Bounds matching RoomController, so a typo cannot produce a nonsense total. */
    private const MIN_CM = 100;
    private const MAX_CM = 2000;

    public function index(): View
    {
        $wishlist = Wishlist::firstOrCreate(['user_id' => auth()->id()]);

        // Everything a product card reads, loaded once for the whole page.
        $items = WishlistItem::with(['product.images', 'product.category', 'product.variants'])
            ->where('wishlist_id', $wishlist->id)
            ->latest()
            ->get();

        $rooms = Room::where('user_id', auth()->id())->orderBy('name')->get();

        // Attach each room's own items so Room::totals() has them in memory
        // rather than issuing a query per room.
        $byRoom = $items->groupBy('room_id');
        $rooms->each(fn (Room $room) => $room->setRelation('items', $byRoom->get($room->id, collect())));

        return view('furnish.rooms', [
            'rooms'      => $rooms,
            'unassigned' => $byRoom->get(null, collect()),
            'total'      => $items->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        Room::create([
            'user_id'   => auth()->id(),
            'name'      => $validated['name'],
            'width_cm'  => $validated['width_cm'] ?? null,
            'length_cm' => $validated['length_cm'] ?? null,
        ]);

        return redirect()->route('rooms.index')->with('success', __('diwan.rooms.created'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $this->authoriseOwner($room);

        $room->update($this->validated($request, $room));

        return redirect()->route('rooms.index')->with('success', __('diwan.rooms.updated'));
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authoriseOwner($room);

        // The pieces survive: room_id is nullOnDelete, so they fall back into
        // the saved list rather than disappearing with the room.
        $room->delete();

        return redirect()->route('rooms.index')->with('success', __('diwan.rooms.deleted'));
    }

    /** Move one saved piece into a room, or back out of every room. */
    public function assign(Request $request, WishlistItem $item): RedirectResponse
    {
        abort_unless($item->wishlist?->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
        ]);

        $roomId = $validated['room_id'] ?? null;

        // Never let a piece be filed into somebody else's room.
        if ($roomId !== null) {
            abort_unless(
                Room::where('id', $roomId)->where('user_id', auth()->id())->exists(),
                403
            );
        }

        $item->update(['room_id' => $roomId]);

        return back(fallback: route('rooms.index'));
    }

    /**
     * @return array{name:string, width_cm:?int, length_cm:?int}
     */
    private function validated(Request $request, ?Room $room = null): array
    {
        $unique = 'unique:rooms,name,' . ($room?->id ?? 'NULL') . ',id,user_id,' . auth()->id();

        return $request->validate([
            'name'      => ['required', 'string', 'max:60', $unique],
            // Nullable together: a room can be named before it is measured, and
            // the page says which totals it cannot compute yet.
            'width_cm'  => ['nullable', 'integer', 'min:' . self::MIN_CM, 'max:' . self::MAX_CM, 'required_with:length_cm'],
            'length_cm' => ['nullable', 'integer', 'min:' . self::MIN_CM, 'max:' . self::MAX_CM, 'required_with:width_cm'],
        ], [], [
            'name'      => __('diwan.rooms.name'),
            'width_cm'  => __('diwan.room.width'),
            'length_cm' => __('diwan.room.length'),
        ]);
    }

    private function authoriseOwner(Room $room): void
    {
        abort_unless($room->user_id === auth()->id(), 403);
    }
}
