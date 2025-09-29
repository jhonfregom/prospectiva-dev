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
        Schema::table('users', function (Blueprint $table) {
            // Esta migración es redundante ya que los campos se crearon en la migración anterior
            // Se mantiene para compatibilidad con el historial de Git
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // No hay nada que revertir ya que no se agregaron campos en esta migración
        });
    }
};
