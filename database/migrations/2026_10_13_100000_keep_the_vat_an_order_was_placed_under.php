<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The VAT an order was placed under, kept with it: the rate, and the tax
 * inside its total. A rate can change; an order's receipt must not. Null on
 * orders placed before the shop counted VAT, which then show no tax line
 * rather than one worked out after the fact.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('vat_rate', 5, 2)->nullable()->after('total_price');
            $table->decimal('vat_amount', 10, 2)->nullable()->after('vat_rate');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['vat_rate', 'vat_amount']);
        });
    }
};
