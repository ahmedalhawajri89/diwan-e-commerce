<?php

namespace App\Models;

use App\Models\Concerns\SpeaksArabic;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    use SpeaksArabic;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'description',
        'description_ar',
        'status',
        'category_id',
    ];

    /** The name in the visitor's language. See SpeaksArabic. */
    protected function name(): Attribute
    {
        return Attribute::get(fn ($value) => $this->inLocale('name', $value));
    }

    protected function description(): Attribute
    {
        return Attribute::get(fn ($value) => $this->inLocale('description', $value));
    }

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
        // A neutral stand-in, not a photograph of some other product. A card
        // with no image of its own must not borrow one — the shop's promise is
        // that the picture is of the piece.
        return $this->primaryImage()?->url ?? asset('assets/images/product-placeholder.jpg');
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

    /** The parts a piece needs before its page is whole, in the order to fix them. */
    public const COMPLETE = ['photo', 'price', 'measured', 'arrives', 'arabic', 'described'];

    /**
     * Which of those this piece has. Read from relations the caller has
     * loaded (images, variants), so a list of pieces asks nothing more.
     *
     * @return array<string, bool>
     */
    public function completeness(): array
    {
        $variant = $this->primaryVariant();

        return [
            'photo' => $this->images->isNotEmpty(),
            'price' => $variant !== null,
            'measured' => (bool) $variant?->hasDimensions(),
            'arrives' => (bool) $variant?->arrivalKnown(),
            'arabic' => filled($this->raw('name_ar')) && filled($this->raw('description_ar')),
            'described' => filled($this->raw('description')),
        ];
    }

    /** All the stock of every colour together. */
    public function totalStock(): int
    {
        return (int) $this->variants->sum('stock');
    }
}
