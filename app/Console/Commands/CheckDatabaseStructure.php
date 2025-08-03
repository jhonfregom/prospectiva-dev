<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckDatabaseStructure extends Command
{
    protected $signature = 'db:check-structure';
    protected $description = 'Verificar que todas las tablas tengan la estructura correcta';

    public function handle()
    {
        $this->info('🔍 Verificando estructura de la base de datos...');
        
        $tables = [
            'users' => [
                'id', 'document_id', 'first_name', 'last_name', 'user', 'password',
                'role', 'status_users_id', 'user_type', 'names', 'surnames',
                'company_name', 'nit', 'city_region', 'economic_sector',
                'data_authorization', 'city', 'registration_type',
                'created_at', 'updated_at', 'remember_token'
            ],
            'status_users' => [
                'id', 'name', 'description', 'created_at', 'updated_at'
            ],
            'economic_sectors' => [
                'id', 'name', 'description', 'is_active', 'sort_order',
                'created_at', 'updated_at'
            ],
            'variables' => [
                'id', 'id_variable', 'name_variable', 'description', 'score',
                'user_id', 'state', 'now_condition', 'tried_id',
                'created_at', 'updated_at'
            ],
            'zones' => [
                'id', 'name_zones', 'created_at', 'updated_at'
            ],
            'hypothesis' => [
                'id', 'id_variable', 'zone_id', 'name_hypothesis', 'description',
                'user_id', 'state', 'tried_id', 'created_at', 'updated_at'
            ],
            'scenarios' => [
                'id', 'user_id', 'titulo', 'edits', 'state', 'num_scenario',
                'tried_id', 'created_at', 'updated_at'
            ],
            'conclusions' => [
                'id', 'component_practice', 'component_practice_edits',
                'actuality', 'actuality_edits', 'aplication', 'aplication_edits',
                'user_id', 'state', 'tried_id', 'created_at', 'updated_at'
            ],
            'traceability' => [
                'id', 'tried', 'variables', 'matriz', 'maps', 'hypothesis',
                'shwartz', 'conditions', 'scenarios', 'conclusions', 'results',
                'state', 'user_id', 'created_at', 'updated_at'
            ],
            'notes' => [
                'id', 'user_id', 'title', 'content', 'created_at', 'updated_at'
            ],
            'matriz' => [
                'id', 'id_matriz', 'id_variable', 'id_resp_depen', 'id_resp_influ',
                'user_id', 'state', 'created_at', 'updated_at'
            ],
            'variables_map_analiyis' => [
                'id', 'description', 'score', 'zone_id', 'user_id',
                'state', 'tried_id', 'created_at', 'updated_at'
            ]
        ];
        
        $allGood = true;
        
        foreach ($tables as $tableName => $expectedColumns) {
            $this->info("\n📋 Verificando tabla: {$tableName}");
            
            if (!Schema::hasTable($tableName)) {
                $this->error("   ❌ Tabla {$tableName} no existe");
                $allGood = false;
                continue;
            }
            
            $actualColumns = Schema::getColumnListing($tableName);
            $missingColumns = array_diff($expectedColumns, $actualColumns);
            $extraColumns = array_diff($actualColumns, $expectedColumns);
            
            if (empty($missingColumns) && empty($extraColumns)) {
                $this->info("   ✅ Estructura correcta");
            } else {
                $allGood = false;
                if (!empty($missingColumns)) {
                    $this->warn("   ⚠️  Columnas faltantes: " . implode(', ', $missingColumns));
                }
                if (!empty($extraColumns)) {
                    $this->warn("   ⚠️  Columnas extra: " . implode(', ', $extraColumns));
                }
            }
            
            // Verificar claves foráneas
            $this->checkForeignKeys($tableName);
        }
        
        if ($allGood) {
            $this->info("\n🎉 ¡Todas las tablas tienen la estructura correcta!");
        } else {
            $this->warn("\n⚠️  Se encontraron problemas en la estructura de algunas tablas");
        }
        
        return 0;
    }
    
    private function checkForeignKeys($tableName)
    {
        $foreignKeys = [
            'users' => [
                'status_users_id' => 'status_users',
                'economic_sector' => 'economic_sectors'
            ],
            'variables' => [
                'user_id' => 'users',
                'tried_id' => 'traceability'
            ],
            'hypothesis' => [
                'id_variable' => 'variables',
                'zone_id' => 'zones',
                'user_id' => 'users',
                'tried_id' => 'traceability'
            ],
            'scenarios' => [
                'user_id' => 'users',
                'tried_id' => 'traceability'
            ],
            'conclusions' => [
                'user_id' => 'users',
                'tried_id' => 'traceability'
            ],
            'notes' => [
                'user_id' => 'users'
            ],
            'matriz' => [
                'user_id' => 'users',
                'variable_id' => 'variables',
                'zone_id' => 'zones',
                'tried_id' => 'traceability'
            ],
            'variables_map_analiyis' => [
                'user_id' => 'users',
                'zone_id' => 'zones',
                'tried_id' => 'traceability'
            ]
        ];
        
        if (isset($foreignKeys[$tableName])) {
            foreach ($foreignKeys[$tableName] as $column => $referencedTable) {
                if (Schema::hasColumn($tableName, $column)) {
                    $this->line("   🔗 Clave foránea {$column} → {$referencedTable}");
                }
            }
        }
    }
} 