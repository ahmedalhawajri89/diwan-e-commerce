<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An order knew only where it stood, never how it got there or when. Each
 * line here is one thing that happened to it: placed, moved to a stage,
 * marked paid, or a note the staff wrote for each other. Who did it, and
 * when, come with every line.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 20);            // placed | status | payment | note
            $table->string('from', 20)->nullable();
            $table->string('to', 20)->nullable();
            $table->text('body')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_events');
    }
};
