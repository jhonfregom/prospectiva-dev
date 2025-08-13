<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixTestDataSeeder extends Command
{
    protected $signature = 'fix:test-data-seeder';
    protected $description = 'Fix TestDataSeeder foreign key constraint issues';

    public function handle()
    {
        $this->info("🔧 Solucionando problemas del TestDataSeeder...");

        // Verificar si las tablas necesarias existen
        $this->info("\n📋 Verificando tablas necesarias...");
        
        if (!Schema::hasTable('status_users')) {
            $this->error("❌ Tabla status_users no existe. Ejecuta las migraciones primero.");
            return 1;
        }

        if (!Schema::hasTable('economic_sectors')) {
            $this->error("❌ Tabla economic_sectors no existe. Ejecuta las migraciones primero.");
            return 1;
        }

        // Verificar si hay datos en status_users
        $statusUsersCount = DB::table('status_users')->count();
        if ($statusUsersCount === 0) {
            $this->warn("⚠️ Tabla status_users está vacía. Ejecutando StateUserSeeder...");
            try {
                $this->call('db:seed', ['--class' => 'StateUserSeeder']);
                $this->info("✅ StateUserSeeder ejecutado correctamente");
            } catch (\Exception $e) {
                $this->error("❌ Error ejecutando StateUserSeeder: " . $e->getMessage());
                return 1;
            }
        } else {
            $this->info("✅ Tabla status_users tiene {$statusUsersCount} registros");
        }

        // Verificar si hay datos en economic_sectors
        $economicSectorsCount = DB::table('economic_sectors')->count();
        if ($economicSectorsCount === 0) {
            $this->warn("⚠️ Tabla economic_sectors está vacía. Ejecutando EconomicSectorSeeder...");
            try {
                $this->call('db:seed', ['--class' => 'EconomicSectorSeeder']);
                $this->info("✅ EconomicSectorSeeder ejecutado correctamente");
            } catch (\Exception $e) {
                $this->error("❌ Error ejecutando EconomicSectorSeeder: " . $e->getMessage());
                return 1;
            }
        } else {
            $this->info("✅ Tabla economic_sectors tiene {$economicSectorsCount} registros");
        }

        // Mostrar los datos disponibles
        $this->info("\n📊 Datos disponibles:");
        
        $statusUsers = DB::table('status_users')->get();
        $this->info("Status Users:");
        foreach ($statusUsers as $status) {
            $this->info("   - ID: {$status->id}, Estado: {$status->state}");
        }

        $economicSectors = DB::table('economic_sectors')->get();
        $this->info("Economic Sectors:");
        foreach ($economicSectors as $sector) {
            $this->info("   - ID: {$sector->id}, Nombre: {$sector->name}");
        }

        // Verificar si hay usuarios existentes con foreign keys inválidas
        $this->info("\n🔍 Verificando usuarios existentes...");
        $invalidUsers = DB::table('users')
            ->leftJoin('status_users', 'users.status_users_id', '=', 'status_users.id')
            ->whereNull('status_users.id')
            ->whereNotNull('users.status_users_id')
            ->get();

        if ($invalidUsers->count() > 0) {
            $this->warn("⚠️ Se encontraron {$invalidUsers->count()} usuarios con status_users_id inválido");
            
            if ($this->confirm('¿Quieres corregir los usuarios existentes?')) {
                foreach ($invalidUsers as $user) {
                    // Asignar el primer status_users disponible
                    $firstStatus = DB::table('status_users')->first();
                    if ($firstStatus) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['status_users_id' => $firstStatus->id]);
                        $this->info("   ✅ Usuario ID {$user->id} corregido");
                    }
                }
            }
        } else {
            $this->info("✅ No se encontraron usuarios con foreign keys inválidas");
        }

        // Preguntar si quiere ejecutar el TestDataSeeder
        if ($this->confirm('¿Quieres ejecutar el TestDataSeeder ahora?')) {
            $this->info("\n🌱 Ejecutando TestDataSeeder...");
            try {
                $this->call('db:seed', ['--class' => 'TestDataSeeder']);
                $this->info("✅ TestDataSeeder ejecutado correctamente");
            } catch (\Exception $e) {
                $this->error("❌ Error ejecutando TestDataSeeder: " . $e->getMessage());
                
                // Mostrar más detalles del error
                $this->info("\n🔍 Detalles del error:");
                $this->line("   - Verifica que las tablas status_users y economic_sectors tengan datos");
                $this->line("   - Verifica que los IDs referenciados en TestDataSeeder existan");
                $this->line("   - Ejecuta 'php artisan migrate:fresh --seed' para empezar desde cero");
                
                return 1;
            }
        }

        // Verificación final
        $this->info("\n✅ Verificación final...");
        $usersCount = DB::table('users')->count();
        $this->info("Total de usuarios en la base de datos: {$usersCount}");

        $this->info("\n🎉 Problema del TestDataSeeder solucionado");
        return 0;
    }
}
