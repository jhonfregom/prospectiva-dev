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

    public function showPasswordReset(): View
    {
        return view('login.restore-password');
    }

    public function passwordReset( Request $request )
    {
        $data = null;
        $code = 200;

        if( isset($request->data ) )
        {
            try
            {
                
                $params = Crypt::decrypt( $request->data ); 
                
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
        
        return response()->view( 'users.password-reset', $data, $code );
    }

    public function apiList(Request $request)
    {
        $user = Auth::user();
        $result = [];
        
        \Log::info('UserController::apiList - Usuario: ' . json_encode(['id' => $user->id, 'role' => $user->role, 'email' => $user->user]));
        
        if ($user->role == 1) {
            
            $users = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')->get();
            \Log::info('UserController::apiList - Usuarios encontrados: ' . $users->count());

            foreach ($users as $userData) {
                
                $userRoutes = \App\Models\Traceability::where('user_id', $userData->id)->get();
                \Log::info('UserController::apiList - Usuario ' . $userData->id . ' tiene ' . $userRoutes->count() . ' rutas');
                
                if ($userRoutes->count() > 0) {
                    
                    foreach ($userRoutes as $route) {
                        $userRow = clone $userData;
                        $userRow->route_id = $route->id;
                        $userRow->route_name = 'Ruta ' . $route->tried;

                        $this->addUserDataByRoute($userRow, $route->id);
                        
                        $result[] = $userRow;
                    }
                } else {
                    
                    $userRow = clone $userData;
                    $userRow->route_id = null;
                    $userRow->route_name = 'Sin ruta';

                    $this->addEmptyUserData($userRow);
                    
                    $result[] = $userRow;
                }
            }
        } else {
            
            $userData = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')
                ->where('id', $user->id)
                ->first();
            
            if ($userData) {
                
                $userRoutes = \App\Models\Traceability::where('user_id', $userData->id)->get();
                
                if ($userRoutes->count() > 0) {
                    
                    foreach ($userRoutes as $route) {
                        $userRow = clone $userData;
                        $userRow->route_id = $route->id;
                        $userRow->route_name = 'Ruta ' . $route->tried;

                        $this->addUserDataByRoute($userRow, $route->id);
                        
                        $result[] = $userRow;
                    }
                } else {
                    
                    $userRow = clone $userData;
                    $userRow->route_id = null;
                    $userRow->route_name = 'Sin ruta';

                    $this->addEmptyUserData($userRow);
                    
                    $result[] = $userRow;
                }
            }
        }

        \Log::info('UserController::apiList - Total de resultados: ' . count($result));
        
        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    private function addEmptyUserData($userData)
    {
        $userData->variables_count = 0;
        $userData->variables_list = [];
        $userData->matriz = [];
        $userData->matriz_cruzada = [];
        $userData->zone_analyses = [];
        $userData->future_drivers = [];
        $userData->initial_conditions = [];
        $userData->scenarios = [];
        $userData->conclusions = [];
        $userData->status = 'Sin terminar';
    }

    public function apiListByRoute(Request $request)
    {
        $user = Auth::user();
        $routeId = $request->query('route_id');
        
        if (!$routeId) {
            return response()->json([
                'status' => 400,
                'message' => 'Se requiere route_id'
            ]);
        }

        $userRoutes = \App\Models\Traceability::where('user_id', $user->id)->get();
        
        if ($user->role == 1) {
            
            $users = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')->get();
        } else {
            
            $users = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')
                ->where('id', $user->id)
                ->get();
        }

        $result = [];

        foreach ($users as $userData) {
            foreach ($userRoutes as $route) {
                $userRow = clone $userData;

                $this->addUserDataByRoute($userRow, $route->id);
                
                $result[] = $userRow;
            }
        }

        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    private function addUserDataByRoute($userData, $routeId)
    {
        
        $route = \App\Models\Traceability::find($routeId);
        $routeNumber = $route ? $route->tried : $routeId;

        $userData->route_id = $routeId;
        $userData->route_name = 'Ruta ' . $routeNumber;

        $userData->status = $route->results === '1' ? 'Completado' : 'Sin terminar';

        $variables = \App\Models\Variable::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->orderByRaw('CAST(SUBSTRING(id_variable, 2) AS UNSIGNED) ASC')
            ->get();
        
        $userData->variables_count = $variables->count();
        $userData->variables_list = $variables->map(function($variable) {
            return [
                'id_variable' => $variable->id_variable,
                'name_variable' => $variable->name_variable,
                'description' => $variable->description,
                'score' => $variable->score
            ];
        });

        $matriz = \App\Models\Matriz::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->get();

        $matrizData = [];
        foreach ($variables as $variable) {
            $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
            $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
            
            $matrizData[] = [
                'id_variable' => $variable->id_variable,
                'name_variable' => $variable->name_variable,
                'dependencia' => $dependencia,
                'influencia' => $influencia
            ];
        }
        $userData->matriz = $matrizData;

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

        $analyses = \App\Models\VariableMapAnalisys::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->get();
        
        $userData->zone_analyses = $analyses->map(function($analysis) use ($userData, $matriz, $variables) {
            $zone = \App\Models\Zones::find($analysis->zone_id);
            $zoneName = $zone ? $zone->name_zones : 'Zona Desconocida';

            $variablesWithCoords = [];
            foreach ($variables as $variable) {
                $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
                $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
                
                $variablesWithCoords[] = [
                    'variable' => $variable,
                    'dependencia' => $dependencia,
                    'influencia' => $influencia
                ];
            }

            $maxX = max(array_column($variablesWithCoords, 'dependencia')) ?: 10;
            $maxY = max(array_column($variablesWithCoords, 'influencia')) ?: 12;
            $centroX = $maxX / 2;
            $centroY = $maxY / 2;

            $variablesInThisZone = [];
            foreach ($variablesWithCoords as $varData) {
                $zona = '';
                $esFrontera = false;
                
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
            
            $description = $analysis->description ?? '';
            $words = array_filter(explode(' ', trim($description)));
            $calculatedScore = count($words);
            
            return [
                'zone_id' => $analysis->zone_id,
                'zone_name' => $zoneName,
                'description' => $analysis->description,
                'score' => $calculatedScore,
                'state' => $analysis->state,
                'variables_in_zone' => $variablesInThisZone
            ];
        });

        // Usar la misma lógica del HypothesisController para seleccionar variables
        $varCoords = [];
        foreach ($variables as $variable) {
            $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
            $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
            
            $varCoords[] = [
                'id' => $variable->id,
                'name' => $variable->name_variable,
                'dependencia' => $dependencia,
                'influencia' => $influencia
            ];
        }

        $maxInfluencia = max(array_column($varCoords, 'influencia'));
        $minDependencia = min(array_column($varCoords, 'dependencia'));

        $porEncimaDiagonal = array_filter($varCoords, function($var) {
            return $var['influencia'] > $var['dependencia'];
        });

        $distanciaZonaPoder = function($var) use ($minDependencia, $maxInfluencia) {
            return sqrt(pow($var['dependencia'] - $minDependencia, 2) + pow($var['influencia'] - $maxInfluencia, 2));
        };

        if (count($porEncimaDiagonal) >= 2) {
            usort($porEncimaDiagonal, function($a, $b) use ($distanciaZonaPoder) {
                return $distanciaZonaPoder($a) <=> $distanciaZonaPoder($b);
            });
            $seleccionados = array_slice($porEncimaDiagonal, 0, 2);
        } elseif (count($porEncimaDiagonal) === 1) {
            $primera = array_values($porEncimaDiagonal)[0];
            $resto = array_filter($varCoords, function($var) use ($primera) {
                return $var['id'] !== $primera['id'];
            });
            usort($resto, function($a, $b) use ($distanciaZonaPoder) {
                return $distanciaZonaPoder($a) <=> $distanciaZonaPoder($b);
            });
            $seleccionados = [$primera, array_values($resto)[0]];
        } else {
            usort($varCoords, function($a, $b) use ($distanciaZonaPoder) {
                return $distanciaZonaPoder($a) <=> $distanciaZonaPoder($b);
            });
            $seleccionados = array_slice($varCoords, 0, 2);
        }

        // Obtener solo las hipótesis de las variables seleccionadas
        $selectedVariableIds = array_column($seleccionados, 'id');
        $hypotheses = \App\Models\Hypothesis::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->whereIn('id_variable', $selectedVariableIds)
            ->get();
        
        // Agrupar hipótesis por variable
        $groupedHypotheses = $hypotheses->groupBy('id_variable');
        
        $userData->future_drivers = $groupedHypotheses->map(function($variableHypotheses, $variableId) {
            $variable = \App\Models\Variable::find($variableId);
            $variableName = $variable ? $variable->name_variable : 'Variable Desconocida';
            
            // Obtener H0 y H1 para esta variable
            $h0 = $variableHypotheses->where('secondary_hypotheses', 'H0')->first();
            $h1 = $variableHypotheses->where('secondary_hypotheses', 'H1')->first();
            
            return [
                'id' => $variableId,
                'variable_id' => $variableId,
                'variable_name' => $variableName,
                'name_hypothesis' => $variableName,
                'h0_description' => $h0 ? $h0->description : null,
                'h1_description' => $h1 ? $h1->description : null,
                'state' => $h0 ? $h0->state : ($h1 ? $h1->state : '0')
            ];
        })->values();

        $userData->initial_conditions = $variables->map(function($variable) {
            return [
                'id' => $variable->id,
                'variable_id' => $variable->id_variable,
                'variable_name' => $variable->name_variable,
                'now_condition' => $variable->now_condition,
                'state' => $variable->state
            ];
        });

        $scenarios = \App\Models\Scenarios::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->orderBy('num_scenario', 'asc')
            ->get();
        
        $userData->scenarios = $scenarios->map(function($scenario) use ($hypotheses) {
            // Obtener las hipótesis específicas para cada escenario según el método de Schwartz
            $scenarioHypotheses = [];
            
            // Obtener las dos variables principales (las más cercanas a la zona de poder)
            $groupedHypotheses = $hypotheses->groupBy('id_variable');
            $mainVariables = $groupedHypotheses->take(2)->values();
            
            if ($mainVariables->count() >= 2) {
                $var1 = $mainVariables[0];
                $var2 = $mainVariables[1];
                
                // Obtener H0 y H1 para cada variable
                $h0_var1 = $var1->where('secondary_hypotheses', 'H0')->first();
                $h1_var1 = $var1->where('secondary_hypotheses', 'H1')->first();
                $h0_var2 = $var2->where('secondary_hypotheses', 'H0')->first();
                $h1_var2 = $var2->where('secondary_hypotheses', 'H1')->first();
                
                // Asignar hipótesis según el número de escenario
                switch ($scenario->num_scenario) {
                    case 1: // Escenario 1: H1+ y H2+
                        if ($h1_var1) {
                            $variable = \App\Models\Variable::find($h1_var1->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h1_var1->id,
                                'name_hypothesis' => 'Hipótesis 1+',
                                'description' => $h1_var1->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h1_var1->state
                            ];
                        }
                        if ($h1_var2) {
                            $variable = \App\Models\Variable::find($h1_var2->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h1_var2->id,
                                'name_hypothesis' => 'Hipótesis 2+',
                                'description' => $h1_var2->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h1_var2->state
                            ];
                        }
                        break;
                    case 2: // Escenario 2: H2+ y H1-
                        if ($h1_var2) {
                            $variable = \App\Models\Variable::find($h1_var2->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h1_var2->id,
                                'name_hypothesis' => 'Hipótesis 2+',
                                'description' => $h1_var2->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h1_var2->state
                            ];
                        }
                        if ($h0_var1) {
                            $variable = \App\Models\Variable::find($h0_var1->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h0_var1->id,
                                'name_hypothesis' => 'Hipótesis 1-',
                                'description' => $h0_var1->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h0_var1->state
                            ];
                        }
                        break;
                    case 3: // Escenario 3: H1- y H2-
                        if ($h0_var1) {
                            $variable = \App\Models\Variable::find($h0_var1->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h0_var1->id,
                                'name_hypothesis' => 'Hipótesis 1-',
                                'description' => $h0_var1->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h0_var1->state
                            ];
                        }
                        if ($h0_var2) {
                            $variable = \App\Models\Variable::find($h0_var2->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h0_var2->id,
                                'name_hypothesis' => 'Hipótesis 2-',
                                'description' => $h0_var2->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h0_var2->state
                            ];
                        }
                        break;
                    case 4: // Escenario 4: H2- y H1+
                        if ($h0_var2) {
                            $variable = \App\Models\Variable::find($h0_var2->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h0_var2->id,
                                'name_hypothesis' => 'Hipótesis 2-',
                                'description' => $h0_var2->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h0_var2->state
                            ];
                        }
                        if ($h1_var1) {
                            $variable = \App\Models\Variable::find($h1_var1->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h1_var1->id,
                                'name_hypothesis' => 'Hipótesis 1+',
                                'description' => $h1_var1->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h1_var1->state
                            ];
                        }
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
                'hypotheses' => $scenarioHypotheses
            ];
        });

        $conclusions = \App\Models\Conclusion::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->orderBy('id', 'asc')
            ->get();
        
        $conclusionsArray = [];
        
        // Si hay conclusiones reales, usar solo la primera
        if ($conclusions->count() > 0) {
            $conclusion = $conclusions->first();
            $conclusionsArray[] = [
                'id' => $conclusion->id,
                'title' => 'Conclusiones',
                'state' => $conclusion->state,
                'component_practice' => $conclusion->component_practice,
                'actuality' => $conclusion->actuality,
                'aplication' => $conclusion->aplication
            ];
        } else {
            // Si no hay conclusiones, crear una vacía
            $conclusionsArray[] = [
                'id' => 1,
                'title' => 'Conclusiones',
                'state' => '0',
                'component_practice' => null,
                'actuality' => null,
                'aplication' => null
            ];
        }
        
        $userData->conclusions = $conclusionsArray;

        $userData->route_id = $routeId; 
        $userData->route_name = "Ruta " . $routeNumber; 
    }

    private function addUserData($userData)
    {
        $variables = \App\Models\Variable::where('user_id', $userData->id)
            ->orderByRaw('CAST(SUBSTRING(id_variable, 2) AS UNSIGNED) ASC')
            ->get();
        $userData->variables_count = $variables->count();
        $userData->variables_list = $variables->map(function($variable) {
            return [
                'id_variable' => $variable->id_variable,
                'name_variable' => $variable->name_variable,
                'description' => $variable->description,
                'score' => $variable->score
            ];
        });

        $matriz = \App\Models\Matriz::where('user_id', $userData->id)->get();

        $matrizData = [];
        foreach ($variables as $variable) {
            $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
            $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
            
            $matrizData[] = [
                'id_variable' => $variable->id_variable,
                'name_variable' => $variable->name_variable,
                'dependencia' => $dependencia,
                'influencia' => $influencia
            ];
        }
        $userData->matriz = $matrizData;

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

        $analyses = \App\Models\VariableMapAnalisys::where('user_id', $userData->id)->get();
        $userData->zone_analyses = $analyses->map(function($analysis) use ($userData, $matriz, $variables) {
            $zone = \App\Models\Zones::find($analysis->zone_id);
            $zoneName = $zone ? $zone->name_zones : 'Zona Desconocida';

            $variablesWithCoords = [];
            foreach ($variables as $variable) {
                $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
                $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
                
                $variablesWithCoords[] = [
                    'variable' => $variable,
                    'dependencia' => $dependencia,
                    'influencia' => $influencia
                ];
            }

            $maxX = max(array_column($variablesWithCoords, 'dependencia')) ?: 10;
            $maxY = max(array_column($variablesWithCoords, 'influencia')) ?: 12;
            $centroX = $maxX / 2;
            $centroY = $maxY / 2;

            $variablesInThisZone = [];
            foreach ($variablesWithCoords as $varData) {
                $zona = '';
                $esFrontera = false;
                
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
            
            $description = $analysis->description ?? '';
            $words = array_filter(explode(' ', trim($description)));
            $calculatedScore = count($words);
            
            return [
                'zone_id' => $analysis->zone_id,
                'zone_name' => $zoneName,
                'description' => $analysis->description,
                'score' => $calculatedScore,
                'state' => $analysis->state,
                'variables_in_zone' => $variablesInThisZone
            ];
        });

        // Usar la misma lógica del HypothesisController para seleccionar variables
        $varCoords = [];
        foreach ($variables as $variable) {
            $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
            $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
            
            $varCoords[] = [
                'id' => $variable->id,
                'name' => $variable->name_variable,
                'dependencia' => $dependencia,
                'influencia' => $influencia
            ];
        }

        $maxInfluencia = max(array_column($varCoords, 'influencia'));
        $minDependencia = min(array_column($varCoords, 'dependencia'));

        $porEncimaDiagonal = array_filter($varCoords, function($var) {
            return $var['influencia'] > $var['dependencia'];
        });

        $distanciaZonaPoder = function($var) use ($minDependencia, $maxInfluencia) {
            return sqrt(pow($var['dependencia'] - $minDependencia, 2) + pow($var['influencia'] - $maxInfluencia, 2));
        };

        if (count($porEncimaDiagonal) >= 2) {
            usort($porEncimaDiagonal, function($a, $b) use ($distanciaZonaPoder) {
                return $distanciaZonaPoder($a) <=> $distanciaZonaPoder($b);
            });
            $seleccionados = array_slice($porEncimaDiagonal, 0, 2);
        } elseif (count($porEncimaDiagonal) === 1) {
            $primera = array_values($porEncimaDiagonal)[0];
            $resto = array_filter($varCoords, function($var) use ($primera) {
                return $var['id'] !== $primera['id'];
            });
            usort($resto, function($a, $b) use ($distanciaZonaPoder) {
                return $distanciaZonaPoder($a) <=> $distanciaZonaPoder($b);
            });
            $seleccionados = [$primera, array_values($resto)[0]];
        } else {
            usort($varCoords, function($a, $b) use ($distanciaZonaPoder) {
                return $distanciaZonaPoder($a) <=> $distanciaZonaPoder($b);
            });
            $seleccionados = array_slice($varCoords, 0, 2);
        }

        // Obtener solo las hipótesis de las variables seleccionadas
        $selectedVariableIds = array_column($seleccionados, 'id');
        $hypotheses = \App\Models\Hypothesis::where('user_id', $userData->id)
            ->whereIn('id_variable', $selectedVariableIds)
            ->get();
        
        // Agrupar hipótesis por variable
        $groupedHypotheses = $hypotheses->groupBy('id_variable');
        
        $userData->future_drivers = $groupedHypotheses->map(function($variableHypotheses, $variableId) {
            $variable = \App\Models\Variable::find($variableId);
            $variableName = $variable ? $variable->name_variable : 'Variable Desconocida';
            
            // Obtener H0 y H1 para esta variable
            $h0 = $variableHypotheses->where('secondary_hypotheses', 'H0')->first();
            $h1 = $variableHypotheses->where('secondary_hypotheses', 'H1')->first();
            
            return [
                'id' => $variableId,
                'variable_id' => $variableId,
                'variable_name' => $variableName,
                'name_hypothesis' => $variableName,
                'h0_description' => $h0 ? $h0->description : null,
                'h1_description' => $h1 ? $h1->description : null,
                'state' => $h0 ? $h0->state : ($h1 ? $h1->state : '0')
            ];
        })->values();

        $userData->initial_conditions = $variables->map(function($variable) {
            return [
                'id' => $variable->id,
                'variable_id' => $variable->id_variable,
                'variable_name' => $variable->name_variable,
                'now_condition' => $variable->now_condition,
                'state' => $variable->state
            ];
        });

        $scenarios = \App\Models\Scenarios::where('user_id', $userData->id)
            ->orderBy('num_scenario', 'asc')
            ->get();
        
        $userData->scenarios = $scenarios->map(function($scenario) use ($hypotheses) {
            // Obtener las hipótesis específicas para cada escenario según el método de Schwartz
            $scenarioHypotheses = [];
            
            // Obtener las dos variables principales (las más cercanas a la zona de poder)
            $groupedHypotheses = $hypotheses->groupBy('id_variable');
            $mainVariables = $groupedHypotheses->take(2)->values();
            
            if ($mainVariables->count() >= 2) {
                $var1 = $mainVariables[0];
                $var2 = $mainVariables[1];
                
                // Obtener H0 y H1 para cada variable
                $h0_var1 = $var1->where('secondary_hypotheses', 'H0')->first();
                $h1_var1 = $var1->where('secondary_hypotheses', 'H1')->first();
                $h0_var2 = $var2->where('secondary_hypotheses', 'H0')->first();
                $h1_var2 = $var2->where('secondary_hypotheses', 'H1')->first();
                
                // Asignar hipótesis según el número de escenario
                switch ($scenario->num_scenario) {
                    case 1: // Escenario 1: H1+ y H2+
                        if ($h1_var1) {
                            $variable = \App\Models\Variable::find($h1_var1->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h1_var1->id,
                                'name_hypothesis' => 'Hipótesis 1+',
                                'description' => $h1_var1->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h1_var1->state
                            ];
                        }
                        if ($h1_var2) {
                            $variable = \App\Models\Variable::find($h1_var2->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h1_var2->id,
                                'name_hypothesis' => 'Hipótesis 2+',
                                'description' => $h1_var2->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h1_var2->state
                            ];
                        }
                        break;
                    case 2: // Escenario 2: H2+ y H1-
                        if ($h1_var2) {
                            $variable = \App\Models\Variable::find($h1_var2->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h1_var2->id,
                                'name_hypothesis' => 'Hipótesis 2+',
                                'description' => $h1_var2->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h1_var2->state
                            ];
                        }
                        if ($h0_var1) {
                            $variable = \App\Models\Variable::find($h0_var1->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h0_var1->id,
                                'name_hypothesis' => 'Hipótesis 1-',
                                'description' => $h0_var1->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h0_var1->state
                            ];
                        }
                        break;
                    case 3: // Escenario 3: H1- y H2-
                        if ($h0_var1) {
                            $variable = \App\Models\Variable::find($h0_var1->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h0_var1->id,
                                'name_hypothesis' => 'Hipótesis 1-',
                                'description' => $h0_var1->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h0_var1->state
                            ];
                        }
                        if ($h0_var2) {
                            $variable = \App\Models\Variable::find($h0_var2->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h0_var2->id,
                                'name_hypothesis' => 'Hipótesis 2-',
                                'description' => $h0_var2->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h0_var2->state
                            ];
                        }
                        break;
                    case 4: // Escenario 4: H2- y H1+
                        if ($h0_var2) {
                            $variable = \App\Models\Variable::find($h0_var2->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h0_var2->id,
                                'name_hypothesis' => 'Hipótesis 2-',
                                'description' => $h0_var2->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h0_var2->state
                            ];
                        }
                        if ($h1_var1) {
                            $variable = \App\Models\Variable::find($h1_var1->id_variable);
                            $scenarioHypotheses[] = [
                                'id' => $h1_var1->id,
                                'name_hypothesis' => 'Hipótesis 1+',
                                'description' => $h1_var1->description,
                                'variable_name' => $variable ? $variable->name_variable : 'Variable Desconocida',
                                'state' => $h1_var1->state
                            ];
                        }
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
                'hypotheses' => $scenarioHypotheses
            ];
        });

        $conclusions = \App\Models\Conclusion::where('user_id', $userData->id)
            ->orderBy('id', 'asc')
            ->get();
        
        $conclusionsArray = [];
        
        // Si hay conclusiones reales, usar solo la primera
        if ($conclusions->count() > 0) {
            $conclusion = $conclusions->first();
            $conclusionsArray[] = [
                'id' => $conclusion->id,
                'title' => 'Conclusiones',
                'state' => $conclusion->state,
                'component_practice' => $conclusion->component_practice,
                'actuality' => $conclusion->actuality,
                'aplication' => $conclusion->aplication
            ];
        } else {
            // Si no hay conclusiones, crear una vacía
            $conclusionsArray[] = [
                'id' => 1,
                'title' => 'Conclusiones',
                'state' => '0',
                'component_practice' => null,
                'actuality' => null,
                'aplication' => null
            ];
        }
        
        $userData->conclusions = $conclusionsArray;
    }

    private function determineZone($dependencia, $influencia, $variables)
    {
        if ($variables->isEmpty()) {
            return 1; 
        }

        $maxDependencia = $variables->max('dependencia') ?: 10;
        $maxInfluencia = $variables->max('influencia') ?: 12;

        $normalizedDependencia = $dependencia / $maxDependencia;
        $normalizedInfluencia = $influencia / $maxInfluencia;

        if ($normalizedInfluencia > 0.5 && $normalizedDependencia < 0.5) {
            return 1; 
        } elseif ($normalizedInfluencia > 0.5 && $normalizedDependencia > 0.5) {
            return 2; 
        } elseif ($normalizedInfluencia < 0.5 && $normalizedDependencia < 0.5) {
            return 3; 
        } else {
            return 4; 
        }
    }
}