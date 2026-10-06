<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

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
}
