<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'provider',
        'amount',
        'currency',
        'status',
        'transaction_id',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
