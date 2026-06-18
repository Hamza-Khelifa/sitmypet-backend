<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('booking_id');
            $table->uuid('reviewer_id');
            $table->uuid('reviewee_id');
            $table->tinyInteger('rating')->unsigned(); // 1 to 5
            $table->text('content')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewee_id')->references('id')->on('users')->onDelete('cascade');

            // Ensure one review per booking per reviewer
            $table->unique(['booking_id', 'reviewer_id']);
            
            // Index for fast profile loading
            $table->index(['reviewee_id', 'is_flagged']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
