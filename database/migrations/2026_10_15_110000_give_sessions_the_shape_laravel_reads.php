<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The first migration made `sessions` a table of its own design (a numeric
 * id, a session_token), which Laravel's database session driver cannot write:
 * it inserts the session id into `id` as a string. Locally sessions live in
 * files, so nothing showed; a host with no lasting disk keeps them in the
 * database, where every insert failed quietly and no one could sign in.
 *
 * Nothing reads the old table. It is set aside under another name rather
 * than dropped, and a table of the shape Laravel expects takes its place.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sessions', 'session_token')) {
            Schema::rename('sessions', 'legacy_sessions');
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('legacy_sessions')) {
            Schema::drop('sessions');
            Schema::rename('legacy_sessions', 'sessions');
        }
    }
};
