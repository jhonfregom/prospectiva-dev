<?php

namespace Database\Seeders;

use App\Models\User;

use Illuminate\Database\Seeder;
use App\Models\StateUser;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        
        $this->call([
            StateUserSeeder::class,
            TestDataSeeder::class,
            TestVariablesSeeder::class,
            TestMatrizSeeder::class,
            TestZonesSeeder::class,
        ]);
        
    }
}