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
        // Obtener todas las claves foráneas que referencian traceability.id
        $foreignKeys = DB::select("
            SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_NAME = 'traceability' 
            AND REFERENCED_COLUMN_NAME = 'id'
        ");

        // Eliminar las claves foráneas encontradas
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE {$fk->TABLE_NAME} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                // Ignorar errores si la clave foránea no existe
            }
        }

        // Modificar la tabla traceability para quitar auto-increment
        DB::statement("ALTER TABLE traceability MODIFY id INT NOT NULL");

        // Restaurar las claves foráneas
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("
                    ALTER TABLE {$fk->TABLE_NAME} 
                    ADD CONSTRAINT {$fk->CONSTRAINT_NAME} 
                    FOREIGN KEY (tried_id) REFERENCES traceability(id)
                    ON UPDATE NO ACTION ON DELETE NO ACTION
                ");
            } catch (\Exception $e) {
                // Ignorar errores si la clave foránea ya existe
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener todas las claves foráneas que referencian traceability.id
        $foreignKeys = DB::select("
            SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_NAME = 'traceability' 
            AND REFERENCED_COLUMN_NAME = 'id'
        ");

        // Eliminar las claves foráneas encontradas
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE {$fk->TABLE_NAME} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                // Ignorar errores si la clave foránea no existe
            }
        }

        // Restaurar auto-increment en traceability
        DB::statement("ALTER TABLE traceability MODIFY id INT NOT NULL AUTO_INCREMENT");

        // Restaurar las claves foráneas
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("
                    ALTER TABLE {$fk->TABLE_NAME} 
                    ADD CONSTRAINT {$fk->CONSTRAINT_NAME} 
                    FOREIGN KEY (tried_id) REFERENCES traceability(id)
                    ON UPDATE NO ACTION ON DELETE NO ACTION
                ");
            } catch (\Exception $e) {
                // Ignorar errores si la clave foránea ya existe
            }
        }
    }
};
