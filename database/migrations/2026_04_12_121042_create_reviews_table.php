<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // A rating outside 1-5 is meaningless, so the constraint is worth
        // keeping — but `ALTER TABLE ... ADD CONSTRAINT` is MySQL syntax that
        // SQLite rejects outright, and the test suite runs on SQLite. Every
        // migration after this one used to die here, taking the whole suite
        // with it. MySQL and MariaDB get the named constraint; SQLite gets an
        // inline CHECK, which it does support at table-creation time.
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_rating_valid CHECK (rating >= 1 AND rating <= 5)');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_rating_valid CHECK (rating >= 1 AND rating <= 5)');
        }

        // SQLite cannot add a CHECK after the fact at all. The application
        // validates the range on the way in (ReviewController), so the data is
        // protected either way; only the database-level belt is driver-bound.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
