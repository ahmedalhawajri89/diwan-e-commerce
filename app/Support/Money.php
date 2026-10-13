<?php

namespace App\Support;

/**
 * Every amount the shop shows, written one way, from one place.
 *
 * The shop sells in Saudi riyals. Arabic puts the unit after the figure, as
 * Saudi shops do ("1,499 ر.س"); English puts the code first ("SAR 1,499").
 * Figures are Western digits with a thousands comma, the site's rule. Halalas
 * show only when there are any: a price of 1,499 is not "1,499.00".
 *
 * Before this, forty templates and three scripts each glued a symbol to a
 * number_format() of their own, so changing the currency meant forty edits
 * and forty chances to miss one.
 */
final class Money
{
    /** "1,499 ر.س" / "SAR 1,499", in the current locale (or the one given). */
    public static function format(float|int|string|null $amount, ?string $locale = null): string
    {
        return __('diwan.common.money', ['amount' => self::figure($amount)], $locale);
    }

    /** The figure alone: "1,499", "1,499.50". */
    public static function figure(float|int|string|null $amount): string
    {
        $value = round((float) $amount, 2);
        $decimals = fmod(abs($value), 1.0) > 0.0001 ? 2 : 0;

        return number_format($value, $decimals);
    }

    /** The unit alone, for a layout that sets it apart: "ر.س" / "SAR". */
    public static function symbol(?string $locale = null): string
    {
        return __('diwan.common.currency', [], $locale);
    }

    /** Whether the unit comes before the figure in this locale. */
    public static function symbolFirst(?string $locale = null): bool
    {
        return ! str_starts_with(__('diwan.common.money', [], $locale), ':amount');
    }
}
