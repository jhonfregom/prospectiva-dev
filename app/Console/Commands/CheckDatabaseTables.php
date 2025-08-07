<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDatabaseTables extends Command
{
    protected $signature = 'db:check-tables';
    protected $description = 'Verificar todas las tablas de la base de datos';

    public function handle()
    {
        $this->info('🔍 Verificando todas las tablas de la base de datos...');

        $tables = DB::select('SHOW TABLES');
        
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $this->line("\n📋 Tabla: {$tableName}");

            try {
                $count = DB::table($tableName)->count();
                $this->line("   📊 Registros: {$count}");

                if ($tableName === 'notes') {
                    $notes = DB::table($tableName)->get();
                    foreach ($notes as $note) {
                        $this->line("   - ID: {$note->id} | Título: '{$note->title}' | User ID: {$note->user_id}");
                    }
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Error accediendo a la tabla: " . $e->getMessage());
            }
        }

        return 0;
    }
}