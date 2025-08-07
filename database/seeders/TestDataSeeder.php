<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StateUser;
use App\Models\User;
use App\Models\EconomicSector;
use App\Models\Traceability;
use App\Models\Note;
use App\Models\Variable;
use App\Models\Matriz;
use App\Models\Hypothesis;
use App\Models\Scenarios;
use App\Models\Conclusion;
use App\Models\Zones;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    
    public function run(): void
    {
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('DELETE FROM notes WHERE id > 0');
        DB::statement('DELETE FROM traceability WHERE id > 0');
        DB::statement('DELETE FROM variables WHERE id > 0');
        DB::statement('DELETE FROM matriz WHERE id > 0');
        DB::statement('DELETE FROM hypothesis WHERE id > 0');
        DB::statement('DELETE FROM scenarios WHERE id > 0');
        DB::statement('DELETE FROM conclusions WHERE id > 0');
        DB::statement('DELETE FROM zones WHERE id > 0');
        DB::statement('DELETE FROM users WHERE id > 0');
        DB::statement('ALTER TABLE notes AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE traceability AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE variables AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE matriz AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE hypothesis AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE scenarios AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE conclusions AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE zones AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $sectors = [
            'Tecnología', 'Salud', 'Educación', 'Finanzas', 'Manufactura',
            'Retail', 'Energía', 'Transporte', 'Agricultura', 'Servicios'
        ];

        foreach ($sectors as $sector) {
            EconomicSector::firstOrCreate(['name' => $sector]);
        }

        $zoneNames = ['Zona Norte', 'Zona Sur', 'Zona Este', 'Zona Oeste', 'Zona Central'];
        foreach ($zoneNames as $zoneName) {
            Zones::create([
                'name_zones' => $zoneName,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $testUser = User::create([
                'id' => 1,
                'first_name' => 'Test User',
                'last_name' => 'Example',
                'document_id' => 123456789,
                'user' => 'test@example.com',
                'password' => Hash::make('abcd1234'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
            'role' => 1, 
            'economic_sector' => 1, 
            'registration_type' => 'natural'
            ]);

        $naturalUsers = [
            [
                'id' => 2,
                'first_name' => 'María',
                'last_name' => 'González',
                'document_id' => 100123456,
                'user' => 'maria.gonzalez@example.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0,
                'economic_sector' => 2, 
                'registration_type' => 'natural'
            ],
            [
                'id' => 3,
                'first_name' => 'Carlos',
                'last_name' => 'Rodríguez',
                'document_id' => 200234567,
                'user' => 'carlos.rodriguez@example.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0,
                'economic_sector' => 3, 
                'registration_type' => 'natural'
            ],
            [
                'id' => 4,
                'first_name' => 'Ana',
                'last_name' => 'López',
                'document_id' => 300345678,
                'user' => 'ana.lopez@example.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0,
                'economic_sector' => 4, 
                'registration_type' => 'natural'
            ],
            [
                'id' => 5,
                'first_name' => 'Luis',
                'last_name' => 'Martínez',
                'document_id' => 400456789,
                'user' => 'luis.martinez@example.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0,
                'economic_sector' => 5, 
                'registration_type' => 'natural'
            ]
        ];

        $companyUsers = [
            [
                'id' => 6,
                'first_name' => 'Sofia',
                'last_name' => 'Pérez',
                'document_id' => 500567890,
                'user' => 'sofia.perez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0,
                'economic_sector' => 6, 
                'registration_type' => 'company'
            ],
            [
                'id' => 7,
                'first_name' => 'Diego',
                'last_name' => 'Hernández',
                'document_id' => 600678901,
                'user' => 'diego.hernandez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0,
                'economic_sector' => 7, 
                'registration_type' => 'company'
            ],
            [
                'id' => 8,
                'first_name' => 'Valentina',
                'last_name' => 'García',
                'document_id' => 700789012,
                'user' => 'valentina.garcia@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0,
                'economic_sector' => 8, 
                'registration_type' => 'company'
            ],
            [
                'id' => 9,
                'first_name' => 'Andrés',
                'last_name' => 'Fernández',
                'document_id' => 800890123,
                'user' => 'andres.fernandez@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0,
                'economic_sector' => 9, 
                'registration_type' => 'company'
            ],
            [
                'id' => 10,
                'first_name' => 'Camila',
                'last_name' => 'Torres',
                'document_id' => 900901234,
                'user' => 'camila.torres@empresa.com',
                'password' => Hash::make('password123'),
                'status_users_id' => StateUser::STATUS_ACTIVE,
                'role' => 0,
                'economic_sector' => 10, 
                'registration_type' => 'company'
            ]
        ];

        foreach (array_merge($naturalUsers, $companyUsers) as $userData) {
                User::create($userData);
        }

        $allUsers = User::all();
        foreach ($allUsers as $user) {
            Traceability::create([
                'user_id' => $user->id,
                'tried' => '1',
                'variables' => '0',
                'matriz' => '0',
                'maps' => '0',
                'hypothesis' => '0',
                'shwartz' => '0',
                'conditions' => '0',
                'scenarios' => '0',
                'conclusions' => '0',
                'results' => '0',
                'state' => '0',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $noteTemplates = [
            'Observaciones sobre el entorno político actual y su impacto en el sector empresarial.',
            'Análisis de tendencias tecnológicas emergentes que podrían afectar la industria.',
            'Reflexiones sobre cambios sociales y demográficos relevantes para la organización.',
            'Notas sobre regulaciones ambientales y su impacto en las operaciones.',
            'Análisis de la competencia y el mercado actual.',
            'Observaciones sobre la economía global y sus efectos locales.',
            'Reflexiones sobre innovación y desarrollo de productos.',
            'Análisis de riesgos y oportunidades del entorno actual.'
        ];

        $allUsers = User::all();
        foreach ($allUsers as $index => $user) {
            $numNotes = rand(1, 3); 
            for ($i = 0; $i < $numNotes; $i++) {
                $templateIndex = ($index + $i) % count($noteTemplates);
                Note::create([
                    'user_id' => $user->id,
                    'title' => 'Nota ' . ($i + 1) . ' - ' . $user->first_name,
                    'content' => $noteTemplates[$templateIndex] . ' (Usuario: ' . $user->first_name . ' ' . $user->last_name . ')',
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30))
                ]);
            }
        }

        $this->command->info('✅ Datos de prueba creados exitosamente:');
        $this->command->info('   - Usuario de prueba: test@example.com / abcd1234');
        $this->command->info('   - ' . count($allUsers) . ' usuarios creados');
        $this->command->info('   - ' . Traceability::count() . ' registros de trazabilidad');
        $this->command->info('   - ' . Note::count() . ' notas creadas');

        $this->command->info('   - Creando datos para variables, matriz, hipótesis, escenarios y conclusiones...');
        
        foreach ($allUsers as $user) {
            
            $traceability = Traceability::where('user_id', $user->id)->first();
            $triedId = $traceability ? $traceability->id : 1;

            $variableNames = ['Variable Política', 'Variable Económica', 'Variable Social', 'Variable Tecnológica', 'Variable Ambiental'];
            foreach ($variableNames as $index => $name) {
                Variable::create([
                    'id_variable' => 'VAR' . ($index + 1),
                    'name_variable' => $name,
                    'description' => 'Descripción de ' . $name . ' para el usuario ' . $user->first_name,
                    'score' => rand(1, 10),
                    'user_id' => $user->id,
                    'state' => '0',
                    'now_condition' => 'Condición actual de ' . $name,
                    'tried_id' => $triedId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $variables = Variable::where('user_id', $user->id)->get();
            foreach ($variables as $variable) {
                Matriz::create([
                    'id_matriz' => 1,
                    'id_variable' => $variable->id,
                    'id_resp_depen' => rand(1, 5),
                    'id_resp_influ' => rand(1, 5),
                    'user_id' => $user->id,
                    'state' => '0',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $hypothesisNames = ['Hipótesis Principal', 'Hipótesis Secundaria'];
            $zones = Zones::all();
            foreach ($hypothesisNames as $index => $name) {
                Hypothesis::create([
                    'id_variable' => $variables->first()->id,
                    'zone_id' => $zones[$index % $zones->count()]->id, 
                    'name_hypothesis' => $name . ' - ' . $user->first_name,
                    'description' => 'Descripción de ' . $name . ' para el usuario ' . $user->first_name,
                    'user_id' => $user->id,
                    'state' => '0',
                    'tried_id' => $triedId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $scenarioTitles = ['Escenario Optimista', 'Escenario Pesimista', 'Escenario Realista'];
            foreach ($scenarioTitles as $index => $title) {
                Scenarios::create([
                    'user_id' => $user->id,
                    'titulo' => $title . ' - ' . $user->first_name,
                    'edits' => 0,
                    'state' => '0',
                    'num_scenario' => $index + 1,
                    'tried_id' => $triedId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            Conclusion::create([
                'component_practice' => 'Componente de práctica para ' . $user->first_name,
                'component_practice_edits' => 0,
                'actuality' => 'Actualidad para ' . $user->first_name,
                'actuality_edits' => 0,
                'aplication' => 'Aplicación para ' . $user->first_name,
                'aplication_edits' => 0,
                'user_id' => $user->id,
                'state' => '0',
                'tried_id' => $triedId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $this->command->info('   - ' . Variable::count() . ' variables creadas');
        $this->command->info('   - ' . Matriz::count() . ' matrices creadas');
        $this->command->info('   - ' . Hypothesis::count() . ' hipótesis creadas');
        $this->command->info('   - ' . Scenarios::count() . ' escenarios creados');
        $this->command->info('   - ' . Conclusion::count() . ' conclusiones creadas');
        $this->command->info('   - ' . Zones::count() . ' zonas creadas');
    }
}