<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('start_lap');
            $table->unsignedInteger('end_lap');
            $table->string('tire_compound');
            $table->unsignedInteger('stint_number');
            $table->unsignedInteger('tyre_age_at_start');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stints');
    }
};
