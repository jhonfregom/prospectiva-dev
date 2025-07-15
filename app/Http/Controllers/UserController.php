<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PasswordChangeRequest;
use App\Models\StatePasswordChangeRequest;
use App\Models\UpdatedPassword;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $rules_update =   [
        'id'                =>  'required|exists:users,id',
        'name'              =>  'nullable|min:3|max:200',
        'user'              =>  'nullable|max:100|unique:users,user',
        'password'          =>  'nullable',
        'roles_id'          =>  'nullable|exists:roles,id',
        'status_users_id'   =>  'nullable|exists:status_users,id'
    ];

    /**
     * Show the form for restore password.
     * @return \Illuminate\Http\Response
     */
    public function showPasswordReset(): View
    {
        return view('login.restore-password');
    }

    /**
     * Password reset from link
     * @param  \Illuminate\Http\Request  $request
     */
    public function passwordReset( Request $request )
    {
        $data = null;
        $code = 200;

        if( isset($request->data ) )
        {
            try
            {
                //Decrypt params
                $params = Crypt::decrypt( $request->data ); // id => value
                //Get password change requests
                $pcr = PasswordChangeRequest::find( $params['id'] );
                if( isset( $pcr ) )
                {
                    $data = [ 'exists' => true ];
                    $data['password_change_request'] = $pcr;
                }else{
                    $data = [ 'exists' => false ];
                }
            }catch( DecryptException $e){
                $data = [
                    'exists'    => false,
                    'msg'     => $e->getMessage()
                ];
            }
        }else{
            $data = [
                        'fails' => [
                    'error' => __('messages.bad_request'),
                    'data'  => __('validation.required', ['attribute' => 'data']),
                ]
            ];
            $code = 400;
        }
        // dd($data);
        return response()->view( 'users.password-reset', $data, $code );
    }

    /**
     * API: Listado de usuarios para la sección de resultados
     */
    public function apiList(Request $request)
    {
        $user = Auth::user();
        if ($user->role == 1) {
            // Admin: ve todos
            $users = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')->get();
        } else {
            // Usuario: solo ve el suyo
            $users = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')->where('id', $user->id)->get();
        }

        // Agregar información de variables para cada usuario
        foreach ($users as $userData) {
            $variables = \App\Models\Variable::where('user_id', $userData->id)->get();
            $userData->variables_count = $variables->count();
            $userData->variables_list = $variables->map(function($variable) {
                return [
                    'id_variable' => $variable->id_variable,
                    'name_variable' => $variable->name_variable,
                    'description' => $variable->description,
                    'score' => $variable->score
                ];
            });

            // Agregar información de matriz
            $matriz = \App\Models\Matriz::where('user_id', $userData->id)->get();
            $variables = \App\Models\Variable::where('user_id', $userData->id)->get();
            
            // Calcular dependencia e influencia para cada variable (misma lógica que la gráfica)
            $matrizData = [];
            foreach ($variables as $variable) {
                $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
                $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
                
                // Determinar la zona basada en las coordenadas
                $zoneId = $this->determineZone($dependencia, $influencia, $variables);
                
                $matrizData[] = [
                    'id_variable' => $variable->id_variable,
                    'name_variable' => $variable->name_variable,
                    'dependencia' => $dependencia,
                    'influencia' => $influencia,
                    'zone_id' => $zoneId
                ];
            }
            $userData->matriz = $matrizData;

            // --- NUEVO: matriz cruzada ---
            $matrizCruzada = [];
            foreach ($variables as $origen) {
                foreach ($variables as $destino) {
                    if ($origen->id === $destino->id) continue;
                    $valor = $matriz->where('id_variable', $origen->id)->where('id_resp_depen', $destino->id)->value('id_resp_influ');
                    $matrizCruzada[] = [
                        'origen' => $origen->id_variable,
                        'destino' => $destino->id_variable,
                        'valor' => $valor !== null ? intval($valor) : 0
                    ];
                }
            }
            $userData->matriz_cruzada = $matrizCruzada;

            // Agregar información de análisis de variables por zona
            $analyses = \App\Models\VariableMapAnalisys::where('user_id', $userData->id)->get();
            $userData->zone_analyses = $analyses->map(function($analysis) use ($userData, $matriz) {
                // Obtener el nombre de la zona
                $zone = \App\Models\Zones::find($analysis->zone_id);
                $zoneName = $zone ? $zone->name_zones : 'Zona Desconocida';
                
                // Obtener las variables del usuario
                $variables = \App\Models\Variable::where('user_id', $userData->id)->get();
                
                // Calcular coordenadas de cada variable usando la matriz
                $variablesWithCoords = [];
                foreach ($variables as $variable) {
                    // Calcular dependencia (suma de la fila donde esta variable es origen)
                    $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
                    // Calcular influencia (suma de la columna donde esta variable es destino)
                    $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
                    
                    $variablesWithCoords[] = [
                        'variable' => $variable,
                        'dependencia' => $dependencia,
                        'influencia' => $influencia
                    ];
                }
                
                // Calcular máximos para determinar el centro (igual que en el módulo analysis)
                $maxX = max(array_column($variablesWithCoords, 'dependencia')) ?: 10;
                $maxY = max(array_column($variablesWithCoords, 'influencia')) ?: 12;
                $centroX = $maxX / 2;
                $centroY = $maxY / 2;
                
                // Determinar variables en esta zona específica (lógica exacta del módulo analysis)
                $variablesInThisZone = [];
                foreach ($variablesWithCoords as $varData) {
                    $zona = '';
                    $esFrontera = false;
                    
                    // Lógica exacta del módulo analysis (corregida)
                    if ($varData['influencia'] === $centroX || $varData['dependencia'] === $centroY) {
                        $esFrontera = true;
                        if ($varData['influencia'] > $centroX && $varData['dependencia'] >= $centroY) $zona = 'conflicto';
                        else if ($varData['influencia'] <= $centroX && $varData['dependencia'] > $centroY) $zona = 'poder';
                        else if ($varData['influencia'] > $centroX && $varData['dependencia'] < $centroY) $zona = 'salida';
                        else $zona = 'indiferencia';
                    } else {
                        if ($varData['influencia'] <= $centroX && $varData['dependencia'] > $centroY) $zona = 'poder';
                        else if ($varData['influencia'] > $centroX && $varData['dependencia'] >= $centroY) $zona = 'conflicto';
                        else if ($varData['influencia'] <= $centroX && $varData['dependencia'] <= $centroY) $zona = 'indiferencia';
                        else if ($varData['influencia'] > $centroX && $varData['dependencia'] < $centroY) $zona = 'salida';
                    }
                    
                    // Mapear el nombre de la zona a la clave del análisis
                    $zoneMapping = [
                        'ZONA DE PODER' => 'poder',
                        'ZONA DE CONFLICTO' => 'conflicto',
                        'ZONA DE SALIDA' => 'salida',
                        'ZONA DE INDIFERENCIA' => 'indiferencia'
                    ];
                    
                    $analysisZoneKey = $zoneMapping[$zoneName] ?? strtolower(str_replace('ZONA DE ', '', $zoneName));
                    
                    if ($zona === $analysisZoneKey) {
                        $variablesInThisZone[] = [
                            'id_variable' => $varData['variable']->id_variable,
                            'name_variable' => $varData['variable']->name_variable,
                            'dependencia' => $varData['dependencia'],
                            'influencia' => $varData['influencia'],
                            'frontera' => $esFrontera
                        ];
                    }
                }
                
                // Calcular el puntaje basado en el número de palabras del comentario (igual que en el frontend)
                $description = $analysis->description ?? '';
                $words = array_filter(explode(' ', trim($description)));
                $calculatedScore = count($words);
                
                return [
                    'zone_id' => $analysis->zone_id,
                    'zone_name' => $zoneName,
                    'description' => $analysis->description,
                    'score' => $calculatedScore, // Usar el puntaje calculado dinámicamente
                    'state' => $analysis->state,
                    'variables_in_zone' => $variablesInThisZone
                ];
            });

            // Agregar información de direccionadores de futuro (hipótesis)
            $hypotheses = \App\Models\Hypothesis::where('user_id', $userData->id)->get();
            $userData->future_drivers = $hypotheses->map(function($hypothesis) {
                // Obtener información de la variable
                $variable = \App\Models\Variable::find($hypothesis->id_variable);
                $variableName = $variable ? $variable->name_variable : 'Variable Desconocida';
                
                return [
                    'id' => $hypothesis->id,
                    'variable_id' => $hypothesis->id_variable,
                    'variable_name' => $variableName,
                    'name_hypothesis' => $hypothesis->name_hypothesis,
                    'description' => $hypothesis->description,
                    'secondary_hypotheses' => $hypothesis->secondary_hypotheses,
                    'state' => $hypothesis->state
                ];
            });

            // Agregar información de condiciones iniciales
            $variablesWithConditions = \App\Models\Variable::where('user_id', $userData->id)
                ->whereNotNull('now_condition')
                ->where('now_condition', '!=', '')
                ->get();
            
            $userData->initial_conditions = $variablesWithConditions->map(function($variable) {
                return [
                    'id' => $variable->id,
                    'variable_id' => $variable->id_variable,
                    'variable_name' => $variable->name_variable,
                    'now_condition' => $variable->now_condition,
                    'state' => $variable->state
                ];
            });

            // Agregar información de escenarios
            $scenarios = \App\Models\Scenarios::where('user_id', $userData->id)
                ->orderBy('num_scenario', 'asc')
                ->get();
            
            // Obtener todas las hipótesis del usuario para asociarlas a los escenarios
            $allHypotheses = \App\Models\Hypothesis::where('user_id', $userData->id)->get();
            
            $userData->scenarios = $scenarios->map(function($scenario) use ($allHypotheses) {
                // Determinar las hipótesis asociadas según el num_scenario
                $associatedHypotheses = [];
                
                if ($allHypotheses->count() >= 2) {
                    // Obtener las hipótesis organizadas por variable
                    $hypothesesByVariable = $allHypotheses->groupBy('id_variable');
                    $variables = $hypothesesByVariable->keys();
                    
                    // Obtener la primera y segunda variable (ordenadas por ID)
                    $sortedVariables = $variables->sort();
                    $firstVariableId = $sortedVariables->first();
                    $secondVariableId = $sortedVariables->skip(1)->first();
                    
                    // Obtener hipótesis H0 y H1 para cada variable
                    $h0_first = $allHypotheses->where('id_variable', $firstVariableId)->where('secondary_hypotheses', 'H0')->first();
                    $h1_first = $allHypotheses->where('id_variable', $firstVariableId)->where('secondary_hypotheses', 'H1')->first();
                    $h0_second = $allHypotheses->where('id_variable', $secondVariableId)->where('secondary_hypotheses', 'H0')->first();
                    $h1_second = $allHypotheses->where('id_variable', $secondVariableId)->where('secondary_hypotheses', 'H1')->first();
                    
                    // Obtener información de las variables
                    $firstVariable = $firstVariableId ? \App\Models\Variable::find($firstVariableId) : null;
                    $secondVariable = $secondVariableId ? \App\Models\Variable::find($secondVariableId) : null;
                    
                    // Definir todas las hipótesis disponibles
                    $allAvailableHypotheses = [
                        'H1+' => [
                            'id' => $h1_first ? $h1_first->id : null,
                            'name_hypothesis' => 'HIPÓTESIS 1+',
                            'variable_name' => $firstVariable ? $firstVariable->name_variable : 'Variable Desconocida',
                            'description' => $h1_first ? $h1_first->description : ''
                        ],
                        'H1-' => [
                            'id' => $h0_first ? $h0_first->id : null,
                            'name_hypothesis' => 'HIPÓTESIS 1-',
                            'variable_name' => $firstVariable ? $firstVariable->name_variable : 'Variable Desconocida',
                            'description' => $h0_first ? $h0_first->description : ''
                        ],
                        'H2+' => [
                            'id' => $h1_second ? $h1_second->id : null,
                            'name_hypothesis' => 'HIPÓTESIS 2+',
                            'variable_name' => $secondVariable ? $secondVariable->name_variable : 'Variable Desconocida',
                            'description' => $h1_second ? $h1_second->description : ''
                        ],
                        'H2-' => [
                            'id' => $h0_second ? $h0_second->id : null,
                            'name_hypothesis' => 'HIPÓTESIS 2-',
                            'variable_name' => $secondVariable ? $secondVariable->name_variable : 'Variable Desconocida',
                            'description' => $h0_second ? $h0_second->description : ''
                        ]
                    ];
                    
                    // Seleccionar hipótesis según el escenario
                    switch ($scenario->num_scenario) {
                        case 1: // H1, H1
                            $associatedHypotheses = [$allAvailableHypotheses['H1+'], $allAvailableHypotheses['H2+']];
                            break;
                        case 2: // H2+, H1- (corregido)
                            $associatedHypotheses = [$allAvailableHypotheses['H2+'], $allAvailableHypotheses['H1-']];
                            break;
                        case 3: // H0, H0
                            $associatedHypotheses = [$allAvailableHypotheses['H1-'], $allAvailableHypotheses['H2-']];
                            break;
                        case 4: // H2-, H1+ (corregido)
                            $associatedHypotheses = [$allAvailableHypotheses['H2-'], $allAvailableHypotheses['H1+']];
                            break;
                    }
                }
                
                return [
                    'id' => $scenario->id,
                    'num_scenario' => $scenario->num_scenario,
                    'titulo' => $scenario->titulo,
                    'year1' => $scenario->year1,
                    'year2' => $scenario->year2,
                    'year3' => $scenario->year3,
                    'edits' => $scenario->edits,
                    'edits_year1' => $scenario->edits_year1,
                    'edits_year2' => $scenario->edits_year2,
                    'edits_year3' => $scenario->edits_year3,
                    'state' => $scenario->state,
                    'hypotheses' => $associatedHypotheses
                ];
            });

        // Agregar información de conclusiones
        $conclusions = \App\Models\Conclusion::where('user_id', $userData->id)
            ->orderBy('id', 'asc')
            ->get();
        
        \Log::info('Conclusiones encontradas para usuario ' . $userData->id . ':', $conclusions->toArray());
        
        $userData->conclusions = $conclusions->map(function($conclusion) {
            \Log::info('Procesando conclusión ID ' . $conclusion->id . ':', [
                'component_practice' => $conclusion->component_practice,
                'actuality' => $conclusion->actuality,
                'aplication' => $conclusion->aplication
            ]);
            
            return [
                'id' => $conclusion->id,
                'title' => 'Conclusión del Usuario',
                'state' => $conclusion->state,
                'component_practice' => $conclusion->component_practice,
                'actuality' => $conclusion->actuality,
                'aplication' => $conclusion->aplication
            ];
        });
    }

        return response()->json([
            'status' => 200,
            'data' => $users
        ]);
    }

    /**
     * Determina la zona de una variable basada en sus coordenadas de dependencia e influencia
     */
    private function determineZone($dependencia, $influencia, $variables)
    {
        if ($variables->isEmpty()) {
            return 1; // Zona de Poder por defecto
        }

        // Calcular máximos para normalizar
        $maxDependencia = $variables->max('dependencia') ?: 10;
        $maxInfluencia = $variables->max('influencia') ?: 12;
        
        // Normalizar coordenadas
        $normalizedDependencia = $dependencia / $maxDependencia;
        $normalizedInfluencia = $influencia / $maxInfluencia;
        
        // Determinar zona basada en las coordenadas normalizadas
        if ($normalizedInfluencia > 0.5 && $normalizedDependencia < 0.5) {
            return 1; // Zona de Poder
        } elseif ($normalizedInfluencia > 0.5 && $normalizedDependencia > 0.5) {
            return 2; // Zona de Problemas
        } elseif ($normalizedInfluencia < 0.5 && $normalizedDependencia < 0.5) {
            return 3; // Zona de Resultados
        } else {
            return 4; // Zona de Contexto
        }
    }
}
