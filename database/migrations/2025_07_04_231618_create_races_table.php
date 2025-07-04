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
        Schema::create('races', function (Blueprint $table) {
    $table->id();
    $table->foreignId('season_id')->constrained()->cascadeOnDelete();
    $table->foreignId('circuit_id')->constrained('circuits')->cascadeOnDelete();
    $table->string('name');
    $table->date('date');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
