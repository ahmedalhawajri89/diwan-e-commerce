<?php

namespace App\Models;

use App\Models\Concerns\SpeaksArabic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * A city the shop delivers to, and what delivery there costs. A fee left
 * empty is not free: it is agreed when the shop calls, and says so.
 */
class DeliveryZone extends Model
{
    use SpeaksArabic;

    protected $fillable = ['name', 'name_ar', 'fee', 'active', 'position'];

    protected function casts(): array
    {
        return ['fee' => 'decimal:2', 'active' => 'boolean', 'position' => 'integer'];
    }

    /** The name in the visitor's language. See SpeaksArabic. */
    protected function name(): Attribute
    {
        return Attribute::get(fn ($value) => $this->inLocale('name', $value));
    }

    /** The cities a customer can choose, in the order the shop set. */
    public function scopeOffered(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('position')->orderBy('name');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
