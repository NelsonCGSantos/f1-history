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
    Schema::create('weather', function (Blueprint $table) {
        $table->id(); // PK

        $table->foreignId('session_id')->constrained('sessions');

        $table->bigInteger('timestamp'); // in ms
        $table->decimal('air_temp', 5, 2)->nullable();     // Celsius
        $table->decimal('track_temp', 5, 2)->nullable();   // Celsius
        $table->decimal('humidity', 5, 2)->nullable();     // %
        $table->decimal('wind_speed', 6, 2)->nullable();   // km/h
        $table->decimal('precipitation', 6, 3)->nullable(); // mm

        $table->timestamps();

        $table->index(['session_id', 'timestamp']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather');
    }
};
