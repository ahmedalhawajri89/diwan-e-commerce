<?php

namespace App\Support;

use App\Models\ProductVariant;

/**
 * The fit system's arithmetic, kept pure so it can be reasoned about and
 * tested without a request, a session, or a database.
 *
 * A piece "fits" a room if its footprint can be placed against a wall in
 * either orientation while leaving a walkway. It is "tight" if it physically
 * goes in but eats the clearance. It "will not fit" if no orientation gets it
 * through. Anything we cannot compute honestly returns UNKNOWN.
 */
final class Fit
{
    public const FITS = 'fits';

    public const TIGHT = 'tight';

    public const NO = 'no';

    public const UNKNOWN = 'unknown';   // the piece has no dimensions on file

    public const NO_ROOM = 'no_room';   // we have dimensions but no room to compare against

    /**
     * Clearance a person needs to walk past a piece, in centimetres.
     * 75cm is the common accessible-walkway figure; below it a room reads
     * cramped even though the piece technically went in.
     */
    public const WALKWAY_CM = 75;

    /** A door most pieces have to come through, in centimetres. */
    public const DOORWAY_CM = 80;

    /** Above this share of the floor a single piece is "will not fit". */
    public const DOMINATES_SHARE = 0.60;

    /** Above this share it fits, but crowds the room. */
    public const CROWDED_SHARE = 0.33;

    /**
     * The rules as data, for the home page's live demo, which runs the same
     * assessment in the browser as the visitor drags the walls. Read from
     * here so the two cannot drift apart. See resources/js/components/fit-demo.js.
     */
    public static function rules(): array
    {
        return [
            'walkway' => self::WALKWAY_CM,
            'doorway' => self::DOORWAY_CM,
            'dominates' => self::DOMINATES_SHARE,
            'crowded' => self::CROWDED_SHARE,
        ];
    }

    /**
     * Compare one variant against a room.
     *
     * @param  array{width:int,length:int}|null  $room
     * @return array{status:string, note:string|null, footprint:float|null, share:float|null}
     */
    public static function assess(?ProductVariant $variant, ?array $room): array
    {
        $w = $variant?->width_cm;
        $d = $variant?->depth_cm;

        // No dimensions recorded is a real answer, not a failure to compute.
        if (! $w || ! $d) {
            return self::result(self::UNKNOWN, null, null, null);
        }

        $footprint = ($w * $d) / 10000; // square metres

        if (! $room || empty($room['width']) || empty($room['length'])) {
            return self::result(self::NO_ROOM, null, $footprint, null);
        }

        $roomW = (int) $room['width'];
        $roomL = (int) $room['length'];
        $share = $footprint / (($roomW * $roomL) / 10000);

        // Longest side of the piece against the longest wall, either way round.
        $longSide = max($w, $d);
        $shortSide = min($w, $d);
        $longWall = max($roomW, $roomL);
        $shortWall = min($roomW, $roomL);

        // The checks run most-severe first. Ordering matters: a bed taking
        // three quarters of the floor was previously reported as "tight
        // (doorway)", because the doorway test ran first and its note masked
        // the fact the visitor actually needed.

        // 1 · It does not go in at all, in either orientation.
        if ($longSide > $longWall || $shortSide > $shortWall) {
            return self::result(self::NO, 'too_long', $footprint, $share);
        }

        // 2 · It fits the walls but swallows the room. Past 60 % of the floor
        //     a single piece stops being furniture and becomes the room.
        if ($share > self::DOMINATES_SHARE) {
            return self::result(self::NO, 'dominates', $footprint, $share);
        }

        // 3 · It goes in, but the room stops working around it.
        if (($shortWall - $shortSide) < self::WALKWAY_CM) {
            return self::result(self::TIGHT, 'walkway', $footprint, $share);
        }

        if ($share > self::CROWDED_SHARE) {
            return self::result(self::TIGHT, 'crowded', $footprint, $share);
        }

        // 4 · Least severe, and the one thing the visitor can measure and
        //     work around: getting it through the door.
        if (min($w, $d, $variant->height_cm ?: PHP_INT_MAX) > self::DOORWAY_CM) {
            return self::result(self::TIGHT, 'doorway', $footprint, $share);
        }

        return self::result(self::FITS, null, $footprint, $share);
    }

    /**
     * Total footprint of a set of variants, and its share of a room's floor.
     *
     * @param  iterable<ProductVariant|null>  $variants
     * @param  array{width:int,length:int}|null  $room
     * @return array{footprint:float, share:float|null, unknown:int}
     */
    public static function total(iterable $variants, ?array $room): array
    {
        $footprint = 0.0;
        $unknown = 0;

        foreach ($variants as $variant) {
            if ($variant && $variant->width_cm && $variant->depth_cm) {
                $footprint += ($variant->width_cm * $variant->depth_cm) / 10000;
            } else {
                $unknown++;
            }
        }

        $share = null;
        if ($room && ! empty($room['width']) && ! empty($room['length'])) {
            $floor = ((int) $room['width'] * (int) $room['length']) / 10000;
            $share = $floor > 0 ? $footprint / $floor : null;
        }

        return ['footprint' => $footprint, 'share' => $share, 'unknown' => $unknown];
    }

    /**
     * The sprite id each status carries. Status is never colour alone.
     *
     * Bare names rather than `bi-` classes: these feed <x-icon name="...">,
     * which resolves them against the sprite in partials/icon-sprite.blade.php.
     */
    public static function icon(string $status): string
    {
        return match ($status) {
            self::FITS => 'check-lg',
            self::TIGHT => 'exclamation-triangle',
            self::NO => 'x-lg',
            self::NO_ROOM => 'rulers',
            default => 'dash-lg',
        };
    }

    /**
     * @return array{status:string, note:string|null, footprint:float|null, share:float|null}
     */
    private static function result(string $status, ?string $note, ?float $footprint, ?float $share): array
    {
        return compact('status', 'note', 'footprint', 'share');
    }
}
