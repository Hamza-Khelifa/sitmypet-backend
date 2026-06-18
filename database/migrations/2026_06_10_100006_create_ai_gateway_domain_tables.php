<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('type')->default('assistant'); // assistant, moderation
            $table->text('prompt');
            $table->text('response')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index for querying history and moderation flags quickly
            $table->index(['user_id', 'type', 'created_at']);
            $table->index(['is_flagged']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_interactions');
    }
};
