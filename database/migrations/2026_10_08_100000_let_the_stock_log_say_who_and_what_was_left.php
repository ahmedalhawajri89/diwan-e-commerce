<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The stock log has existed since the first migration and was never written
 * to. Before it starts recording, it needs to answer the questions an
 * operator asks of it: how many were left after this, who did it, and why.
 *
 * `reference_id` becomes nullable: a hand adjustment refers to no order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->integer('stock_after')->nullable()->after('change_amount');
            $table->foreignId('user_id')->nullable()->after('reference_id')->constrained()->nullOnDelete();
            $table->string('note')->nullable()->after('user_id');
            $table->unsignedBigInteger('reference_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['stock_after', 'note']);
            $table->unsignedBigInteger('reference_id')->nullable(false)->change();
        });
    }
};
