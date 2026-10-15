<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name_snapshot',
        'product_image_snapshot',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * The photograph as it was when bought, resolved the way a product image
     * is (a seeded asset, a stored upload, or a full URL); null if none.
     */
    public function imageUrl(): ?string
    {
        $path = (string) $this->product_image_snapshot;

        return match (true) {
            $path === '' => null,
            str_starts_with($path, 'http://'), str_starts_with($path, 'https://') => $path,
            str_starts_with($path, 'assets/') => asset($path),
            default => asset('storage/'.ltrim($path, '/')),
        };
    }
}
