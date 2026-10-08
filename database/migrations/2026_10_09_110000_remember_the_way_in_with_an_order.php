<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An order keeps the way into the customer's home as it was when they
 * ordered, so the people delivering it know before they set out whether
 * it needs the stairs, a hoist, or taking apart. And two answers that say
 * whether the passage check works: how the delivery went, from the
 * customer, and why an order was cancelled, from the shop.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('route')->nullable()->after('notes');
            $table->string('delivery_feedback', 12)->nullable()->after('route');
            $table->string('cancel_reason', 24)->nullable()->after('delivery_feedback');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['route', 'delivery_feedback', 'cancel_reason']);
        });
    }
};
