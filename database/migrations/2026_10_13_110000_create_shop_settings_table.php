<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The shop's own facts and policies, set by an administrator: who issues the
 * invoices and under which VAT number, how long a customer has to return a
 * piece, and when deliveries can be booked. One row, one column per fact,
 * each typed and checked. Every one starts empty: until the shop decides, the
 * storefront says it will be agreed on contact, rather than invent a policy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name', 160)->nullable();
            $table->string('vat_number', 15)->nullable();
            $table->unsignedSmallInteger('return_days')->nullable();
            $table->unsignedTinyInteger('delivery_lead_days')->nullable();
            $table->json('delivery_weekdays')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};
