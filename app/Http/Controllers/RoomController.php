<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * "My room" — the two numbers the whole fit system is computed against.
 *
 * Deliberately session-backed rather than account-backed: a visitor measuring
 * their living room should not have to register first, and the measurement is
 * not worth a database row until they buy something.
 */
class RoomController extends Controller
{
    public const SESSION_KEY = 'diwan.room';

    /** Bounds chosen so a typo cannot produce an absurd fit verdict. */
    private const MIN_CM = 100;

    private const MAX_CM = 2000;

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'width' => ['required', 'integer', 'min:'.self::MIN_CM, 'max:'.self::MAX_CM],
            'length' => ['required', 'integer', 'min:'.self::MIN_CM, 'max:'.self::MAX_CM],
        ], [], [
            'width' => __('diwan.room.width'),
            'length' => __('diwan.room.length'),
        ]);

        $request->session()->put(self::SESSION_KEY, [
            'width' => (int) $validated['width'],
            'length' => (int) $validated['length'],
        ]);

        // back() falls back to the storefront when there is no referer — never
        // to a URL supplied by the request.
        return back(fallback: route('furnish.index'))
            ->with('success', __('diwan.room.saved'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return back(fallback: route('furnish.index'))
            ->with('success', __('diwan.room.cleared'));
    }

    /** @return array{width:int,length:int}|null */
    public static function current(): ?array
    {
        $room = session(self::SESSION_KEY);

        return (is_array($room) && ! empty($room['width']) && ! empty($room['length']))
            ? $room
            : null;
    }
}
