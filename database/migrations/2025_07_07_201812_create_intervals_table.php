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
    Schema::create('intervals', function (Blueprint $table) {
        $table->id(); // PK

        $table->foreignId('session_id')->constrained('sessions');
        $table->foreignId('driver_id')->constrained('drivers');

        $table->bigInteger('timestamp'); // in ms
        $table->decimal('gap_to_leader', 6, 3)->nullable(); // time in seconds, like 5.238

        $table->timestamps();

        $table->index(['session_id', 'driver_id', 'timestamp']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervals');
    }
};
