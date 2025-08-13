<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Seeder;
use App\Models\StateUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        
        // Solo ejecutar seeders que no se han ejecutado previamente
        if (DB::table('status_users')->count() === 0) {
            $this->call(StateUserSeeder::class);
        }
        
        if (DB::table('economic_sectors')->count() === 0) {
            $this->call(EconomicSectorSeeder::class);
        }
        
        if (DB::table('users')->where('role', 1)->count() === 0) {
            $this->call(AdminUserSeeder::class);
        }
        
        // Ejecutar seeders de datos de prueba
        $this->call([
            TestDataSeeder::class,         // Datos de prueba
            TestVariablesSeeder::class,    // Variables de prueba
            TestMatrizSeeder::class,       // Matriz de prueba
            TestZonesSeeder::class,        // Zonas de prueba
        ]);
        
    }
}