<?php

namespace App\Support;

use App\Models\Order;
use App\Models\ShopSetting;
use Illuminate\Support\Carbon;

/**
 * What an order's paper is: a simplified tax invoice, or a plain receipt,
 * and why.
 *
 * A tax invoice is issued only when all of these hold, because calling a
 * paper a tax invoice is a claim to the tax authority:
 *   - the shop has a legal name and a VAT number on file;
 *   - the order counted its VAT when placed (orders.vat_amount);
 *   - the sale has happened: delivered, or paid;
 *   - it was not cancelled.
 * The first time those hold, the seller's name and number and the moment
 * are frozen on the order; from then on the invoice reads only those.
 */
final class Invoice
{
    public const TAX = 'tax';

    public const RECEIPT = 'receipt';

    /**
     * @return array{kind:string, reason:string|null, number:string, issued_at:Carbon|null,
     *               seller:string|null, vat_number:string|null, net:float, vat:float|null, qr:string|null}
     */
    public static function for(Order $order): array
    {
        if (! $order->invoiced_at && self::due($order)) {
            $settings = ShopSetting::current();
            $order->forceFill([
                'invoiced_at' => self::suppliedAt($order),
                'invoice_seller' => $settings->legal_name,
                'invoice_vat_number' => $settings->vat_number,
            ])->save();
        }

        $total = (float) $order->total_price;
        $vat = $order->vat_amount !== null ? (float) $order->vat_amount : null;

        if ($order->invoiced_at) {
            $payload = ZatcaQr::payload($order->invoice_seller, $order->invoice_vat_number, $order->invoiced_at, $total, $vat);

            return [
                'kind' => self::TAX, 'reason' => null, 'number' => 'INV-'.$order->reference(),
                'issued_at' => $order->invoiced_at, 'seller' => $order->invoice_seller, 'vat_number' => $order->invoice_vat_number,
                'net' => round($total - $vat, 2), 'vat' => $vat, 'qr' => ZatcaQr::svg($payload),
            ];
        }

        return [
            'kind' => self::RECEIPT, 'reason' => self::why($order), 'number' => $order->number(),
            'issued_at' => null, 'seller' => ShopSetting::current()->legal_name, 'vat_number' => null,
            'net' => $vat !== null ? round($total - $vat, 2) : $total, 'vat' => $vat, 'qr' => null,
        ];
    }

    /** Every condition for a tax invoice holds now. */
    private static function due(Order $order): bool
    {
        return $order->status !== 'cancelled'
            && $order->vat_amount !== null
            && ShopSetting::current()->issuesTaxInvoices()
            && ($order->status === 'delivered' || $order->payment_status === 'paid');
    }

    /** When the sale happened: the delivery, or else the payment, or else now. */
    private static function suppliedAt(Order $order): Carbon
    {
        $at = $order->events()->where(fn ($q) => $q->where(fn ($q) => $q->where('kind', 'status')->where('to', 'delivered'))
            ->orWhere(fn ($q) => $q->where('kind', 'payment')->where('to', 'paid')))
            ->oldest()->value('created_at');

        return $at ? Carbon::parse($at) : now();
    }

    /** Why this is a receipt and not a tax invoice, in one key. */
    private static function why(Order $order): string
    {
        return match (true) {
            $order->status === 'cancelled' => 'cancelled',
            $order->vat_amount === null => 'before_vat',
            ! ShopSetting::current()->issuesTaxInvoices() => 'no_vat_number',
            default => 'not_supplied',
        };
    }
}
