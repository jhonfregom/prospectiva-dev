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
        // Deshabilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Corregir la tabla traceability para que tenga auto-increment correcto
        DB::statement('ALTER TABLE traceability MODIFY id INT AUTO_INCREMENT');
        
        // Verificar que el auto-increment esté funcionando
        DB::statement('ALTER TABLE traceability AUTO_INCREMENT = 1');
        
        // Habilitar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario revertir este cambio
    }
};
