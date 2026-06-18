<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_id');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status');
            $table->text('requirements')->nullable();
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add PostGIS Geography column
        DB::statement('ALTER TABLE demands ADD COLUMN location geography(Point, 4326)');
        DB::statement('CREATE INDEX demands_location_idx ON demands USING GIST (location)');

        Schema::create('demand_pet', function (Blueprint $table) {
            $table->id();
            $table->uuid('demand_id');
            $table->uuid('pet_id');

            $table->foreign('demand_id')->references('id')->on('demands')->onDelete('cascade');
            $table->foreign('pet_id')->references('id')->on('pets')->onDelete('cascade');
        });

        Schema::create('bids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('demand_id');
            $table->uuid('sitter_id');
            $table->decimal('proposed_rate', 8, 2);
            $table->text('cover_letter')->nullable();
            $table->string('status');
            $table->timestamps();

            $table->foreign('demand_id')->references('id')->on('demands')->onDelete('cascade');
            // Assuming sitters are users for now. Normally this links to SitterProfile.
            $table->foreign('sitter_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('demand_id');
            $table->uuid('sitter_id');
            $table->string('status');
            $table->decimal('total_price', 8, 2);
            $table->timestamps();

            $table->foreign('demand_id')->references('id')->on('demands')->onDelete('cascade');
            $table->foreign('sitter_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('bids');
        Schema::dropIfExists('demand_pet');
        Schema::dropIfExists('demands');
    }
};
