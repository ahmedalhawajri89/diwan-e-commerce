<?php

namespace App\Support;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use DateTimeInterface;

/**
 * The QR code a Saudi simplified tax invoice must carry (ZATCA e-invoicing,
 * phase one): five fields in tag-length-value form, Base64-encoded. Scanned
 * with ZATCA's app, it shows who issued the invoice, under which VAT number,
 * when, and for how much, so the buyer can check the tax they paid is
 * declared by a real seller.
 *
 * Phase two (reporting each invoice to ZATCA's Fatoora platform with a
 * cryptographic stamp) needs a registered business and is not done here.
 */
final class ZatcaQr
{
    /**
     * The TLV payload, Base64. Each field: one byte for its tag (1–5), one
     * byte for the length of its value in UTF-8 bytes, then the value.
     */
    public static function payload(string $seller, string $vatNumber, DateTimeInterface $issuedAt, float $total, float $vat): string
    {
        $fields = [
            1 => $seller,
            2 => $vatNumber,
            3 => gmdate('Y-m-d\TH:i:s\Z', $issuedAt->getTimestamp()),
            4 => number_format($total, 2, '.', ''),
            5 => number_format($vat, 2, '.', ''),
        ];

        $bytes = '';
        foreach ($fields as $tag => $value) {
            $bytes .= chr($tag).chr(strlen($value)).$value;
        }

        return base64_encode($bytes);
    }

    /** The payload drawn as an inline SVG, ready to print. */
    public static function svg(string $payload): string
    {
        return (new QRCode(new QROptions([
            'outputBase64' => false,
            'svgAddXmlHeader' => false,
            'addQuietzone' => true,
        ])))->render($payload);
    }
}
