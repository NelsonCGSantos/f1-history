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
    Schema::create('sessions', function (Blueprint $table) {
        $table->id(); // PK

        $table->foreignId('meeting_id')->constrained('meetings'); // FK → meetings.id
        $table->string('type'); // e.g. FP1, Quali, Race
        $table->bigInteger('session_key')->unique(); // OpenF1 API

        $table->timestamp('start_time')->nullable();
        $table->timestamp('end_time')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
