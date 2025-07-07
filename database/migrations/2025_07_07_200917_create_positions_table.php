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
    Schema::create('positions', function (Blueprint $table) {
        $table->id(); // PK

        $table->foreignId('session_id')->constrained('sessions');
        $table->foreignId('driver_id')->constrained('drivers');

        $table->bigInteger('timestamp'); // in ms
        $table->integer('position');     // track position

        $table->timestamps();

        $table->index(['session_id', 'driver_id', 'timestamp']); // optimize queries
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
