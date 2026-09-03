<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The admin order form and the orders table disagreed about what an order
     * can be.
     *
     * `Admin\OrderController::update()` validates status against
     * pending/processing/shipped/delivered/cancelled and payment_status
     * against pending/paid/failed/refunded. The table allowed neither
     * `processing` nor `refunded`. Both values passed validation and were then
     * rejected by MySQL in strict mode, so saving an order as "processing" —
     * the most ordinary transition there is — returned a 500.
     *
     * The controller's vocabulary is the correct one, so the table moves to
     * meet it. `paid` also leaves the *order* status: whether money arrived is
     * what payment_status is for, and no row uses it.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])
                ->default('pending')
                ->change();

            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])
                ->default('pending')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'shipped', 'delivered', 'cancelled'])
                ->default('pending')
                ->change();

            $table->enum('payment_status', ['pending', 'paid', 'failed'])
                ->default('pending')
                ->change();
        });
    }
};
