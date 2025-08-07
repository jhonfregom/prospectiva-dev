<?php

namespace Database\Seeders;

use App\Models\Variable;
use App\Models\Matriz;
use App\Models\VariableMapAnalisys;
use App\Models\Hypothesis;
use App\Models\Scenarios;
use App\Models\Conclusion;
use App\Models\Zones;
use App\Models\Traceability;
use App\Models\Note;
use App\Models\User;
use App\Models\EconomicSector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestCompleteDataSeeder extends Seeder
{
    
    public function run(): void
    {
        
        DB::statement('DELETE FROM conclusions WHERE user_id IN (1, 2, 3, 4)');
        DB::statement('DELETE FROM scenarios WHERE user_id IN (1, 2, 3, 4)');
        DB::statement('DELETE FROM hypothesis WHERE user_id IN (1, 2, 3, 4)');
        DB::statement('DELETE FROM variables_map_analiyis WHERE user_id IN (1, 2, 3, 4)');
        DB::statement('DELETE FROM matriz WHERE user_id IN (1, 2, 3, 4)');
        DB::statement('DELETE FROM variables WHERE user_id IN (1, 2, 3, 4)');
        DB::statement('DELETE FROM notes WHERE user_id IN (1, 2, 3, 4)');
        DB::statement('DELETE FROM traceability WHERE user_id IN (1, 2, 3, 4)');

        DB::statement('ALTER TABLE variables AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE matriz AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE variables_map_analiyis AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE hypothesis AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE scenarios AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE conclusions AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE notes AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE traceability AUTO_INCREMENT = 1');

        $users = [
            
            [
                'id' => 1,
                'document_id' => 12345678,
                'first_name' => 'Juan',
                'last_name' => 'Pérez',
                'user' => 'admin',
                'password' => Hash::make('password'),
                'role' => 1, 
                'status_users_id' => 1, 
                'economic_sector' => 1, 
                'registration_type' => 'natural'
            ],
            
            [
                'id' => 2,
                'document_id' => 87654321,
                'first_name' => 'María',
                'last_name' => 'García',
                'user' => 'usuario',
                'password' => Hash::make('password'),
                'role' => 0, 
                'status_users_id' => 1, 
                'economic_sector' => 2, 
                'registration_type' => 'natural'
            ],
            
            [
                'id' => 3,
                'document_id' => 900123456,
                'first_name' => 'Carlos',
                'last_name' => 'Rodríguez',
                'user' => 'empresa',
                'password' => Hash::make('password'),
                'role' => 0, 
                'status_users_id' => 1, 
                'economic_sector' => 1, 
                'registration_type' => 'company'
            ],
            
            [
                'id' => 4,
                'document_id' => 800987654,
                'first_name' => 'Ana',
                'last_name' => 'Martínez',
                'user' => 'empresa2',
                'password' => Hash::make('password'),
                'role' => 0, 
                'status_users_id' => 1, 
                'economic_sector' => 3, 
                'registration_type' => 'company'
            ]
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['id' => $userData['id']],
                $userData
            );
        }

        $traceabilityRecords = [
            
            [
                'id' => 1,
                'user_id' => 1,
                'tried' => '1',
                'variables' => '1',
                'matriz' => '1',
                'maps' => '1',
                'hypothesis' => '1',
                'shwartz' => '0',
                'conditions' => '0',
                'scenarios' => '1',
                'conclusions' => '1',
                'results' => '0',
                'state' => '1'
            ],
            
            [
                'id' => 2,
                'user_id' => 2,
                'tried' => '1',
                'variables' => '1',
                'matriz' => '1',
                'maps' => '1',
                'hypothesis' => '1',
                'shwartz' => '0',
                'conditions' => '0',
                'scenarios' => '1',
                'conclusions' => '1',
                'results' => '0',
                'state' => '1'
            ],
            
            [
                'id' => 3,
                'user_id' => 3,
                'tried' => '1',
                'variables' => '1',
                'matriz' => '0',
                'maps' => '0',
                'hypothesis' => '0',
                'shwartz' => '0',
                'conditions' => '0',
                'scenarios' => '0',
                'conclusions' => '0',
                'results' => '0',
                'state' => '1'
            ],
            
            [
                'id' => 4,
                'user_id' => 4,
                'tried' => '1',
                'variables' => '1',
                'matriz' => '0',
                'maps' => '0',
                'hypothesis' => '0',
                'shwartz' => '0',
                'conditions' => '0',
                'scenarios' => '0',
                'conclusions' => '0',
                'results' => '0',
                'state' => '1'
            ]
        ];

        foreach ($traceabilityRecords as $data) {
            Traceability::create($data);
        }

        $notes = [
            [
                'id' => 1,
                'user_id' => 1,
                'title' => 'Notas del Administrador',
                'content' => 'Observaciones importantes sobre el análisis de prospectiva tecnológica.'
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'title' => 'Notas del Usuario',
                'content' => 'Reflexiones sobre las variables analizadas en el proyecto.'
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'title' => 'Notas de TechCorp',
                'content' => 'Análisis estratégico para la implementación de nuevas tecnologías.'
            ],
            [
                'id' => 4,
                'user_id' => 4,
                'title' => 'Notas de EcoGreen',
                'content' => 'Consideraciones ambientales para el desarrollo sostenible.'
            ]
        ];

        foreach ($notes as $data) {
            Note::create($data);
        }

        $adminVariables = [
            [ 'id' => 1, 'id_variable' => 'V1', 'name_variable' => 'Tecnología Digital', 'description' => 'Avances en tecnologías digitales y su impacto en la sociedad', 'score' => 15, 'user_id' => 1, 'state' => '0', 'now_condition' => 'Infraestructura digital avanzada y conectividad total.', 'tried_id' => 1, 'edits_variable' => 2, 'edits_now_condition' => 1 ],
            [ 'id' => 2, 'id_variable' => 'V2', 'name_variable' => 'Cambio Climático', 'description' => 'Efectos del cambio climático en el medio ambiente', 'score' => 12, 'user_id' => 1, 'state' => '0', 'now_condition' => 'Aumento de eventos climáticos extremos en la región.', 'tried_id' => 1, 'edits_variable' => 1, 'edits_now_condition' => 1 ],
            [ 'id' => 3, 'id_variable' => 'V3', 'name_variable' => 'Globalización Económica', 'description' => 'Procesos de integración económica mundial', 'score' => 18, 'user_id' => 1, 'state' => '0', 'now_condition' => 'Mercados internacionales abiertos y competitivos.', 'tried_id' => 1, 'edits_variable' => 1, 'edits_now_condition' => 0 ],
            [ 'id' => 4, 'id_variable' => 'V4', 'name_variable' => 'Educación Virtual', 'description' => 'Transformación de la educación hacia modalidades virtuales', 'score' => 10, 'user_id' => 1, 'state' => '0', 'now_condition' => 'Alta adopción de plataformas de educación online.', 'tried_id' => 1, 'edits_variable' => 0, 'edits_now_condition' => 0 ],
            [ 'id' => 5, 'id_variable' => 'V5', 'name_variable' => 'Inteligencia Artificial', 'description' => 'Desarrollo y aplicación de IA en diversos sectores', 'score' => 20, 'user_id' => 1, 'state' => '0', 'now_condition' => 'IA presente en procesos productivos y administrativos.', 'tried_id' => 1, 'edits_variable' => 3, 'edits_now_condition' => 2 ]
        ];

        $userVariables = [
            [ 'id' => 6, 'id_variable' => 'V1', 'name_variable' => 'Sostenibilidad Empresarial', 'description' => 'Prácticas sostenibles en el sector empresarial', 'score' => 14, 'user_id' => 2, 'state' => '0', 'now_condition' => 'Empresas implementan políticas de reciclaje y eficiencia energética.', 'tried_id' => 2, 'edits_variable' => 1, 'edits_now_condition' => 1 ],
            [ 'id' => 7, 'id_variable' => 'V2', 'name_variable' => 'Trabajo Remoto', 'description' => 'Tendencias del trabajo a distancia y sus implicaciones', 'score' => 16, 'user_id' => 2, 'state' => '0', 'now_condition' => 'El 80% del personal trabaja desde casa al menos dos días a la semana.', 'tried_id' => 2, 'edits_variable' => 2, 'edits_now_condition' => 1 ],
            [ 'id' => 8, 'id_variable' => 'V3', 'name_variable' => 'Comercio Electrónico', 'description' => 'Crecimiento del comercio online y su impacto', 'score' => 13, 'user_id' => 2, 'state' => '0', 'now_condition' => 'Aumento sostenido de ventas online y digitalización de pagos.', 'tried_id' => 2, 'edits_variable' => 1, 'edits_now_condition' => 0 ]
        ];

        $empresa1Variables = [
            [ 'id' => 9, 'id_variable' => 'V1', 'name_variable' => 'Innovación Tecnológica', 'description' => 'Desarrollo de nuevas tecnologías en el sector empresarial', 'score' => 17, 'user_id' => 3, 'state' => '0', 'now_condition' => 'Inversión constante en I+D+i para mantener competitividad.', 'tried_id' => 3, 'edits_variable' => 1, 'edits_now_condition' => 1 ],
            [ 'id' => 10, 'id_variable' => 'V2', 'name_variable' => 'Transformación Digital', 'description' => 'Proceso de digitalización de procesos empresariales', 'score' => 15, 'user_id' => 3, 'state' => '0', 'now_condition' => 'Migración gradual de sistemas tradicionales a plataformas digitales.', 'tried_id' => 3, 'edits_variable' => 2, 'edits_now_condition' => 1 ]
        ];

        $empresa2Variables = [
            [ 'id' => 11, 'id_variable' => 'V1', 'name_variable' => 'Sostenibilidad Ambiental', 'description' => 'Prácticas eco-amigables en la industria', 'score' => 19, 'user_id' => 4, 'state' => '0', 'now_condition' => 'Implementación de energías renovables y procesos verdes.', 'tried_id' => 4, 'edits_variable' => 1, 'edits_now_condition' => 1 ],
            [ 'id' => 12, 'id_variable' => 'V2', 'name_variable' => 'Economía Circular', 'description' => 'Modelos de negocio basados en reutilización', 'score' => 14, 'user_id' => 4, 'state' => '0', 'now_condition' => 'Reducción de residuos y maximización de recursos.', 'tried_id' => 4, 'edits_variable' => 1, 'edits_now_condition' => 0 ]
        ];

        $allVariables = array_merge($adminVariables, $userVariables, $empresa1Variables, $empresa2Variables);
        $adminVarIds = [];
        $userVarIds = [];
        $empresa1VarIds = [];
        $empresa2VarIds = [];

        foreach ($allVariables as $data) {
            $v = Variable::create($data);

            if ($data['user_id'] == 1) {
                $adminVarIds[$data['id_variable']] = $v->id;
            } elseif ($data['user_id'] == 2) {
            $userVarIds[$data['id_variable']] = $v->id;
            } elseif ($data['user_id'] == 3) {
                $empresa1VarIds[$data['id_variable']] = $v->id;
            } elseif ($data['user_id'] == 4) {
                $empresa2VarIds[$data['id_variable']] = $v->id;
            }
        }

        $adminMatriz = [
            ['id' => 1, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V1'], 'id_resp_depen' => $adminVarIds['V2'], 'id_resp_influ' => 3, 'user_id' => 1, 'state' => '1', 'tried_id' => 1],
            ['id' => 2, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V2'], 'id_resp_depen' => $adminVarIds['V1'], 'id_resp_influ' => 2, 'user_id' => 1, 'state' => '1', 'tried_id' => 1],
            ['id' => 3, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V1'], 'id_resp_depen' => $adminVarIds['V3'], 'id_resp_influ' => 2, 'user_id' => 1, 'state' => '1', 'tried_id' => 1],
            ['id' => 4, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V3'], 'id_resp_depen' => $adminVarIds['V1'], 'id_resp_influ' => 1, 'user_id' => 1, 'state' => '1', 'tried_id' => 1],
            ['id' => 5, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V1'], 'id_resp_depen' => $adminVarIds['V4'], 'id_resp_influ' => 1, 'user_id' => 1, 'state' => '1', 'tried_id' => 1],
            ['id' => 6, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V4'], 'id_resp_depen' => $adminVarIds['V1'], 'id_resp_influ' => 2, 'user_id' => 1, 'state' => '1', 'tried_id' => 1],
            ['id' => 7, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V1'], 'id_resp_depen' => $adminVarIds['V5'], 'id_resp_influ' => 3, 'user_id' => 1, 'state' => '1', 'tried_id' => 1],
            ['id' => 8, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V5'], 'id_resp_depen' => $adminVarIds['V1'], 'id_resp_influ' => 1, 'user_id' => 1, 'state' => '1', 'tried_id' => 1],
        ];

        $userMatriz = [
            ['id' => 9, 'id_matriz' => 2, 'id_variable' => $userVarIds['V1'], 'id_resp_depen' => $userVarIds['V2'], 'id_resp_influ' => 2, 'user_id' => 2, 'state' => '1', 'tried_id' => 2],
            ['id' => 10, 'id_matriz' => 2, 'id_variable' => $userVarIds['V2'], 'id_resp_depen' => $userVarIds['V1'], 'id_resp_influ' => 2, 'user_id' => 2, 'state' => '1', 'tried_id' => 2],
            ['id' => 11, 'id_matriz' => 2, 'id_variable' => $userVarIds['V1'], 'id_resp_depen' => $userVarIds['V3'], 'id_resp_influ' => 3, 'user_id' => 2, 'state' => '1', 'tried_id' => 2],
            ['id' => 12, 'id_matriz' => 2, 'id_variable' => $userVarIds['V3'], 'id_resp_depen' => $userVarIds['V1'], 'id_resp_influ' => 1, 'user_id' => 2, 'state' => '1', 'tried_id' => 2],
        ];

        $allMatriz = array_merge($adminMatriz, $userMatriz);
        foreach ($allMatriz as $data) {
            Matriz::create($data);
        }

        $adminAnalyses = [
            [ 'id' => 1, 'zone_id' => 1, 'description' => 'Las variables de tecnología digital e inteligencia artificial muestran alta influencia y dependencia, posicionándolas como elementos clave del poder en el sistema.', 'score' => 85, 'user_id' => 1, 'state' => '1', 'tried_id' => 1, 'edits' => 2 ],
            [ 'id' => 2, 'zone_id' => 2, 'description' => 'El cambio climático presenta alta influencia pero baja dependencia, generando tensiones y conflictos en el sistema.', 'score' => 72, 'user_id' => 1, 'state' => '1', 'tried_id' => 1, 'edits' => 1 ],
            [ 'id' => 3, 'zone_id' => 3, 'description' => 'La globalización económica muestra baja influencia y dependencia, siendo un elemento de salida del sistema.', 'score' => 45, 'user_id' => 1, 'state' => '1', 'tried_id' => 1, 'edits' => 0 ],
            [ 'id' => 4, 'zone_id' => 4, 'description' => 'La educación virtual presenta características de zona de indiferencia con valores moderados.', 'score' => 38, 'user_id' => 1, 'state' => '1', 'tried_id' => 1, 'edits' => 0 ]
        ];

        $userAnalyses = [
            [ 'id' => 5, 'zone_id' => 1, 'description' => 'La sostenibilidad empresarial muestra alta influencia y dependencia, siendo un elemento de poder en el sistema empresarial.', 'score' => 78, 'user_id' => 2, 'state' => '1', 'tried_id' => 2, 'edits' => 1 ],
            [ 'id' => 6, 'zone_id' => 2, 'description' => 'El trabajo remoto presenta alta influencia pero baja dependencia, generando conflictos en las organizaciones.', 'score' => 65, 'user_id' => 2, 'state' => '1', 'tried_id' => 2, 'edits' => 1 ],
            [ 'id' => 7, 'zone_id' => 3, 'description' => 'El comercio electrónico muestra características de zona de salida con baja influencia y dependencia.', 'score' => 42, 'user_id' => 2, 'state' => '1', 'tried_id' => 2, 'edits' => 0 ],
            [ 'id' => 8, 'zone_id' => 4, 'description' => 'La innovación en productos es percibida como indiferente por el mercado actual.', 'score' => 30, 'user_id' => 2, 'state' => '1', 'tried_id' => 2, 'edits' => 0 ]
        ];

        $allAnalyses = array_merge($adminAnalyses, $userAnalyses);
        foreach ($allAnalyses as $data) {
            DB::table('variables_map_analiyis')->insert($data);
        }

        $hypotheses = [
            
            [
                'id' => 1, 'id_variable' => 5, 'zone_id' => 4, 'name_hypothesis' => 'H1',
                'description' => 'Hipótesis sobre el impacto de la Inteligencia Artificial.',
                'secondary_hypotheses' => 'H0', 'user_id' => 1, 'state' => '0', 'tried_id' => 1, 'edits' => 1
            ],
            [
                'id' => 2, 'id_variable' => 5, 'zone_id' => 4, 'name_hypothesis' => 'H1',
                'description' => 'Evaluación alternativa de la Inteligencia Artificial.',
                'secondary_hypotheses' => 'H1', 'user_id' => 1, 'state' => '0', 'tried_id' => 1, 'edits' => 1
            ],
            [
                'id' => 3, 'id_variable' => 2, 'zone_id' => 4, 'name_hypothesis' => 'H2',
                'description' => 'Hipótesis sobre los efectos del Cambio Climático.',
                'secondary_hypotheses' => 'H0', 'user_id' => 1, 'state' => '0', 'tried_id' => 1, 'edits' => 0
            ],
            [
                'id' => 4, 'id_variable' => 2, 'zone_id' => 4, 'name_hypothesis' => 'H2',
                'description' => 'Análisis alternativo del Cambio Climático.',
                'secondary_hypotheses' => 'H1', 'user_id' => 1, 'state' => '0', 'tried_id' => 1, 'edits' => 0
            ],
            
            [
                'id' => 5, 'id_variable' => 8, 'zone_id' => 4, 'name_hypothesis' => 'H1',
                'description' => 'Hipótesis h0 sobre comercio electrónico',
                'secondary_hypotheses' => 'H0', 'user_id' => 2, 'state' => '0', 'tried_id' => 2, 'edits' => 1
            ],
            [
                'id' => 6, 'id_variable' => 8, 'zone_id' => 4, 'name_hypothesis' => 'H1',
                'description' => 'Hipótesis h1 sobre comercio electrónico',
                'secondary_hypotheses' => 'H1', 'user_id' => 2, 'state' => '0', 'tried_id' => 2, 'edits' => 1
            ],
            [
                'id' => 7, 'id_variable' => 7, 'zone_id' => 4, 'name_hypothesis' => 'H2',
                'description' => 'Hipótesis h0 sobre trabajo remoto',
                'secondary_hypotheses' => 'H0', 'user_id' => 2, 'state' => '0', 'tried_id' => 2, 'edits' => 0
            ],
            [
                'id' => 8, 'id_variable' => 7, 'zone_id' => 4, 'name_hypothesis' => 'H2',
                'description' => 'Hipótesis h1 sobre trabajo remoto',
                'secondary_hypotheses' => 'H1', 'user_id' => 2, 'state' => '0', 'tried_id' => 2, 'edits' => 0
            ]
        ];

        foreach ($hypotheses as $data) {
            Hypothesis::create($data);
        }

        $adminScenarios = [
            [ 'id' => 1, 'titulo' => 'Escenario Tecnológico Optimista', 'num_scenario' => 1, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 2, 'edits_year1' => 1, 'edits_year2' => 1, 'edits_year3' => 0, 'state' => '1', 'user_id' => 1, 'tried_id' => 1 ],
            [ 'id' => 2, 'titulo' => 'Escenario de Transformación Digital', 'num_scenario' => 2, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 1, 'edits_year1' => 1, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 1, 'tried_id' => 1 ],
            [ 'id' => 3, 'titulo' => 'Escenario de Desafíos Climáticos', 'num_scenario' => 3, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 1, 'edits_year1' => 1, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 1, 'tried_id' => 1 ],
            [ 'id' => 4, 'titulo' => 'Escenario de Integración Global', 'num_scenario' => 4, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 0, 'edits_year1' => 0, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 1, 'tried_id' => 1 ]
        ];

        $userScenarios = [
            [ 'id' => 5, 'titulo' => 'Escenario Empresarial Sostenible', 'num_scenario' => 1, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 1, 'edits_year1' => 1, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 2, 'tried_id' => 2 ],
            [ 'id' => 6, 'titulo' => 'Escenario de Trabajo Híbrido', 'num_scenario' => 2, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 0, 'edits_year1' => 0, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 2, 'tried_id' => 2 ],
            [ 'id' => 7, 'titulo' => 'Escenario de Comercio Digital', 'num_scenario' => 3, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 2, 'edits_year1' => 1, 'edits_year2' => 1, 'edits_year3' => 0, 'state' => '1', 'user_id' => 2, 'tried_id' => 2 ],
            [ 'id' => 8, 'titulo' => 'Escenario de Innovación Indiferente', 'num_scenario' => 4, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 0, 'edits_year1' => 0, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 2, 'tried_id' => 2 ]
        ];

        $allScenarios = array_merge($adminScenarios, $userScenarios);
        foreach ($allScenarios as $data) {
            Scenarios::create($data);
        }

        $adminConclusions = [
            [
                'id' => 1,
                'component_practice' => 'El uso de IA en procesos administrativos mejora la eficiencia.',
                'component_practice_edits' => 1,
                'actuality' => 'Actualmente, la IA se implementa en áreas clave de la organización.',
                'actuality_edits' => 1,
                'aplication' => 'Se recomienda capacitar al personal en herramientas de IA.',
                'aplication_edits' => 0,
                'user_id' => 1,
                'state' => '1',
                'tried_id' => 1
            ]
        ];

        $userConclusions = [
            [
                'id' => 2,
                'component_practice' => 'El trabajo remoto incrementa la satisfacción laboral.',
                'component_practice_edits' => 1,
                'actuality' => 'El 80% del equipo trabaja desde casa al menos dos días a la semana.',
                'actuality_edits' => 1,
                'aplication' => 'Fomentar políticas de flexibilidad horaria y teletrabajo.',
                'aplication_edits' => 0,
                'user_id' => 2,
                'state' => '1',
                'tried_id' => 2
            ]
        ];

        $allConclusions = array_merge($adminConclusions, $userConclusions);
        foreach ($allConclusions as $data) {
            Conclusion::create($data);
        }

        $this->command->info('✅ Datos de prueba completos creados exitosamente!');
        $this->command->info('👥 Usuarios creados:');
        $this->command->info('   - admin (Natural - Administrador)');
        $this->command->info('   - usuario (Natural - Usuario)');
        $this->command->info('   - empresa (Empresa - TechCorp Solutions)');
        $this->command->info('   - empresa2 (Empresa - EcoGreen Industries)');
        $this->command->info('🔑 Contraseña para todos: password');
    }
}