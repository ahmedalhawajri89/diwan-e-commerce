<?php

namespace App\Support;

use App\Models\ShopSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The days a customer can book a delivery for: from the shop's notice
 * onwards, only on the days it delivers, for the next two weeks. Nothing
 * when the shop has not said when it delivers: then it calls to agree a day,
 * and promises none.
 */
final class DeliveryDates
{
    /** How far ahead a delivery can be booked. */
    public const HORIZON_DAYS = 14;

    /** The parts of the day, named without hours the shop has not set. */
    public const WINDOWS = ['morning', 'evening'];

    /** @return Collection<int, Carbon> */
    public static function options(?ShopSetting $settings = null, ?Carbon $today = null): Collection
    {
        $settings ??= ShopSetting::current();

        if (! $settings->booksDeliveries()) {
            return collect();
        }

        $start = ($today ?? now())->copy()->startOfDay()->addDays($settings->delivery_lead_days);
        $days = array_map('intval', $settings->delivery_weekdays);

        return collect(range(0, self::HORIZON_DAYS - 1))
            ->map(fn (int $i) => $start->copy()->addDays($i))
            ->filter(fn (Carbon $day) => in_array($day->dayOfWeek, $days, true))
            ->values();
    }

    /** Whether a date the form sent is one of those offered. */
    public static function offers(?string $date): bool
    {
        return $date !== null && self::options()->contains(fn (Carbon $day) => $day->toDateString() === $date);
    }
}
