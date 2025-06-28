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
        DB::statement('DELETE FROM zones WHERE id > 0');
        DB::statement('ALTER TABLE zones AUTO_INCREMENT = 0');

   $Zona = [
    [
        'name_zones' => 'ZONA DE PODER',
    ],
    [
        'name_zones' => 'ZONA DE CONFLICTO',
    ],
    [
        'name_zones' => 'ZONA DE SALIDA',
    ],
    [
        'name_zones' => 'ZONA DE INDIFERENCIA',
    ]
];

foreach ($Zona as $data) {
    Zones::create($data);
}
    
    }
}
