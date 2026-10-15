<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The activity log has existed since the first migration and was never
 * written. Before it starts, it needs to say what changed (each field, before
 * and after) and what the thing was called at the time, so a line about a
 * deleted category still reads as something.
 *
 * `entity_id` and `ip_address` become nullable: a sign-in is about no entity,
 * and a queued job has no address.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->json('changes')->nullable()->after('entity_id');
            $table->string('entity_label')->nullable()->after('entity_id');
            $table->unsignedBigInteger('entity_id')->nullable()->change();
            $table->string('ip_address', 45)->nullable()->change();

            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['entity_type', 'entity_id']);
            $table->dropIndex(['created_at']);
            $table->dropColumn(['changes', 'entity_label']);
        });
    }
};
