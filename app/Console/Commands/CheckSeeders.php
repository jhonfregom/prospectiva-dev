<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckSeeders extends Command
{
    protected $signature = 'check:seeders';
    protected $description = 'Check that all seeders are working correctly';

    public function handle()
    {
        $this->info('🌱 Verificando seeders...');

        // Verificar StateUserSeeder
        $this->info("\n📊 Estado de usuarios:");
        $stateUsersCount = DB::table('status_users')->count();
        $this->info("   - Status users: {$stateUsersCount}");

        // Verificar EconomicSectorSeeder
        $this->info("\n🏭 Sectores económicos:");
        $economicSectorsCount = DB::table('economic_sectors')->count();
        $this->info("   - Economic sectors: {$economicSectorsCount}");

        if ($economicSectorsCount == 0) {
            $this->warn("   ⚠️ No hay sectores económicos. Ejecutando seeder...");
            $this->call('db:seed', ['--class' => 'EconomicSectorSeeder']);
        }

        // Verificar TestDataSeeder
        $this->info("\n👥 Usuarios de prueba:");
        $testUsersCount = DB::table('users')->where('user', 'like', '%@example.com')->orWhere('user', 'like', '%@empresa.com')->count();
        $this->info("   - Test users: {$testUsersCount}");

        // Verificar TestVariablesSeeder
        $this->info("\n📈 Variables de prueba:");
        $testVariablesCount = DB::table('variables')->where('user_id', 1)->count();
        $this->info("   - Test variables: {$testVariablesCount}");

        // Verificar TestMatrizSeeder
        $this->info("\n📊 Matriz de prueba:");
        $testMatrizCount = DB::table('matriz')->where('user_id', 1)->count();
        $this->info("   - Test matriz: {$testMatrizCount}");

        // Verificar TestZonesSeeder
        $this->info("\n🗺️ Zonas de prueba:");
        $testZonesCount = DB::table('zones')->where('user_id', 1)->count();
        $this->info("   - Test zones: {$testZonesCount}");

        // Verificar datos mínimos necesarios
        $this->info("\n✅ Verificación de datos mínimos:");
        
        if ($stateUsersCount > 0) {
            $this->info("   ✅ Status users: OK");
        } else {
            $this->error("   ❌ Status users: FALTAN");
        }

        if ($economicSectorsCount > 0) {
            $this->info("   ✅ Economic sectors: OK");
        } else {
            $this->error("   ❌ Economic sectors: FALTAN");
        }

        if ($testUsersCount > 0) {
            $this->info("   ✅ Test users: OK");
        } else {
            $this->warn("   ⚠️ Test users: No hay datos de prueba");
        }

        $this->info("\n🎉 Verificación de seeders completada");
        return 0;
    }
}
