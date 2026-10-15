<?php

namespace App\Support;

use App\Http\Controllers\RoomController;
use App\Models\User;

/**
 * The visitor's room and way in, kept with their account once they have one,
 * so measuring a living room on a laptop is not lost on the phone.
 *
 * The session stays the working copy (the fit and passage checks read it,
 * and a visitor without an account measures there). This only copies it to
 * the account when it changes, and back from the account on sign-in.
 */
final class Measurements
{
    /** The session's room and way in, written to the signed-in account. */
    public static function keep(?User $user): void
    {
        if (! $user) {
            return;
        }

        $user->forceFill([
            'room' => RoomController::current(),
            'way_in' => RoomController::route(),
        ])->save();
    }

    /**
     * On sign-in: what the account knows fills a session that knows nothing,
     * and what the session measured fills an account that knew nothing. When
     * both have one, the account's stands; it is the deliberate, saved one.
     */
    public static function adopt(User $user): void
    {
        $session = session();

        if ($user->room) {
            $session->put(RoomController::SESSION_KEY, $user->room);
        }
        if ($user->way_in) {
            $session->put(RoomController::ROUTE_KEY, $user->way_in);
        }

        if (! $user->room && RoomController::current() || ! $user->way_in && RoomController::route()) {
            self::keep($user);
        }
    }
}
