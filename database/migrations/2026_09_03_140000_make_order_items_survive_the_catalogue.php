<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An order line is a record of what somebody bought. The catalogue is a
     * list of what is for sale today. The two were tied together in a way that
     * let the second rewrite the first.
     *
     * `product_id` and `product_variant_id` were both `cascadeOnDelete`, so an
     * operator retiring a discontinued piece silently deleted it from every
     * past order — the totals stayed, the lines vanished, and the order no
     * longer said what it contained. That defeats the whole point of the
     * `product_name_snapshot` and `product_image_snapshot` columns, which exist
     * precisely so history survives the catalogue.
     *
     * `product_variant_id` was also NOT NULL while CartController writes null
     * for any product with no variant, so checking out such a product returned
     * a 500 instead of an order.
     *
     * Both columns become nullable and release rather than cascade. The
     * snapshots carry the record; the ids are only a link back when the piece
     * is still on sale.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->unsignedBigInteger('product_variant_id')->nullable()->change();

            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
        });

        // The same fault sits one table earlier: cart_items.product_variant_id
        // is NOT NULL while CartController writes null for a product with no
        // variant, so such a piece could not even be added to the bag. Here the
        // product_id cascade is correct — a deleted product should leave the
        // bag, because nobody can buy it — so only the variant changes.
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id')->nullable()->change();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_variant_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->unsignedBigInteger('product_variant_id')->nullable(false)->change();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_variant_id')->nullable(false)->change();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->cascadeOnDelete();
        });
    }
};
