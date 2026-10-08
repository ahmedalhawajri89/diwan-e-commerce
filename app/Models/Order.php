<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'shipping_address_id',
        'coupon_id',
        'shipping_method_id',
        'total_price',
        'discount_amount',
        'status',
        'payment_status',
        'notes',
        'locale',
        'route',
        'delivery_feedback',
        'cancel_reason',
    ];

    protected function casts(): array
    {
        return ['route' => 'array'];
    }

    /** How the delivery went, as the customer says once it has arrived. */
    public const FEEDBACK = ['easy', 'hard', 'failed'];

    /** Why the shop cancelled an order. `route` is the one the passage check is meant to prevent. */
    public const CANCEL_REASONS = ['customer', 'unreachable', 'route', 'stock', 'other'];

    /**
     * The order's number as people read it: "#000042".
     *
     * Made here once. The site printed "#000042" and the emails "#42", so a
     * customer quoting their receipt on the phone gave a number the operator
     * could not find on screen.
     */
    public function number(): string
    {
        return '#'.$this->reference();
    }

    /** The same without the "#", for sentences that supply their own. */
    public function reference(): string
    {
        return str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    /** The stages an order moves through, in order. Cancelled stands apart. */
    public const STAGES = ['pending', 'processing', 'shipped', 'delivered'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /** What happened to it, oldest first. */
    public function events()
    {
        return $this->hasMany(OrderEvent::class)->oldest()->orderBy('id');
    }

    /** Write a line in its history, signed by whoever is signed in. */
    public function log(string $kind, ?string $from = null, ?string $to = null, ?string $body = null): OrderEvent
    {
        return $this->events()->create([
            'user_id' => auth()->id(),
            'kind' => $kind,
            'from' => $from,
            'to' => $to,
            'body' => $body,
        ]);
    }

    /** The stage after this one, or null at the last stage or when cancelled. */
    public function nextStage(): ?string
    {
        $at = array_search($this->status, self::STAGES, true);

        return $at === false ? null : (self::STAGES[$at + 1] ?? null);
    }

    /**
     * When it reached each stage, from its history. An order placed before
     * the history existed knows only when it was placed.
     *
     * @return array<string, Carbon|null>
     */
    public function stageDates(): array
    {
        $reached = $this->events
            ->where('kind', 'status')
            ->groupBy('to')
            ->map(fn ($events) => $events->last()->created_at);

        $dates = ['pending' => $this->created_at];
        foreach (array_slice(self::STAGES, 1) as $stage) {
            $dates[$stage] = $reached[$stage] ?? null;
        }
        $dates['cancelled'] = $reached['cancelled'] ?? null;

        return $dates;
    }
}
