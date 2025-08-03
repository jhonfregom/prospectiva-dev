<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero eliminar la foreign key si existe
        try {
            DB::statement('ALTER TABLE notes DROP FOREIGN KEY notes_traceability_id_foreign');
        } catch (\Exception $e) {
            // La foreign key no existe, continuar
        }
        
        // Luego eliminar el índice compuesto si existe
        try {
            DB::statement('ALTER TABLE notes DROP INDEX notes_user_id_traceability_id_index');
        } catch (\Exception $e) {
            // El índice no existe, continuar
        }
        
        // Finalmente eliminar la columna
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('traceability_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            // Agregar la columna traceability_id de vuelta
            $table->integer('traceability_id')->nullable()->after('user_id');
            
            // Crear el índice compuesto original
            $table->index(['user_id', 'traceability_id']);
            
            // Agregar la foreign key de vuelta
            $table->foreign('traceability_id')->references('id')->on('traceability')->onDelete('cascade');
        });
    }
};
