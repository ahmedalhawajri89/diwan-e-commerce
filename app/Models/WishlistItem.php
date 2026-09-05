<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    protected $fillable = [
        'wishlist_id',
        'product_id',
        'room_id',
    ];

    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** The room this piece has been put in, if any. */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
