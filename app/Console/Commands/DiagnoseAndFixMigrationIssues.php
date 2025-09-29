<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiagnoseAndFixMigrationIssues extends Command
{
    protected $signature = 'diagnose:migration-issues';
    protected $description = 'Diagnose and fix migration and seeder issues';

    public function handle()
    {
        $this->info("🔍 Diagnóstico de problemas de migraciones y seeders...");

        // Verificar estado de migraciones
        $this->info("\n📋 Verificando estado de migraciones...");
        $this->call('migrate:status');

        // Verificar tablas críticas
        $this->info("\n🏗️ Verificando tablas críticas...");
        $criticalTables = [
            'status_users',
            'users',
            'economic_sectors',
            'traceability',
            'notes',
            'variables',
            'matriz',
            'zones',
            'variables_map_analiyis',
            'hypothesis',
            'scenarios',
            'conclusions'
        ];

        foreach ($criticalTables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $this->info("✅ Tabla {$table} existe con {$count} registros");
            } else {
                $this->error("❌ Tabla {$table} NO existe");
            }
        }

        // Verificar datos en status_users
        $this->info("\n👥 Verificando datos en status_users...");
        if (Schema::hasTable('status_users')) {
            $statusUsers = DB::table('status_users')->get();
            if ($statusUsers->count() > 0) {
                foreach ($statusUsers as $status) {
                    $state = $status->state ?? 'N/A';
                    $this->info("   - ID: {$status->id}, Estado: {$state}");
                }
            } else {
                $this->warn("⚠️ Tabla status_users está vacía");
            }
        }

        // Verificar datos en economic_sectors
        $this->info("\n🏭 Verificando datos en economic_sectors...");
        if (Schema::hasTable('economic_sectors')) {
            $economicSectors = DB::table('economic_sectors')->get();
            if ($economicSectors->count() > 0) {
                foreach ($economicSectors as $sector) {
                    $name = $sector->name ?? 'N/A';
                    $this->info("   - ID: {$sector->id}, Nombre: {$name}");
                }
            } else {
                $this->warn("⚠️ Tabla economic_sectors está vacía");
            }
        }

        // Verificar foreign keys
        $this->info("\n🔗 Verificando foreign keys...");
        $this->checkForeignKeys();

        // Preguntar si quiere ejecutar las migraciones
        if ($this->confirm('¿Quieres ejecutar las migraciones pendientes?')) {
            $this->info("\n🔄 Ejecutando migraciones...");
            $this->call('migrate');
        }

        // Preguntar si quiere ejecutar los seeders
        if ($this->confirm('¿Quieres ejecutar los seeders?')) {
            $this->info("\n🌱 Ejecutando seeders...");
            
            // Ejecutar seeders en orden correcto
            $seeders = [
                'StateUserSeeder',
                'EconomicSectorSeeder',
                'AdminUserSeeder',
                'DatabaseSeeder'
            ];

            foreach ($seeders as $seeder) {
                $this->info("   Ejecutando {$seeder}...");
                try {
                    $this->call('db:seed', ['--class' => $seeder]);
                    $this->info("   ✅ {$seeder} ejecutado correctamente");
                } catch (\Exception $e) {
                    $this->error("   ❌ Error en {$seeder}: " . $e->getMessage());
                }
            }
        }

        // Verificación final
        $this->info("\n✅ Verificación final...");
        $this->verifyFinalState();

        $this->info("\n🎉 Diagnóstico completado");
        return 0;
    }

    private function checkForeignKeys()
    {
        try {
            // Verificar foreign key de users a status_users
            if (Schema::hasTable('users') && Schema::hasTable('status_users')) {
                $usersWithInvalidStatus = DB::table('users')
                    ->leftJoin('status_users', 'users.status_users_id', '=', 'status_users.id')
                    ->whereNull('status_users.id')
                    ->count();
                
                if ($usersWithInvalidStatus > 0) {
                    $this->warn("⚠️ {$usersWithInvalidStatus} usuarios tienen status_users_id inválido");
                } else {
                    $this->info("✅ Foreign key users.status_users_id está correcta");
                }
            }

            // Verificar foreign key de users a economic_sectors
            if (Schema::hasTable('users') && Schema::hasTable('economic_sectors')) {
                $usersWithInvalidSector = DB::table('users')
                    ->leftJoin('economic_sectors', 'users.economic_sector', '=', 'economic_sectors.id')
                    ->whereNull('economic_sectors.id')
                    ->whereNotNull('users.economic_sector')
                    ->count();
                
                if ($usersWithInvalidSector > 0) {
                    $this->warn("⚠️ {$usersWithInvalidSector} usuarios tienen economic_sector inválido");
                } else {
                    $this->info("✅ Foreign key users.economic_sector está correcta");
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Error verificando foreign keys: " . $e->getMessage());
        }
    }

    private function verifyFinalState()
    {
        // Verificar que las tablas críticas tengan datos
        $criticalTablesWithData = [
            'status_users' => 1,
            'economic_sectors' => 1,
            'users' => 0 // Puede estar vacía inicialmente
        ];

        foreach ($criticalTablesWithData as $table => $minCount) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                if ($count >= $minCount) {
                    $this->info("✅ Tabla {$table} tiene {$count} registros");
                } else {
                    $this->warn("⚠️ Tabla {$table} tiene solo {$count} registros (mínimo: {$minCount})");
                }
            } else {
                $this->error("❌ Tabla {$table} no existe");
            }
        }

        // Verificar que no haya foreign key violations
        $this->info("\n🔍 Verificando integridad de datos...");
        try {
            $violations = DB::table('users')
                ->leftJoin('status_users', 'users.status_users_id', '=', 'status_users.id')
                ->whereNull('status_users.id')
                ->whereNotNull('users.status_users_id')
                ->count();
            
            if ($violations > 0) {
                $this->error("❌ {$violations} violaciones de foreign key encontradas");
            } else {
                $this->info("✅ No se encontraron violaciones de foreign key");
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ No se pudo verificar integridad: " . $e->getMessage());
        }
    }
}
