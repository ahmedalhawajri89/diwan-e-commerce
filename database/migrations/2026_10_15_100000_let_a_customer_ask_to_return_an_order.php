<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A customer's request to send an order back, with why. The reason is the
 * point: counted on the overview, it says whether returns for size (what the
 * fit and passage checks exist to prevent) are few beside the rest. One per
 * order; the shop approves or declines it, then marks the pieces received,
 * which puts them back in stock.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason', 20);
            $table->text('note')->nullable();
            $table->string('status', 12)->default('requested');
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};
