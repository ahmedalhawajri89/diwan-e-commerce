<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two things an order needs to carry for the people who handle it.
 *
 * notes   — what the customer wants the courier to know: the third floor,
 *           no lift, call before coming. Free text, optional.
 * locale  — the language the customer shopped in, so every later email about
 *           the order is written in it. Status emails went out in the app's
 *           default language because nothing recorded the customer's.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('payment_status');
            $table->string('locale', 8)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['notes', 'locale']);
        });
    }
};
