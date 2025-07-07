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
    Schema::create('race_control', function (Blueprint $table) {
        $table->id(); // PK

        $table->foreignId('session_id')->constrained('sessions');

        $table->bigInteger('timestamp'); // in ms
        $table->string('event_type'); // e.g. "Yellow Flag", "SC Deployed"
        $table->text('message'); // full message content

        $table->timestamps();

        $table->index(['session_id', 'timestamp']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_controls');
    }
};
