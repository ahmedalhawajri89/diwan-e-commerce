<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color',
        'size',
        'width_cm',
        'depth_cm',
        'height_cm',
        'price',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'width_cm' => 'integer',
            'depth_cm' => 'integer',
            'height_cm' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
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
     * The colour's name in the visitor's language.
     *
     * The stored value stays English, because the catalogue filter matches on
     * it and the swatch paints it. Names the shop has a translation for read
     * in Arabic on the Arabic site; any other name an operator typed is shown
     * as typed, rather than hidden.
     */
    public function colourLabel(): ?string
    {
        return $this->color ? self::labelFor($this->color) : null;
    }

    /** The same, for a stored colour value on its own (the catalogue filter). */
    public static function labelFor(string $colour): string
    {
        $key = 'diwan.colours.'.Str::lower($colour);

        return Lang::has($key) ? __($key) : $colour;
    }

    /** True only when every dimension the fit system needs is recorded. */
    public function hasDimensions(): bool
    {
        return (bool) ($this->width_cm && $this->depth_cm && $this->height_cm);
    }

    /** Footprint in square metres, or null when it cannot be known. */
    public function footprint(): ?float
    {
        return ($this->width_cm && $this->depth_cm)
            ? ($this->width_cm * $this->depth_cm) / 10000
            : null;
    }
}
