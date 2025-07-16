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
            $table->id();
            $table->foreignId('session_id')->constrained();
            $table->foreignId('driver_id')->constrained();

            // Use `date` to store the ISO timestamp from the API
            $table->timestamp('date')->nullable();

            $table->integer('position');

            $table->timestamps();

            // ensure uniqueness per driver/time
            $table->unique(['session_id', 'driver_id', 'date']);
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
