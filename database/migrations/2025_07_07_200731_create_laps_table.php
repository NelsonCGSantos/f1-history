<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laps', function (Blueprint $table) {
            $table->id(); // PK

            $table->foreignId('session_id')->constrained('sessions');
            $table->foreignId('driver_id')->constrained('drivers');

            $table->integer('lap_number');
            // store durations in seconds with millisecond precision
            $table->decimal('lap_time', 6, 3)->nullable();
            $table->decimal('sector_1_time', 6, 3)->nullable();
            $table->decimal('sector_2_time', 6, 3)->nullable();
            $table->decimal('sector_3_time', 6, 3)->nullable();

            // intermediate speeds
            $table->unsignedInteger('i1_speed')->nullable();
            $table->unsignedInteger('i2_speed')->nullable();
            $table->unsignedInteger('speed_trap')->nullable();

            // pit-out flag
            $table->boolean('is_pit_out')->default(false);

            // mini-sector segments stored as JSON arrays
            $table->json('segments_sector_1')->nullable();
            $table->json('segments_sector_2')->nullable();
            $table->json('segments_sector_3')->nullable();

            $table->timestamps();

            $table->unique(['session_id', 'driver_id', 'lap_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laps');
    }
};
