<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The catalogue's own words in Arabic. The shop's interface was bilingual and
 * its content was not, so an Arabic page read "Cozy Stool" under an Arabic
 * heading. Nullable: a piece without an Arabic name shows its English one,
 * never an empty line.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description_ar')->nullable()->after('description');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_ar')->nullable()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->text('description_ar')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description_ar']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'description', 'description_ar']);
        });
    }
};
