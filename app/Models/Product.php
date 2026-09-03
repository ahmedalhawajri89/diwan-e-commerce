<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * The variant a card and a listing speak for — the cheapest one.
     *
     * Reads the loaded collection when `variants` is eager-loaded and only
     * falls back to a query when it is not. The previous version always issued
     * a query, which meant `with('variants')` bought nothing and every product
     * grid ran one extra query per card.
     */
    public function primaryVariant(): ?ProductVariant
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->sortBy('price')->first();
        }

        return $this->variants()->orderBy('price')->first();
    }

    /** The image a card shows: the flagged primary, else the first on file. */
    public function primaryImage(): ?ProductImage
    {
        if ($this->relationLoaded('images')) {
            return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        }

        return $this->images()->orderByDesc('is_primary')->first();
    }

    /** A card always has something to show, even with no rows in `images`. */
    public function imageUrl(): string
    {
        return $this->primaryImage()?->url ?? asset('assets/images/product-img-1.jpg');
    }

    /**
     * Price is the cheapest variant's. A product with no variant has no price,
     * and callers must say so rather than print an invented figure.
     */
    public function getPriceAttribute(): ?float
    {
        $variant = $this->primaryVariant();

        return $variant ? (float) $variant->price : null;
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }
}
