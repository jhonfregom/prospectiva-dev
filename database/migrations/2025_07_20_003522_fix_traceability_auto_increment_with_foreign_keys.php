<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        
        $foreignKeys = DB::select("
            SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_NAME = 'traceability' 
            AND REFERENCED_COLUMN_NAME = 'id'
        ");

        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE {$fk->TABLE_NAME} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                
            }
        }

        DB::statement("ALTER TABLE traceability MODIFY id INT NOT NULL");

        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("
                    ALTER TABLE {$fk->TABLE_NAME} 
                    ADD CONSTRAINT {$fk->CONSTRAINT_NAME} 
                    FOREIGN KEY (tried_id) REFERENCES traceability(id)
                    ON UPDATE NO ACTION ON DELETE NO ACTION
                ");
            } catch (\Exception $e) {
                
            }
        }
    }

    public function down(): void
    {
        
        $foreignKeys = DB::select("
            SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_NAME = 'traceability' 
            AND REFERENCED_COLUMN_NAME = 'id'
        ");

        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE {$fk->TABLE_NAME} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            } catch (\Exception $e) {
                
            }
        }

        DB::statement("ALTER TABLE traceability MODIFY id INT NOT NULL AUTO_INCREMENT");

        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("
                    ALTER TABLE {$fk->TABLE_NAME} 
                    ADD CONSTRAINT {$fk->CONSTRAINT_NAME} 
                    FOREIGN KEY (tried_id) REFERENCES traceability(id)
                    ON UPDATE NO ACTION ON DELETE NO ACTION
                ");
            } catch (\Exception $e) {
                
            }
        }
    }
};