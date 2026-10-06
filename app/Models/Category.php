<?php

namespace App\Models;

use App\Models\Concerns\SpeaksArabic;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    use SpeaksArabic;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'description',
        'description_ar',
        'parent_id',
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

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
