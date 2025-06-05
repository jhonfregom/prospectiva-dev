<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class StateUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    DB::table('status_users')->insert([
            [
                'id' => 1,
                'name' => 'Activo',
                'description' => 'Usuario activo y acceso permitido al sistema'
            ],
            [
                'id' => 2,
                'name' => 'Inactivo',
                'description' => null
            ],
            [
                'id' => 3,
                'name' => 'Suspendido',
                'description' => null
            ],
            [
                'id' => 4,
                'name' => 'Bloqueado',
                'description' => null
            ],
            [
                'id' => 5,
                'name' => 'Eliminado',
                'description' => null
            ]
        ]);
    }
}
