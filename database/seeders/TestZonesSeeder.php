<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Zones;

class TestZonesSeeder extends Seeder
{
    
    public function run(): void
    {
        
        // Verificar si ya existen zonas
        if (DB::table('zones')->count() > 0) {
            $this->command->info('Las zonas ya existen, saltando seeder...');
            return;
        }

        $this->command->info('Creando zonas de prueba...');

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

$this->command->info('Zonas creadas exitosamente: ' . count($Zona));
    
    }
}