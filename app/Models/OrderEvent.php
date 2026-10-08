<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One thing that happened to an order: it was placed, moved to a stage,
 * marked paid, or a member of staff left a note on it. Written through
 * Order::log(), so every line says who and when.
 */
class OrderEvent extends Model
{
    public const KINDS = ['placed', 'status', 'payment', 'note'];

    protected $fillable = ['order_id', 'user_id', 'kind', 'from', 'to', 'body'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
