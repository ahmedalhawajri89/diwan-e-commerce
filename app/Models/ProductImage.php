<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_url',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Resolve the stored path to a URL.
     *
     * The `image_url` column holds three different shapes depending on where
     * the row came from — an absolute URL from a seeder, a repo-relative
     * `assets/...` path from the original template, or a `products/...` path
     * from an admin upload on the public disk. This accessor is the single
     * place that knows that; nothing else should branch on it.
     */
    public function getUrlAttribute(): string
    {
        $path = (string) $this->image_url;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
