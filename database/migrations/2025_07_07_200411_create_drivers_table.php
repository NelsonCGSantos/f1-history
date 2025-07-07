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
    Schema::create('drivers', function (Blueprint $table) {
        $table->id(); // PK

        $table->integer('driver_number'); // 44
        $table->string('name'); // Full name
        $table->string('team_name')->nullable(); //  Mercedes
        $table->string('nationality')->nullable(); // British
        $table->string('abbreviation')->nullable(); // HAM

        $table->timestamps(); // created_at, updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
