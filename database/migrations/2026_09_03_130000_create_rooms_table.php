<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Layer 4 of the fit system: the wishlist becomes named rooms.
     *
     * "My room" in the header is one measurement held in the session, which is
     * right for a visitor who has not registered. Someone furnishing a flat has
     * several rooms and wants to keep them, so a room here is a real row with
     * its own dimensions.
     *
     * A saved piece may belong to a room or to none. `wishlist_items` keeps its
     * unique (wishlist_id, product_id) constraint — a piece is still saved once;
     * the room is only where it has been put.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            // Nullable so a room can be named before it is measured. Every
            // total the page prints says which of the two it is working from.
            $table->unsignedSmallInteger('width_cm')->nullable();
            $table->unsignedSmallInteger('length_cm')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            // nullOnDelete: deleting a room releases its pieces back to the
            // saved list rather than taking them with it.
            $table->foreignId('room_id')->nullable()->after('product_id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
        });

        Schema::dropIfExists('rooms');
    }
};
