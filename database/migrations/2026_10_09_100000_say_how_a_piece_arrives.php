<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How a piece comes through the door, for the passage check: assembled, with
 * parts that come off, or flat in boxes, and the size of the largest thing
 * carried. All nullable: "not said yet" is a real answer, and the shop is
 * cautious about it rather than assuming a piece comes assembled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('arrives', 16)->nullable()->after('height_cm');
            $table->unsignedSmallInteger('carry_w_cm')->nullable()->after('arrives');
            $table->unsignedSmallInteger('carry_d_cm')->nullable()->after('carry_w_cm');
            $table->unsignedSmallInteger('carry_h_cm')->nullable()->after('carry_d_cm');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['arrives', 'carry_w_cm', 'carry_d_cm', 'carry_h_cm']);
        });
    }
};
