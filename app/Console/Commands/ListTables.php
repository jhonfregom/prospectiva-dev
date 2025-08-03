<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListTables extends Command
{
    protected $signature = 'db:list-tables';
    protected $description = 'Listar todas las tablas de la base de datos';

    public function handle()
    {
        $this->info('📋 Tablas en la base de datos:');
        
        try {
            $tables = DB::select('SHOW TABLES');
            
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                $this->line("   - {$tableName}");
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error al listar tablas:');
            $this->line("   {$e->getMessage()}");
        }
        
        return 0;
    }
} 