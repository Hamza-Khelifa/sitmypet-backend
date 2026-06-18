<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Explicitly enable PostGIS if it isn't already
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');

        Schema::create('sitter_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->text('bio')->nullable();
            $table->decimal('hourly_rate', 8, 2)->default(0);
            
            // Requires postgis extension
            $table->geography('location', 'Point', 4326)->nullable();
            
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add GiST index for fast spatial queries
        DB::statement('CREATE INDEX sitter_profiles_location_gist ON sitter_profiles USING GIST (location);');

        Schema::create('certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sitter_profile_id');
            $table->string('type');
            $table->string('document_url')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('sitter_profile_id')->references('id')->on('sitter_profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('sitter_profiles');
    }
};
