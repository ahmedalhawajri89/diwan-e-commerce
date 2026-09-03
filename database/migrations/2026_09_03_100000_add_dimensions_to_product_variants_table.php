<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The fit system needs real numbers, not the free-text `size` column.
     * Nullable on purpose: a variant with no dimensions recorded must render
     * "not recorded" rather than a guess, so absence has to be representable.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->unsignedSmallInteger('width_cm')->nullable()->after('size');
            $table->unsignedSmallInteger('depth_cm')->nullable()->after('width_cm');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('depth_cm');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['width_cm', 'depth_cm', 'height_cm']);
        });
    }
};
