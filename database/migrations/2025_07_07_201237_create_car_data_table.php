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
    Schema::create('car_data', function (Blueprint $table) {
        $table->id(); // Primary key

        $table->foreignId('session_id')->constrained('sessions');
        $table->foreignId('driver_id')->constrained('drivers');

        $table->bigInteger('timestamp'); // Milliseconds
        $table->decimal('speed', 6, 2)->nullable();     // up to 999.99 km/h
        $table->decimal('throttle', 5, 2)->nullable();  // up to 100.00
        $table->boolean('brake')->nullable();           // true/false
        $table->boolean('drs')->nullable();             // true/false
        $table->integer('gear')->nullable();            // -1 to 8, or whatever range applies

        $table->timestamps();

        //for better query performance on session + driver + time
        $table->index(['session_id', 'driver_id', 'timestamp']);
    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_data');
    }
};
