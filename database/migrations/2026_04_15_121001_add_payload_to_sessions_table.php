<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Check if payload column doesn't exist before adding
            if (! Schema::hasColumn('sessions', 'payload')) {
                $table->longText('payload')->after('session_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            if (Schema::hasColumn('sessions', 'payload')) {
                $table->dropColumn('payload');
            }
        });
    }
};
