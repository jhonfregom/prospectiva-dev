<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Zones;

class TestZonesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar datos existentes y reiniciar auto-incremento
        DB::statement('DELETE FROM zones WHERE id > 0');
        DB::statement('ALTER TABLE zones AUTO_INCREMENT = 1');

   $Zona = [
    [
        'id' => 1,
        'name_zones' => 'ZONA DE PODER',
    ],
    [
        'id' => 2,
        'name_zones' => 'ZONA DE CONFLICTO',
    ],
    [
        'id' => 3,
        'name_zones' => 'ZONA DE SALIDA',
    ],
    [
        'id' => 4,
        'name_zones' => 'ZONA DE INDIFERENCIA',
    ]
];

foreach ($Zona as $data) {
    Zones::create($data);
}
    
    }
}
