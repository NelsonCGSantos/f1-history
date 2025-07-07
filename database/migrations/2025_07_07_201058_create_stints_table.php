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
    Schema::create('stints', function (Blueprint $table) {
        $table->id(); // PK

        $table->foreignId('session_id')->constrained('sessions');
        $table->foreignId('driver_id')->constrained('drivers');

        $table->integer('start_lap');
        $table->integer('end_lap');
        $table->string('tire_compound'); // soft, medium, hard

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stints');
    }
};
