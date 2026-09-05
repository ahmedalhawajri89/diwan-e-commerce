<?php

namespace App\Models;

use App\Support\Fit;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'width_cm',
        'length_cm',
    ];

    protected function casts(): array
    {
        return [
            'width_cm'  => 'integer',
            'length_cm' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(WishlistItem::class);
    }

    /** True only when the room can be compared against a piece. */
    public function isMeasured(): bool
    {
        return (bool) ($this->width_cm && $this->length_cm);
    }

    /** Floor area in square metres, or null when the room is unmeasured. */
    public function floorArea(): ?float
    {
        return $this->isMeasured()
            ? ($this->width_cm * $this->length_cm) / 10000
            : null;
    }

    /** The shape Fit::assess() expects, or null. */
    public function dimensions(): ?array
    {
        return $this->isMeasured()
            ? ['width' => $this->width_cm, 'length' => $this->length_cm]
            : null;
    }

    /**
     * What the pieces in this room add up to.
     *
     * @return array{footprint:float, share:float|null, unknown:int}
     */
    public function totals(): array
    {
        $variants = $this->items
            ->map(fn (WishlistItem $item) => $item->product?->primaryVariant());

        return Fit::total($variants, $this->dimensions());
    }
}
