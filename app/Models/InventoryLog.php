<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One movement of one colour's stock: what moved it, by how much, and what
 * was left. Written by App\Support\Stock only, so every movement is told the
 * same way. `source_type` is one of SOURCES; `reference_id` is the order it
 * belongs to, when it belongs to one.
 */
class InventoryLog extends Model
{
    public const SOURCES = ['order', 'cancel', 'manual', 'edit'];

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'change_amount',
        'stock_after',
        'type',
        'source_type',
        'reference_id',
        'user_id',
        'note',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The change with its sign: +3, −2. */
    public function signed(): int
    {
        return $this->type === 'decrease' ? -$this->change_amount : $this->change_amount;
    }
}
