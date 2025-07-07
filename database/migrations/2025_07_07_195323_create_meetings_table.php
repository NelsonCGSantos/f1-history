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
    Schema::create('meetings', function (Blueprint $table) {
        $table->id(); // bigIncrements, primary key

        $table->string('name'); // e.g. "Monaco Grand Prix"
        $table->integer('season_year'); // 2024
        $table->string('location')->nullable(); // e.g. Monte Carlo
        $table->string('country')->nullable(); // e.g. Monaco

        $table->date('start_date')->nullable();
        $table->date('end_date')->nullable();

        $table->timestamps(); // created_at, updated_at
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
