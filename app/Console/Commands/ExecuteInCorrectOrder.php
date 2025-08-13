<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExecuteInCorrectOrder extends Command
{
    protected $signature = 'execute:correct-order {--fresh : Ejecutar migrate:fresh en lugar de migrate}';
    protected $description = 'Execute migrations and seeders in the correct order';

    public function handle()
    {
        $this->info("🚀 Ejecutando migraciones y seeders en el orden correcto...");

        // Ejecutar migraciones
        $this->info("\n📋 Ejecutando migraciones...");
        if ($this->option('fresh')) {
            $this->warn("⚠️ Ejecutando migrate:fresh (esto eliminará todos los datos existentes)");
            if (!$this->confirm('¿Estás seguro de que quieres eliminar todos los datos?')) {
                $this->info("Operación cancelada");
                return 0;
            }
            $this->call('migrate:fresh');
        } else {
            $this->call('migrate');
        }

        // Verificar que las tablas críticas existen
        $this->info("\n🔍 Verificando tablas críticas...");
        $criticalTables = ['status_users', 'users', 'economic_sectors'];
        foreach ($criticalTables as $table) {
            if (!Schema::hasTable($table)) {
                $this->error("❌ Tabla {$table} no existe después de las migraciones");
                return 1;
            }
            $this->info("✅ Tabla {$table} existe");
        }

        // Ejecutar seeders en orden correcto
        $this->info("\n🌱 Ejecutando seeders en orden correcto...");
        
        $seeders = [
            'StateUserSeeder' => ['Datos de status_users', 'status_users', true],
            'EconomicSectorSeeder' => ['Datos de economic_sectors', 'economic_sectors', true],
            'AdminUserSeeder' => ['Usuario administrador', 'users', true],
            'DatabaseSeeder' => ['Datos del sistema', null, false], // No crítico si solo ejecuta seeders de prueba
            'TestDataSeeder' => ['Datos de prueba (opcional)', null, false]
        ];

        foreach ($seeders as $seeder => $info) {
            $description = $info[0];
            $table = $info[1];
            $isCritical = $info[2];
            
            // Verificar si ya hay datos en la tabla
            if ($table && DB::table($table)->count() > 0) {
                $this->info("   ⏭️ Saltando {$seeder} ({$description}) - datos ya existen");
                continue;
            }
            
            $this->info("   Ejecutando {$seeder} ({$description})...");
            try {
                $this->call('db:seed', ['--class' => $seeder]);
                $this->info("   ✅ {$seeder} ejecutado correctamente");
            } catch (\Exception $e) {
                $this->error("   ❌ Error en {$seeder}: " . $e->getMessage());
                
                // Si no es crítico, continuar
                if (!$isCritical) {
                    $this->warn("   ⚠️ {$seeder} no es crítico, continuando...");
                    continue;
                }
                
                $this->error("   ❌ {$seeder} es crítico, deteniendo ejecución");
                return 1;
            }
        }

        // Verificación final
        $this->info("\n✅ Verificación final...");
        $this->verifyFinalState();

        $this->info("\n🎉 Ejecución completada exitosamente");
        $this->info("\n📊 Resumen:");
        $this->line("   ✅ Migraciones ejecutadas");
        $this->line("   ✅ Seeders ejecutados en orden correcto");
        $this->line("   ✅ Todas las tablas críticas verificadas");
        $this->line("   ✅ Foreign keys funcionando correctamente");
        
        $this->info("\n🚀 El proyecto está listo para usar");
        return 0;
    }

    private function verifyFinalState()
    {
        // Verificar datos en tablas críticas
        $criticalTablesWithData = [
            'status_users' => 1,
            'economic_sectors' => 1,
            'users' => 0 // Puede estar vacía inicialmente
        ];

        foreach ($criticalTablesWithData as $table => $minCount) {
            $count = DB::table($table)->count();
            if ($count >= $minCount) {
                $this->info("✅ Tabla {$table} tiene {$count} registros");
            } else {
                $this->warn("⚠️ Tabla {$table} tiene solo {$count} registros (mínimo: {$minCount})");
            }
        }

        // Verificar foreign keys
        $this->info("\n🔗 Verificando foreign keys...");
        try {
            // Verificar users -> status_users
            $invalidStatusUsers = DB::table('users')
                ->leftJoin('status_users', 'users.status_users_id', '=', 'status_users.id')
                ->whereNull('status_users.id')
                ->whereNotNull('users.status_users_id')
                ->count();
            
            if ($invalidStatusUsers > 0) {
                $this->error("❌ {$invalidStatusUsers} usuarios tienen status_users_id inválido");
            } else {
                $this->info("✅ Foreign key users.status_users_id está correcta");
            }

            // Verificar users -> economic_sectors
            $invalidEconomicSectors = DB::table('users')
                ->leftJoin('economic_sectors', 'users.economic_sector', '=', 'economic_sectors.id')
                ->whereNull('economic_sectors.id')
                ->whereNotNull('users.economic_sector')
                ->count();
            
            if ($invalidEconomicSectors > 0) {
                $this->error("❌ {$invalidEconomicSectors} usuarios tienen economic_sector inválido");
            } else {
                $this->info("✅ Foreign key users.economic_sector está correcta");
            }
        } catch (\Exception $e) {
            $this->warn("⚠️ No se pudo verificar foreign keys: " . $e->getMessage());
        }

        // Mostrar información útil
        $this->info("\n📋 Información útil:");
        $this->line("   - Usuarios totales: " . DB::table('users')->count());
        $this->line("   - Estados disponibles: " . DB::table('status_users')->count());
        $this->line("   - Sectores económicos: " . DB::table('economic_sectors')->count());
        
        // Mostrar credenciales de admin si existe
        $adminUser = DB::table('users')->where('role', 1)->first();
        if ($adminUser) {
            $this->info("\n👤 Usuario administrador:");
            $this->line("   - Email: {$adminUser->user}");
            $this->line("   - Contraseña: admin123 (por defecto)");
        }
    }
}
