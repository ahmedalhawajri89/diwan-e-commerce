<?php

namespace App\Support;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\ShopSetting;
use Illuminate\Support\Carbon;

/**
 * Whether a customer can ask to return an order now, and what to tell them
 * when they cannot. The period is the shop's own (shop settings): without
 * one, there is no button, only "contact us", because a return policy the
 * shop has not set must not be promised.
 */
final class Returns
{
    /** The reasons a customer chooses from, for the page that asks. */
    public const REASONS = ReturnRequest::REASONS;

    /** The states a request moves through, for the panel's filter. */
    public const STATUSES = ReturnRequest::STATUSES;

    /** Already asked; the request speaks for itself. */
    public const ASKED = 'asked';

    /** Not delivered yet, or cancelled: nothing to return. */
    public const NOT_YET = 'not_yet';

    /** The shop has not set a return period: contact it. */
    public const CONTACT = 'contact';

    public const OPEN = 'open';

    public const CLOSED = 'closed';

    /** @return array{state:string, until:Carbon|null} */
    public static function window(Order $order): array
    {
        if ($order->returnRequest) {
            return ['state' => self::ASKED, 'until' => null];
        }
        if ($order->status !== 'delivered') {
            return ['state' => self::NOT_YET, 'until' => null];
        }

        $days = ShopSetting::current()->return_days;
        if ($days === null) {
            return ['state' => self::CONTACT, 'until' => null];
        }

        // From the day it was delivered; an order delivered before its history
        // was kept counts from its last change.
        $delivered = $order->events()->where('kind', 'status')->where('to', 'delivered')->latest()->value('created_at');
        $until = Carbon::parse($delivered ?? $order->updated_at)->addDays($days)->endOfDay();

        return ['state' => now()->lte($until) ? self::OPEN : self::CLOSED, 'until' => $until];
    }
}
