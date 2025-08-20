<?php

namespace Database\Seeders;

use App\Models\Variable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
;
use Illuminate\Support\Facades\DB;

class TestVariablesSeeder extends Seeder
{
    
    public function run(): void
    {
        
        // Verificar si ya existen variables de prueba
        if (DB::table('variables')->where('user_id', 1)->count() > 0) {
            $this->command->info('Las variables de prueba ya existen, saltando seeder...');
            return;
        }

        $this->command->info('Creando variables de prueba...');

   $variables = [
    [
        'id' => 1,
        'id_variable' => 'V1',
        'name_variable' => 'Variable 1',
        'description' => 'Este es una variable de prueba',
        'score' => 10,
        'user_id' => 1,
        'state' => '0'
    ],
    [
        'id' => 2,
        'id_variable' => 'V2',
        'name_variable' => 'Variable 2',
        'description' => 'Este es una segunda variable de prueba',
        'score' => 10,
        'user_id' => 1,
        'state' => '0'
    ],
    [
        'id' => 3,
        'id_variable' => 'V3',
        'name_variable' => 'Variable 3',
        'description' => 'Este es una tercera variable de prueba',
        'score' => 10,
        'user_id' => 1,
        'state' => '0'
    ],
    [
        'id' => 4,
        'id_variable' => 'V4',
        'name_variable' => 'Variable 4',
        'description' => 'Este es una cuarta variable de prueba',
        'score' => 10,
        'user_id' => 1,
        'state' => '0'
    ]
];

foreach ($variables as $data) {
    Variable::create($data);
}

$this->command->info('Variables creadas exitosamente: ' . count($variables));
    }
}