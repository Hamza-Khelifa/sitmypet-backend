<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_id');
            $table->string('name');
            $table->string('species');
            $table->string('breed')->nullable();
            $table->date('birth_date')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->text('behavior_notes')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('medical_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pet_id');
            $table->string('condition');
            $table->string('medication')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('pet_id')->references('id')->on('pets')->onDelete('cascade');
        });

        Schema::create('vaccinations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pet_id');
            $table->string('name');
            $table->date('date_administered');
            $table->date('next_due_date')->nullable();
            $table->string('document_url')->nullable();
            $table->timestamps();

            $table->foreign('pet_id')->references('id')->on('pets')->onDelete('cascade');
        });

        Schema::create('pet_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pet_id');
            $table->string('photo_url');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('pet_id')->references('id')->on('pets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_photos');
        Schema::dropIfExists('vaccinations');
        Schema::dropIfExists('medical_records');
        Schema::dropIfExists('pets');
    }
};
