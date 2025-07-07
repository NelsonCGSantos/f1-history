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
        $table->bigInteger('lap_time');
        $table->bigInteger('sector_1_time')->nullable();
        $table->bigInteger('sector_2_time')->nullable();
        $table->bigInteger('sector_3_time')->nullable();

        $table->timestamps();

        $table->unique(['session_id', 'driver_id', 'lap_number']); // ensure no duplicates
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
