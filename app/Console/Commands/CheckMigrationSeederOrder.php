<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckMigrationSeederOrder extends Command
{
    protected $signature = 'check:migration-seeder-order';
    protected $description = 'Check that migrations and seeders are executed in the correct order';

    public function handle()
    {
        $this->info("🔍 Verificando orden correcto de migraciones y seeders...");

        // Definir el orden correcto de migraciones
        $this->info("\n📋 Orden correcto de migraciones:");
        $correctMigrationOrder = [
            '0001_01_01_000001_create_cache_table',
            '0001_01_01_000002_create_jobs_table',
            '2025_06_04_214520_create_status_users_table',      // Primero status_users
            '2025_06_04_214521_create_users_table',            // Luego users (depende de status_users)
            '2025_06_08_005347_create_traceability_table',
            '2025_06_08_005348_create_notes_table',
            '2025_06_09_011538_create_variables_table',
            '2025_06_11_030500_modify_state_column_in_variables_table',
            '2025_06_18_013037_create_matriz_table',
            '2025_06_28_030618_create_zonas_table',
            '2025_06_28_030753_create_variables_mapa_analisis_table',
            '2025_06_29_210918_create_hypothesis_table',
            '2025_06_30_051030_add_state_fields_to_hypothesis_table',
            '2025_06_30_051552_add_individual_lock_fields_to_hypothesis_table',
            '2025_07_02_234157_add_secondary_hypotheses_to_hypothesis_table',
            '2025_07_03_022624_create_scenarios_table',
            '2025_07_10_000002_add_years_and_edits_fields_to_scenarios_table',
            '2025_07_11_003239_create_conclusions_table',
            '2025_07_13_000001_update_foreign_keys_to_cascade',
            '2025_07_14_000002_add_edit_fields_to_variables_table',
            '2025_07_19_234236_add_edits_fields_to_variables_map_analisis_table',
            '2025_07_20_002620_add_edits_field_to_hypothesis_table',
            '2025_07_20_003522_fix_traceability_auto_increment_with_foreign_keys',
            '2025_07_20_181609_add_tried_id_to_matriz_table',
            '2025_07_31_030000_add_missing_fields_to_users_table',
            '2025_07_31_031208_change_document_id_to_bigint',
            '2025_08_03_020504_create_economic_sectors_table',  // Luego economic_sectors
            '2025_08_03_021457_change_economic_sector_to_foreign_key', // Luego foreign key
            '2025_08_03_033556_remove_traceability_id_from_notes_table',
            '2025_08_03_200025_fix_traceability_table_auto_increment',
            '2025_08_03_230449_add_activation_fields_to_users_table',
            '2025_08_04_013651_create_sessions_table',
            '2025_08_06_050000_add_city_to_users_table',
            '2025_08_06_193821_add_password_reset_fields_to_users_table',
            '2025_08_07_190644_add_activation_token_fields_to_users_table'
        ];

        foreach ($correctMigrationOrder as $index => $migration) {
            $this->line("   " . ($index + 1) . ". {$migration}");
        }

        // Verificar estado actual de migraciones
        $this->info("\n📊 Estado actual de migraciones:");
        $this->call('migrate:status');

        // Definir el orden correcto de seeders
        $this->info("\n🌱 Orden correcto de seeders:");
        $correctSeederOrder = [
            'StateUserSeeder',        // Primero status_users
            'EconomicSectorSeeder',   // Luego economic_sectors
            'AdminUserSeeder',        // Luego admin user
            'DatabaseSeeder',         // Finalmente otros datos
            'TestDataSeeder'          // Opcional: datos de prueba
        ];

        foreach ($correctSeederOrder as $index => $seeder) {
            $this->line("   " . ($index + 1) . ". {$seeder}");
        }

        // Verificar dependencias críticas
        $this->info("\n🔗 Verificando dependencias críticas...");
        $this->checkCriticalDependencies();

        // Verificar orden de ejecución recomendado
        $this->info("\n📋 Orden de ejecución recomendado:");
        $this->showRecommendedExecutionOrder();

        // Verificar si hay problemas de orden
        $this->info("\n⚠️ Verificando problemas de orden...");
        $this->checkOrderIssues();

        $this->info("\n🎉 Verificación de orden completada");
        return 0;
    }

    private function checkCriticalDependencies()
    {
        // Verificar que status_users existe antes que users
        if (Schema::hasTable('status_users') && Schema::hasTable('users')) {
            $this->info("✅ status_users existe antes que users");
        } else {
            $this->error("❌ Problema: users debe crearse después de status_users");
        }

        // Verificar que economic_sectors existe antes de la foreign key
        if (Schema::hasTable('economic_sectors')) {
            $this->info("✅ economic_sectors existe");
            
            // Verificar si la foreign key está configurada correctamente
            try {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'users'
                    AND REFERENCED_TABLE_NAME = 'economic_sectors'
                ");
                
                if (count($foreignKeys) > 0) {
                    $this->info("✅ Foreign key users.economic_sector -> economic_sectors.id configurada");
                } else {
                    $this->warn("⚠️ Foreign key users.economic_sector no está configurada");
                }
            } catch (\Exception $e) {
                $this->warn("⚠️ No se pudo verificar foreign key: " . $e->getMessage());
            }
        } else {
            $this->error("❌ economic_sectors debe existir antes de configurar foreign keys");
        }

        // Verificar que status_users tiene datos antes de crear usuarios
        if (Schema::hasTable('status_users')) {
            $statusUsersCount = DB::table('status_users')->count();
            if ($statusUsersCount > 0) {
                $this->info("✅ status_users tiene {$statusUsersCount} registros");
            } else {
                $this->warn("⚠️ status_users está vacía - ejecuta StateUserSeeder primero");
            }
        }

        // Verificar que economic_sectors tiene datos
        if (Schema::hasTable('economic_sectors')) {
            $economicSectorsCount = DB::table('economic_sectors')->count();
            if ($economicSectorsCount > 0) {
                $this->info("✅ economic_sectors tiene {$economicSectorsCount} registros");
            } else {
                $this->warn("⚠️ economic_sectors está vacía - ejecuta EconomicSectorSeeder primero");
            }
        }
    }

    private function showRecommendedExecutionOrder()
    {
        $this->info("Para una instalación limpia:");
        $this->line("   1. php artisan migrate:fresh");
        $this->line("   2. php artisan db:seed --class=StateUserSeeder");
        $this->line("   3. php artisan db:seed --class=EconomicSectorSeeder");
        $this->line("   4. php artisan db:seed --class=AdminUserSeeder");
        $this->line("   5. php artisan db:seed --class=DatabaseSeeder");
        $this->line("   6. php artisan db:seed --class=TestDataSeeder (opcional)");
        
        $this->info("\nO usar el comando automático:");
        $this->line("   php artisan setup:project");
        
        $this->info("\nPara verificar problemas específicos:");
        $this->line("   php artisan diagnose:migration-issues");
        $this->line("   php artisan fix:test-data-seeder");
    }

    private function checkOrderIssues()
    {
        $issues = [];

        // Verificar si hay usuarios con foreign keys inválidas
        if (Schema::hasTable('users') && Schema::hasTable('status_users')) {
            $invalidStatusUsers = DB::table('users')
                ->leftJoin('status_users', 'users.status_users_id', '=', 'status_users.id')
                ->whereNull('status_users.id')
                ->whereNotNull('users.status_users_id')
                ->count();
            
            if ($invalidStatusUsers > 0) {
                $issues[] = "❌ {$invalidStatusUsers} usuarios tienen status_users_id inválido";
            }
        }

        if (Schema::hasTable('users') && Schema::hasTable('economic_sectors')) {
            $invalidEconomicSectors = DB::table('users')
                ->leftJoin('economic_sectors', 'users.economic_sector', '=', 'economic_sectors.id')
                ->whereNull('economic_sectors.id')
                ->whereNotNull('users.economic_sector')
                ->count();
            
            if ($invalidEconomicSectors > 0) {
                $issues[] = "❌ {$invalidEconomicSectors} usuarios tienen economic_sector inválido";
            }
        }

        // Verificar si las tablas críticas están vacías
        if (Schema::hasTable('status_users') && DB::table('status_users')->count() === 0) {
            $issues[] = "❌ Tabla status_users está vacía - ejecuta StateUserSeeder";
        }

        if (Schema::hasTable('economic_sectors') && DB::table('economic_sectors')->count() === 0) {
            $issues[] = "❌ Tabla economic_sectors está vacía - ejecuta EconomicSectorSeeder";
        }

        if (empty($issues)) {
            $this->info("✅ No se encontraron problemas de orden");
        } else {
            $this->error("Problemas encontrados:");
            foreach ($issues as $issue) {
                $this->line("   {$issue}");
            }
            
            $this->info("\n💡 Soluciones:");
            $this->line("   - Ejecuta: php artisan fix:test-data-seeder");
            $this->line("   - O ejecuta: php artisan migrate:fresh --seed");
        }
    }
}
