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
        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('titulo')->nullable();
            $table->integer('edits')->default(0);
            $table->enum('state', ['0', '1'])->default('0');
            $table->integer('num_scenario')->default(1); // Nuevo campo para identificar el escenario
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scenarios');
    }
};
