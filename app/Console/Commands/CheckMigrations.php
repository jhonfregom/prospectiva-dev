<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckMigrations extends Command
{
    protected $signature = 'check:migrations';
    protected $description = 'Check the status of all migrations and detect potential issues';

    public function handle()
    {
        $this->info('🔍 Verificando estado de migraciones...');

        // Obtener todas las migraciones del directorio
        $migrationFiles = glob(database_path('migrations/*.php'));
        $migrationNames = [];
        
        foreach ($migrationFiles as $file) {
            $migrationNames[] = basename($file, '.php');
        }

        // Obtener migraciones ejecutadas
        $executedMigrations = DB::table('migrations')->pluck('migration')->toArray();

        $this->info("\n📊 Resumen:");
        $this->info("   - Archivos de migración encontrados: " . count($migrationNames));
        $this->info("   - Migraciones ejecutadas: " . count($executedMigrations));

        // Verificar migraciones faltantes
        $missingMigrations = array_diff($migrationNames, $executedMigrations);
        if (!empty($missingMigrations)) {
            $this->warn("\n⚠️ Migraciones no ejecutadas:");
            foreach ($missingMigrations as $migration) {
                $this->line("   - {$migration}");
            }
        } else {
            $this->info("\n✅ Todas las migraciones están ejecutadas");
        }

        // Verificar tablas importantes
        $this->info("\n🏗️ Verificando tablas importantes:");
        $importantTables = [
            'users',
            'economic_sectors',
            'traceability',
            'variables',
            'matriz',
            'notes',
            'hypothesis',
            'scenarios',
            'conclusions',
            'zones',
            'variables_map_analiyis'
        ];

        foreach ($importantTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->info("   ✅ {$table}: {$count} registros");
            } else {
                $this->error("   ❌ {$table}: Tabla no existe");
            }
        }

        // Verificar foreign keys importantes
        $this->info("\n🔗 Verificando foreign keys:");
        $this->checkForeignKey('users', 'economic_sector', 'economic_sectors', 'id');
        $this->checkForeignKey('users', 'status_users_id', 'status_users', 'id');

        $this->info("\n🎉 Verificación completada");
        return 0;
    }

    private function checkForeignKey($table, $column, $referencedTable, $referencedColumn)
    {
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = ? 
                AND COLUMN_NAME = ? 
                AND REFERENCED_TABLE_NAME = ?
                AND REFERENCED_COLUMN_NAME = ?
            ", [$table, $column, $referencedTable, $referencedColumn]);

            if (!empty($foreignKeys)) {
                $this->info("   ✅ {$table}.{$column} -> {$referencedTable}.{$referencedColumn}");
            } else {
                $this->warn("   ⚠️ {$table}.{$column} -> {$referencedTable}.{$referencedColumn} (no encontrada)");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando foreign key: {$e->getMessage()}");
        }
    }
}
