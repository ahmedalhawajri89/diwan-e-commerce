<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A customer asking to send an order back, and why. Requested, then approved
 * or declined by the shop; an approved one is marked received when the
 * pieces are back, which returns them to stock.
 */
class ReturnRequest extends Model
{
    /** Why, in the customer's words chosen from a list. `size` is the one the passage check is for. */
    public const REASONS = ['size', 'colour', 'damaged', 'changed_mind', 'other'];

    public const STATUSES = ['requested', 'approved', 'rejected', 'received'];

    protected $fillable = ['order_id', 'user_id', 'reason', 'note', 'status', 'decided_at', 'received_at'];

    protected function casts(): array
    {
        return ['decided_at' => 'datetime', 'received_at' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
