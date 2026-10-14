<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The cities the shop delivers to, each with its fee (empty: agreed on
 * contact), and on an order the city, the fee as it was, and the day and
 * part of the day booked for delivery. The fee is a snapshot: a fee changed
 * later must not change what an order already says it costs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_ar', 100)->nullable();
            $table->decimal('fee', 10, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('delivery_zone_id')->nullable()->after('shipping_method_id')->constrained()->nullOnDelete();
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('delivery_zone_id');
            $table->date('delivery_date')->nullable()->after('delivery_fee');
            $table->string('delivery_window', 10)->nullable()->after('delivery_date');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivery_zone_id');
            $table->dropColumn(['delivery_fee', 'delivery_date', 'delivery_window']);
        });
        Schema::dropIfExists('delivery_zones');
    }
};
