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
        Schema::create('results', function (Blueprint $table) {
    $table->id();
    $table->foreignId('race_id')->constrained()->cascadeOnDelete();
    $table->string('driver_id');
    $table->string('constructor_id');
    $table->tinyInteger('grid');
    $table->tinyInteger('position');
    $table->smallInteger('laps');
    $table->string('status');
    $table->string('time')->nullable();
    $table->timestamps();
    $table->index(['race_id','driver_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
