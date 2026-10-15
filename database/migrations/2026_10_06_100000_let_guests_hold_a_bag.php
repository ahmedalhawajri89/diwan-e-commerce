<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A bag no longer needs an account. A visitor's bag is found by a random token
 * kept in their session, and becomes their account's bag when they sign in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('token', 64)->nullable()->unique()->after('user_id');
        });
    }

    public function down(): void
    {
        // Guest bags have no owner to fall back to; they go before the column
        // can be required again.
        DB::table('carts')->whereNull('user_id')->delete();

        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn('token');
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
