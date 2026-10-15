<?php

return [

    /*
    | Saudi value-added tax, in percent. A legal rate, not a shop choice: 15%
    | since July 2020. Prices are stored and shown with it included, as the
    | consumer-protection rules require, so a shopper pays what they read.
    | An order keeps the rate it was placed under (orders.vat_rate).
    */
    'vat_rate' => (float) env('SHOP_VAT_RATE', 15),

    /*
    | The currency every price is in, as ISO 4217: what search engines are
    | told in a product's structured data. The words on screen come from
    | App\Support\Money.
    */
    'currency' => 'SAR',

];
