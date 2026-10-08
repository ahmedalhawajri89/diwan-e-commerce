<?php

namespace App\Support;

use App\Models\ProductVariant;

/**
 * Whether a piece can make its way into the home: through the front door,
 * into the lift or round the turn of the stairs. Fit asks whether it suits
 * the room's floor; this asks whether it can get there. Pure arithmetic,
 * like Fit, so it can be tested without a request or a database.
 *
 * Every rounding and every shortcut errs towards "it will not go": telling
 * a customer a sofa passes and watching it stick in the lift is far worse
 * than being cautious. Nothing here is assumed: a measurement the visitor
 * does not know leaves its step unchecked, and the verdict says so.
 *
 * A route, in centimetres:
 *
 *   ['door'   => ['w' => 90, 'h' => 205],
 *    'lift'   => ['door_w' => 80, 'door_h' => 200, 'w' => 110, 'd' => 140, 'h' => 220],
 *    'stairs' => ['w' => 100, 'landing' => 110]]
 *
 * A step that is absent or null is not on the way (no lift, no stairs). A
 * step that is there with a null measurement is one the visitor does not
 * know, and is reported as unchecked.
 */
final class Passage
{
    public const PASSES = 'passes';

    public const TIGHT = 'tight';     // it goes, but inside the margin

    public const NO = 'no';

    public const UNKNOWN = 'unknown';

    /**
     * Room left on each side for hands and the blanket it is wrapped in, in
     * centimetres. A piece that clears an opening by less than twice this
     * goes through, but only with care.
     */
    public const MARGIN_CM = 2;

    /** Most severe first: the worst step decides the verdict. */
    private const SEVERITY = [self::NO => 3, self::TIGHT => 2, self::UNKNOWN => 1, self::PASSES => 0];

    /**
     * The piece's three sides, or null when any is missing.
     *
     * @return array{0:int,1:int,2:int}|null
     */
    public static function dimensions(ProductVariant|array|null $piece): ?array
    {
        $sides = $piece instanceof ProductVariant
            ? [$piece->width_cm, $piece->depth_cm, $piece->height_cm]
            : array_values((array) $piece);

        if (count($sides) !== 3 || in_array(null, $sides, true) || min($sides) <= 0) {
            return null;
        }

        $sides = array_map('intval', $sides);
        sort($sides);

        return $sides;
    }

    /**
     * Through a door or any opening, w × h. The piece goes through end first,
     * so its two smaller sides have to clear the opening, either way round;
     * its length is not limited by the opening itself.
     *
     * @param  array{0:int,1:int,2:int}  $sides  smallest first
     */
    public static function opening(array $sides, int $w, int $h): array
    {
        [$a, $b] = $sides;

        return self::best([
            [[$a, $w], [$b, $h]],   // on its side
            [[$a, $h], [$b, $w]],   // upright
        ]);
    }

    /**
     * Into a lift: through its door, then inside the car. Inside, the
     * piece's length has to lie along the floor, across its diagonal, stand
     * up, or lean from a floor corner to the ceiling. A diagonal is shortened
     * by the piece's thickness, because a sofa is not a line.
     *
     * @param  array{0:int,1:int,2:int}  $sides  smallest first
     * @param  array{door_w:int,door_h:int,w:int,d:int,h:int}  $lift
     */
    public static function lift(array $sides, array $lift): array
    {
        $door = self::opening($sides, $lift['door_w'], $lift['door_h']);
        if ($door['status'] === self::NO) {
            return $door + ['where' => 'lift_door'];
        }

        [$a, $b, $c] = $sides;
        ['w' => $w, 'd' => $d, 'h' => $h] = $lift;
        $narrow = min($w, $d);
        $long = max($w, $d);

        $car = self::best([
            [[$c, $long], [$a, $narrow], [$b, $h]],                 // lying along the car
            [[$c, sqrt($w ** 2 + $d ** 2) - $a], [$b, $h]],          // across the floor's diagonal
            [[$c, $h], [$a, $narrow], [$b, $long]],                 // standing up
            [[$c, sqrt($d ** 2 + $h ** 2) - $a], [$b, $w]],          // leaning along the depth
            [[$c, sqrt($w ** 2 + $h ** 2) - $a], [$b, $d]],          // leaning across the width
        ]);

        // The tighter of the two is the lift's answer.
        return $car['gap'] < $door['gap'] ? $car + ['where' => 'lift_car'] : $door + ['where' => 'lift_door'];
    }

    /**
     * Round a right-angled turn, from a passage w1 wide into one w2 wide: a
     * stair turning at its landing, or a corridor. The longest piece of
     * thickness a that can be carried flat round the corner is the least, over
     * every angle, of
     *
     *   L(θ) = w1 / sin θ + w2 / cos θ − a / (sin θ · cos θ)
     *
     * found by trying the angles in half-degree steps. It is carried flat:
     * tilting it up would give more room, but the headroom is not measured,
     * so it is not counted on.
     *
     * @param  array{0:int,1:int,2:int}  $sides  smallest first
     */
    public static function corner(array $sides, int $w1, int $w2): array
    {
        [$a, , $c] = $sides;

        // As thick as the narrower passage, or thicker: it cannot even start
        // to turn, whatever its length. A gap of nought is still no here.
        if ($a >= min($w1, $w2)) {
            return ['status' => self::NO, 'gap' => min($w1, $w2) - $a, 'need' => $a, 'room' => min($w1, $w2)];
        }

        $longest = INF;
        for ($deg = 1.0; $deg < 90; $deg += 0.5) {
            $t = deg2rad($deg);
            $longest = min($longest, $w1 / sin($t) + $w2 / cos($t) - $a / (sin($t) * cos($t)));
        }

        return self::best([[[$c, $longest], [$a, min($w1, $w2)]]]);
    }

    /**
     * The whole way in: the front door, then the lift if there is one, or
     * else the stairs. The worst step decides; a step with a measurement the
     * visitor does not know is listed as unchecked, and a piece that passes
     * every step we could check, with one left unchecked, is UNKNOWN, never
     * PASSES.
     *
     * @return array{status:string, where:string|null, gap:int|null, need:int|null, room:int|null, steps:array, unchecked:array}
     */
    public static function assess(ProductVariant|array|null $piece, ?array $route): array
    {
        $sides = self::dimensions($piece);

        if (! $sides || ! $route) {
            return ['status' => self::UNKNOWN, 'where' => $sides ? null : 'piece', 'gap' => null,
                'need' => null, 'room' => null, 'steps' => [], 'unchecked' => []];
        }

        $steps = [];
        $unchecked = [];

        $door = $route['door'] ?? null;
        if (self::known($door, ['w', 'h'])) {
            $steps['door'] = self::opening($sides, (int) $door['w'], (int) $door['h']) + ['where' => 'door'];
        } else {
            $unchecked[] = 'door';
        }

        $lift = $route['lift'] ?? null;
        $stairs = $route['stairs'] ?? null;

        if ($lift) {
            if (self::known($lift, ['door_w', 'door_h', 'w', 'd', 'h'])) {
                $steps['lift'] = self::lift($sides, array_map('intval', $lift));
            } else {
                $unchecked[] = 'lift';
            }
        } elseif ($stairs) {
            if (self::known($stairs, ['w', 'landing'])) {
                $steps['stairs'] = self::corner($sides, (int) $stairs['w'], (int) $stairs['landing']) + ['where' => 'stairs'];
            } else {
                $unchecked[] = 'stairs';
            }
        }

        if (! $steps) {
            return ['status' => self::UNKNOWN, 'where' => null, 'gap' => null,
                'need' => null, 'room' => null, 'steps' => [], 'unchecked' => $unchecked];
        }

        $worst = collect($steps)->sortBy([
            fn ($x, $y) => self::SEVERITY[$y['status']] <=> self::SEVERITY[$x['status']],
            fn ($x, $y) => $x['gap'] <=> $y['gap'],
        ])->first();

        $status = $worst['status'] === self::PASSES && $unchecked ? self::UNKNOWN : $worst['status'];

        return ['status' => $status] + array_intersect_key($worst, array_flip(['where', 'gap', 'need', 'room']))
            + ['steps' => $steps, 'unchecked' => $unchecked];
    }

    /** Every measurement a step needs is there. */
    private static function known(?array $step, array $keys): bool
    {
        foreach ($keys as $key) {
            if (! isset($step[$key]) || (int) $step[$key] <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * The best of several ways through. Each way is a list of [needed,
     * available] pairs; its slack is the smallest difference, and the pair
     * that sets it is the reason a verdict can give. Available room is
     * rounded down to the centimetre, never up.
     *
     * @param  list<list<array{0:int,1:int|float}>>  $ways
     * @return array{status:string, gap:int, need:int, room:int}
     */
    private static function best(array $ways): array
    {
        $best = null;

        foreach ($ways as $way) {
            $limit = null;
            foreach ($way as [$need, $room]) {
                $room = (int) floor($room);
                $gap = $room - $need;
                if ($limit === null || $gap < $limit['gap']) {
                    $limit = ['gap' => $gap, 'need' => $need, 'room' => $room];
                }
            }
            if ($best === null || $limit['gap'] > $best['gap']) {
                $best = $limit;
            }
        }

        $status = match (true) {
            $best['gap'] >= 2 * self::MARGIN_CM => self::PASSES,
            $best['gap'] >= 0 => self::TIGHT,
            default => self::NO,
        };

        return ['status' => $status] + $best;
    }
}
