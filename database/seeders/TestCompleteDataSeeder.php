<?php

namespace Database\Seeders;

use App\Models\Variable;
use App\Models\Matriz;
use App\Models\VariableMapAnalisys;
use App\Models\Hypothesis;
use App\Models\Scenarios;
use App\Models\Conclusion;
use App\Models\Zones;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestCompleteDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar datos existentes para los usuarios específicos (en orden correcto)
        DB::statement('DELETE FROM conclusions WHERE user_id IN (1, 2)');
        DB::statement('DELETE FROM scenarios WHERE user_id IN (1, 2)');
        DB::statement('DELETE FROM hypothesis WHERE user_id IN (1, 2)');
        DB::statement('DELETE FROM variables_map_analiyis WHERE user_id IN (1, 2)');
        DB::statement('DELETE FROM matriz WHERE user_id IN (1, 2)');
        DB::statement('DELETE FROM variables WHERE user_id IN (1, 2)');

        // Reiniciar auto-incremento de todas las tablas
        DB::statement('ALTER TABLE variables AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE matriz AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE variables_map_analiyis AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE hypothesis AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE scenarios AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE conclusions AUTO_INCREMENT = 1');

        // 1. Crear variables y mapear sus IDs
        $adminVariables = [
            [ 'id' => 1, 'id_variable' => 'V1', 'name_variable' => 'Tecnología Digital', 'description' => 'Avances en tecnologías digitales y su impacto en la sociedad', 'score' => 15, 'user_id' => 1, 'state' => '0', 'now_condition' => 'Infraestructura digital avanzada y conectividad total.' ],
            [ 'id' => 2, 'id_variable' => 'V2', 'name_variable' => 'Cambio Climático', 'description' => 'Efectos del cambio climático en el medio ambiente', 'score' => 12, 'user_id' => 1, 'state' => '0', 'now_condition' => 'Aumento de eventos climáticos extremos en la región.' ],
            [ 'id' => 3, 'id_variable' => 'V3', 'name_variable' => 'Globalización Económica', 'description' => 'Procesos de integración económica mundial', 'score' => 18, 'user_id' => 1, 'state' => '0', 'now_condition' => 'Mercados internacionales abiertos y competitivos.' ],
            [ 'id' => 4, 'id_variable' => 'V4', 'name_variable' => 'Educación Virtual', 'description' => 'Transformación de la educación hacia modalidades virtuales', 'score' => 10, 'user_id' => 1, 'state' => '0', 'now_condition' => 'Alta adopción de plataformas de educación online.' ],
            [ 'id' => 5, 'id_variable' => 'V5', 'name_variable' => 'Inteligencia Artificial', 'description' => 'Desarrollo y aplicación de IA en diversos sectores', 'score' => 20, 'user_id' => 1, 'state' => '0', 'now_condition' => 'IA presente en procesos productivos y administrativos.' ]
        ];
        $userVariables = [
            [ 'id' => 6, 'id_variable' => 'V1', 'name_variable' => 'Sostenibilidad Empresarial', 'description' => 'Prácticas sostenibles en el sector empresarial', 'score' => 14, 'user_id' => 2, 'state' => '0', 'now_condition' => 'Empresas implementan políticas de reciclaje y eficiencia energética.' ],
            [ 'id' => 7, 'id_variable' => 'V2', 'name_variable' => 'Trabajo Remoto', 'description' => 'Tendencias del trabajo a distancia y sus implicaciones', 'score' => 16, 'user_id' => 2, 'state' => '0', 'now_condition' => 'El 80% del personal trabaja desde casa al menos dos días a la semana.' ],
            [ 'id' => 8, 'id_variable' => 'V3', 'name_variable' => 'Comercio Electrónico', 'description' => 'Crecimiento del comercio online y su impacto', 'score' => 13, 'user_id' => 2, 'state' => '0', 'now_condition' => 'Aumento sostenido de ventas online y digitalización de pagos.' ]
        ];
        $adminVarIds = [];
        foreach ($adminVariables as $data) {
            $v = Variable::create($data);
            $adminVarIds[$data['id_variable']] = $v->id;
        }
        $userVarIds = [];
        foreach ($userVariables as $data) {
            $v = Variable::create($data);
            $userVarIds[$data['id_variable']] = $v->id;
        }

        // 2. Matriz para el usuario administrador (usar IDs reales)
        $adminMatriz = [
            // V1 vs V2
            ['id' => 1, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V1'], 'id_resp_depen' => $adminVarIds['V2'], 'id_resp_influ' => 3, 'user_id' => 1, 'state' => '1'],
            ['id' => 2, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V2'], 'id_resp_depen' => $adminVarIds['V1'], 'id_resp_influ' => 2, 'user_id' => 1, 'state' => '1'],
            // V1 vs V3
            ['id' => 3, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V1'], 'id_resp_depen' => $adminVarIds['V3'], 'id_resp_influ' => 2, 'user_id' => 1, 'state' => '1'],
            ['id' => 4, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V3'], 'id_resp_depen' => $adminVarIds['V1'], 'id_resp_influ' => 1, 'user_id' => 1, 'state' => '1'],
            // V1 vs V4
            ['id' => 5, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V1'], 'id_resp_depen' => $adminVarIds['V4'], 'id_resp_influ' => 1, 'user_id' => 1, 'state' => '1'],
            ['id' => 6, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V4'], 'id_resp_depen' => $adminVarIds['V1'], 'id_resp_influ' => 2, 'user_id' => 1, 'state' => '1'],
            // V1 vs V5
            ['id' => 7, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V1'], 'id_resp_depen' => $adminVarIds['V5'], 'id_resp_influ' => 3, 'user_id' => 1, 'state' => '1'],
            ['id' => 8, 'id_matriz' => 1, 'id_variable' => $adminVarIds['V5'], 'id_resp_depen' => $adminVarIds['V1'], 'id_resp_influ' => 1, 'user_id' => 1, 'state' => '1'],
        ];
        foreach ($adminMatriz as $data) {
            Matriz::create($data);
        }
        // Matriz para el usuario normal
        $userMatriz = [
            ['id' => 9, 'id_matriz' => 2, 'id_variable' => $userVarIds['V1'], 'id_resp_depen' => $userVarIds['V2'], 'id_resp_influ' => 2, 'user_id' => 2, 'state' => '1'],
            ['id' => 10, 'id_matriz' => 2, 'id_variable' => $userVarIds['V2'], 'id_resp_depen' => $userVarIds['V1'], 'id_resp_influ' => 2, 'user_id' => 2, 'state' => '1'],
            ['id' => 11, 'id_matriz' => 2, 'id_variable' => $userVarIds['V1'], 'id_resp_depen' => $userVarIds['V3'], 'id_resp_influ' => 3, 'user_id' => 2, 'state' => '1'],
            ['id' => 12, 'id_matriz' => 2, 'id_variable' => $userVarIds['V3'], 'id_resp_depen' => $userVarIds['V1'], 'id_resp_influ' => 1, 'user_id' => 2, 'state' => '1'],
        ];
        foreach ($userMatriz as $data) {
            Matriz::create($data);
        }

        // 3. Análisis de variables por zona para el administrador
        $adminAnalyses = [
            [ 'id' => 1, 'zone_id' => 1, 'description' => 'Las variables de tecnología digital e inteligencia artificial muestran alta influencia y dependencia, posicionándolas como elementos clave del poder en el sistema.', 'score' => 85, 'user_id' => 1 ],
            [ 'id' => 2, 'zone_id' => 2, 'description' => 'El cambio climático presenta alta influencia pero baja dependencia, generando tensiones y conflictos en el sistema.', 'score' => 72, 'user_id' => 1 ],
            [ 'id' => 3, 'zone_id' => 3, 'description' => 'La globalización económica muestra baja influencia y dependencia, siendo un elemento de salida del sistema.', 'score' => 45, 'user_id' => 1 ],
            [ 'id' => 4, 'zone_id' => 4, 'description' => 'La educación virtual presenta características de zona de indiferencia con valores moderados.', 'score' => 38, 'user_id' => 1 ]
        ];
        foreach ($adminAnalyses as $data) {
            DB::table('variables_map_analiyis')->insert($data);
        }
        $userAnalyses = [
            [ 'id' => 5, 'zone_id' => 1, 'description' => 'La sostenibilidad empresarial muestra alta influencia y dependencia, siendo un elemento de poder en el sistema empresarial.', 'score' => 78, 'user_id' => 2 ],
            [ 'id' => 6, 'zone_id' => 2, 'description' => 'El trabajo remoto presenta alta influencia pero baja dependencia, generando conflictos en las organizaciones.', 'score' => 65, 'user_id' => 2 ],
            [ 'id' => 7, 'zone_id' => 3, 'description' => 'El comercio electrónico muestra características de zona de salida con baja influencia y dependencia.', 'score' => 42, 'user_id' => 2 ],
            [ 'id' => 8, 'zone_id' => 4, 'description' => 'La innovación en productos es percibida como indiferente por el mercado actual.', 'score' => 30, 'user_id' => 2 ]
        ];
        foreach ($userAnalyses as $data) {
            DB::table('variables_map_analiyis')->insert($data);
        }

        // 4. Hipótesis SOLO para el usuario 1, exactamente como la imagen
        DB::statement('DELETE FROM hypothesis WHERE user_id IN (1,2)');
        DB::statement('ALTER TABLE hypothesis AUTO_INCREMENT = 1');
        $hypotheses = [
            // Usuario 1
            [
                'id' => 1,
                'id_variable' => 5,
                'zone_id' => 4,
                'name_hypothesis' => 'H1',
                'description' => 'Hipótesis sobre el impacto de la Inteligencia Artificial.',
                'secondary_hypotheses' => 'H0',
                'user_id' => 1,
                'state' => '0',
                'created_at' => '2025-07-12 17:36:08',
                'updated_at' => '2025-07-12 17:36:08',
            ],
            [
                'id' => 2,
                'id_variable' => 5,
                'zone_id' => 4,
                'name_hypothesis' => 'H1',
                'description' => 'Evaluación alternativa de la Inteligencia Artificial.',
                'secondary_hypotheses' => 'H1',
                'user_id' => 1,
                'state' => '0',
                'created_at' => '2025-07-12 17:36:09',
                'updated_at' => '2025-07-12 17:36:09',
            ],
            [
                'id' => 3,
                'id_variable' => 2,
                'zone_id' => 4,
                'name_hypothesis' => 'H2',
                'description' => 'Hipótesis sobre los efectos del Cambio Climático.',
                'secondary_hypotheses' => 'H0',
                'user_id' => 1,
                'state' => '0',
                'created_at' => '2025-07-12 17:36:16',
                'updated_at' => '2025-07-12 17:36:16',
            ],
            [
                'id' => 4,
                'id_variable' => 2,
                'zone_id' => 4,
                'name_hypothesis' => 'H2',
                'description' => 'Análisis alternativo del Cambio Climático.',
                'secondary_hypotheses' => 'H1',
                'user_id' => 1,
                'state' => '0',
                'created_at' => '2025-07-12 17:36:16',
                'updated_at' => '2025-07-12 17:36:16',
            ],
            // Usuario 2 (de la imagen)
            [
                'id' => 5,
                'id_variable' => 8,
                'zone_id' => 4,
                'name_hypothesis' => 'H1',
                'description' => 'Hipótesis h0 sobre comercio electrónico',
                'secondary_hypotheses' => 'H0',
                'user_id' => 2,
                'state' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'id_variable' => 8,
                'zone_id' => 4,
                'name_hypothesis' => 'H1',
                'description' => 'Hipótesis h1 sobre comercio electrónico',
                'secondary_hypotheses' => 'H1',
                'user_id' => 2,
                'state' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'id_variable' => 7,
                'zone_id' => 4,
                'name_hypothesis' => 'H2',
                'description' => 'Hipótesis h0 sobre trabajo remoto',
                'secondary_hypotheses' => 'H0',
                'user_id' => 2,
                'state' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'id_variable' => 7,
                'zone_id' => 4,
                'name_hypothesis' => 'H2',
                'description' => 'Hipótesis h1 sobre trabajo remoto',
                'secondary_hypotheses' => 'H1',
                'user_id' => 2,
                'state' => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        foreach ($hypotheses as $data) {
            \App\Models\Hypothesis::create($data);
        }

        // 5. Escenarios para el administrador
        $adminScenarios = [
            [ 'id' => 1, 'titulo' => 'Escenario Tecnológico Optimista', 'num_scenario' => 1, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 2, 'edits_year1' => 1, 'edits_year2' => 1, 'edits_year3' => 0, 'state' => '1', 'user_id' => 1 ],
            [ 'id' => 2, 'titulo' => 'Escenario de Transformación Digital', 'num_scenario' => 2, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 1, 'edits_year1' => 1, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 1 ],
            [ 'id' => 3, 'titulo' => 'Escenario de Desafíos Climáticos', 'num_scenario' => 3, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 1, 'edits_year1' => 1, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 1 ],
            [ 'id' => 4, 'titulo' => 'Escenario de Integración Global', 'num_scenario' => 4, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 0, 'edits_year1' => 0, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 1 ]
        ];
        foreach ($adminScenarios as $data) {
            Scenarios::create($data);
        }
        $userScenarios = [
            [ 'id' => 5, 'titulo' => 'Escenario Empresarial Sostenible', 'num_scenario' => 1, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 1, 'edits_year1' => 1, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 2 ],
            [ 'id' => 6, 'titulo' => 'Escenario de Trabajo Híbrido', 'num_scenario' => 2, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 0, 'edits_year1' => 0, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 2 ],
            [ 'id' => 7, 'titulo' => 'Escenario de Comercio Digital', 'num_scenario' => 3, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 2, 'edits_year1' => 1, 'edits_year2' => 1, 'edits_year3' => 0, 'state' => '1', 'user_id' => 2 ],
            [ 'id' => 8, 'titulo' => 'Escenario de Innovación Indiferente', 'num_scenario' => 4, 'year1' => '2025', 'year2' => '2030', 'year3' => '2035', 'edits' => 0, 'edits_year1' => 0, 'edits_year2' => 0, 'edits_year3' => 0, 'state' => '1', 'user_id' => 2 ]
        ];
        foreach ($userScenarios as $data) {
            Scenarios::create($data);
        }

        // 6. Conclusiones para el administrador
        $adminConclusions = [
            [
                'id' => 1,
                'component_practice' => 'El uso de IA en procesos administrativos mejora la eficiencia.',
                'actuality' => 'Actualmente, la IA se implementa en áreas clave de la organización.',
                'aplication' => 'Se recomienda capacitar al personal en herramientas de IA.',
                'user_id' => 1
            ]
        ];
        foreach ($adminConclusions as $data) {
            Conclusion::create($data);
        }
        $userConclusions = [
            [
                'id' => 2,
                'component_practice' => 'El trabajo remoto incrementa la satisfacción laboral.',
                'actuality' => 'El 80% del equipo trabaja desde casa al menos dos días a la semana.',
                'aplication' => 'Fomentar políticas de flexibilidad horaria y teletrabajo.',
                'user_id' => 2
            ]
        ];
        foreach ($userConclusions as $data) {
            Conclusion::create($data);
        }
    }
} 