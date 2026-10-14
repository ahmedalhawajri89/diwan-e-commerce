<?php

namespace App\Http\Controllers;

use App\Support\Measurements;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * "My room" — the two numbers the whole fit system is computed against.
 *
 * Session-backed, so a visitor measuring their living room need not register
 * first; once signed in, each change is also kept with their account
 * (App\Support\Measurements), so the next device has it.
 */
class RoomController extends Controller
{
    public const SESSION_KEY = 'diwan.room';

    /** Bounds chosen so a typo cannot produce an absurd fit verdict. */
    private const MIN_CM = 100;

    private const MAX_CM = 2000;

    public const ROUTE_KEY = 'diwan.route';

    /** Narrower than this is a typo, not a doorway. */
    private const ROUTE_MIN_CM = 30;

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'width' => ['required', 'integer', 'min:'.self::MIN_CM, 'max:'.self::MAX_CM],
            'length' => ['required', 'integer', 'min:'.self::MIN_CM, 'max:'.self::MAX_CM],
            // Where to go next. A name from a short list, never a URL: the
            // home page's "Show me what fits" goes on to the catalogue.
            'then' => ['nullable', 'in:catalogue'],
        ], [], [
            'width' => __('diwan.room.width'),
            'length' => __('diwan.room.length'),
        ]);

        $request->session()->put(self::SESSION_KEY, [
            'width' => (int) $validated['width'],
            'length' => (int) $validated['length'],
        ]);
        Measurements::keep($request->user());

        if (($validated['then'] ?? null) === 'catalogue') {
            return redirect()->route('furnish.products', ['fit_only' => 1]);
        }

        // back() falls back to the storefront when there is no referer — never
        // to a URL supplied by the request.
        return back(fallback: route('furnish.index'))
            ->with('success', __('diwan.room.saved'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);
        Measurements::keep($request->user());

        return back(fallback: route('furnish.index'))
            ->with('success', __('diwan.room.cleared'));
    }

    /**
     * The way into the visitor's home, for the passage check: the narrowest
     * door, how the piece gets up (on the ground floor, by lift, by stairs),
     * and a balcony if a hoist is an option. Same rule as the room: in the
     * session, no account needed.
     *
     * Every measurement is optional. One left blank is one the visitor does
     * not know, kept as null so the check reports that step as unchecked
     * rather than assuming it.
     */
    public function storeRoute(Request $request): RedirectResponse
    {
        $cm = ['nullable', 'integer', 'min:'.self::ROUTE_MIN_CM, 'max:'.self::MAX_CM];
        $v = $request->validate([
            'access' => ['required', 'in:ground,lift,stairs'],
            'door_w' => $cm, 'door_h' => $cm,
            'lift_door_w' => $cm, 'lift_door_h' => $cm, 'lift_w' => $cm, 'lift_d' => $cm, 'lift_h' => $cm,
            'stairs_w' => $cm, 'stairs_landing' => $cm,
            'balcony_w' => $cm, 'balcony_h' => $cm,
        ]);

        $n = fn (string $key) => isset($v[$key]) ? (int) $v[$key] : null;

        $request->session()->put(self::ROUTE_KEY, [
            'access' => $v['access'],
            'door' => ['w' => $n('door_w'), 'h' => $n('door_h')],
            // Only the way that was chosen: a lift measured earlier must not
            // follow a visitor who has since said they take the stairs.
            'lift' => $v['access'] === 'lift'
                ? ['door_w' => $n('lift_door_w'), 'door_h' => $n('lift_door_h'), 'w' => $n('lift_w'), 'd' => $n('lift_d'), 'h' => $n('lift_h')]
                : null,
            'stairs' => $v['access'] === 'stairs' ? ['w' => $n('stairs_w'), 'landing' => $n('stairs_landing')] : null,
            // On the way only when the visitor gave it: an empty balcony is no balcony.
            'balcony' => ($n('balcony_w') || $n('balcony_h')) ? ['w' => $n('balcony_w'), 'h' => $n('balcony_h')] : null,
        ]);
        Measurements::keep($request->user());

        return back(fallback: route('furnish.index'))->with('success', __('diwan.route.saved'));
    }

    public function destroyRoute(Request $request): RedirectResponse
    {
        $request->session()->forget(self::ROUTE_KEY);
        Measurements::keep($request->user());

        return back(fallback: route('furnish.index'))->with('success', __('diwan.route.cleared'));
    }

    /** The visitor's way in, in the shape Passage::assess() reads, or null. */
    public static function route(): ?array
    {
        $route = session(self::ROUTE_KEY);

        return is_array($route) && isset($route['access']) ? $route : null;
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
