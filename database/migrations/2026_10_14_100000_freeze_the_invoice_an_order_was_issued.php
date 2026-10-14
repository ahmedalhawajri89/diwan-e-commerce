<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A tax invoice, once issued, does not change: the seller's name and VAT
 * number it was issued under, and when, are kept on the order the first
 * time it is issued, so a later edit of the shop's settings cannot rewrite
 * an invoice a customer already holds.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('invoiced_at')->nullable()->after('vat_amount');
            $table->string('invoice_seller', 160)->nullable()->after('invoiced_at');
            $table->string('invoice_vat_number', 15)->nullable()->after('invoice_seller');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['invoiced_at', 'invoice_seller', 'invoice_vat_number']);
        });
    }
};
