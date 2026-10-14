<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * The shop's settings: one row, read on most pages, so cached until saved.
 * An empty value is a decision not yet taken, and every reader treats it as
 * such ("agreed on contact"), never as a default of its own.
 */
class ShopSetting extends Model
{
    private const CACHE_KEY = 'shop.settings';

    /** Days of the week as Carbon numbers them: 0 is Sunday. */
    public const WEEKDAYS = [0, 1, 2, 3, 4, 5, 6];

    protected $fillable = ['legal_name', 'vat_number', 'return_days', 'delivery_lead_days', 'delivery_weekdays'];

    protected function casts(): array
    {
        return [
            'return_days' => 'integer',
            'delivery_lead_days' => 'integer',
            'delivery_weekdays' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
    }

    /** The settings as they stand, or an empty, unsaved row. */
    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->first()) ?? new static;
    }

    /** An invoice may be called a tax invoice only with the seller's name and VAT number on file. */
    public function issuesTaxInvoices(): bool
    {
        return filled($this->vat_number) && filled($this->legal_name);
    }

    /** A delivery can be booked only once the shop has said when it delivers. */
    public function booksDeliveries(): bool
    {
        return $this->delivery_lead_days !== null && ! empty($this->delivery_weekdays);
    }
}
