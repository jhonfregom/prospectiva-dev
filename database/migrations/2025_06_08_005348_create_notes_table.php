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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('traceability_id')->nullable(); // Para asociar con una ruta específica
            $table->text('content'); // El contenido del texto
            $table->string('title')->nullable(); // Título opcional de la nota
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'traceability_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('traceability_id')->references('id')->on('traceability')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
}; 