<?php

namespace App\Support;

use App\Models\InventoryLog;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

/**
 * Every change to a colour's stock, and the line in the log that says so.
 *
 * The checkout takes stock under its own lock and reports here what it took;
 * a cancellation gives it back; an operator sets a number by hand or through
 * the product form. Whichever it is, the log gets the same row: how much,
 * which way, what was left, who, and why.
 */
final class Stock
{
    /**
     * Log a movement that has already happened to $variant (its `stock` is
     * the new figure).
     */
    public static function record(ProductVariant $variant, int $delta, string $source, ?int $reference = null, ?string $note = null): void
    {
        if ($delta === 0) {
            return;
        }

        InventoryLog::create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'change_amount' => abs($delta),
            'stock_after' => $variant->stock,
            'type' => $delta > 0 ? 'increase' : 'decrease',
            'source_type' => $source,
            'reference_id' => $reference,
            'user_id' => auth()->id(),
            'note' => $note !== null ? mb_substr($note, 0, 255) : null,
        ]);
    }

    /**
     * Set a colour's stock to a figure an operator gave, under a lock so a
     * sale at the same moment is not lost, and log the difference. Returns
     * the variant as it now stands.
     */
    public static function set(ProductVariant $variant, int $stock, string $source = 'manual', ?string $note = null): ProductVariant
    {
        return DB::transaction(function () use ($variant, $stock, $source, $note) {
            $locked = ProductVariant::whereKey($variant->id)->lockForUpdate()->firstOrFail();
            $delta = $stock - $locked->stock;

            if ($delta !== 0) {
                $locked->update(['stock' => $stock]);
                self::record($locked, $delta, $source, null, $note);
            }

            return $locked;
        });
    }

    /** "out", "low" or "ok", by the shop's line. */
    public static function state(int $stock): string
    {
        return match (true) {
            $stock <= 0 => 'out',
            $stock <= Overview::LOW_STOCK => 'low',
            default => 'ok',
        };
    }
}
