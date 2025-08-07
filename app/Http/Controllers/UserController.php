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

        $hypotheses = \App\Models\Hypothesis::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->get();
        
        $userData->future_drivers = $hypotheses->map(function($hypothesis) {
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
                'hypotheses' => [] 
            ];
        });

        $conclusions = \App\Models\Conclusion::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->orderBy('id', 'asc')
            ->get();
        
        $conclusionsArray = [];
        for ($i = 1; $i <= 3; $i++) {
            $actualId = 4 - $i;
            $existingConclusion = $conclusions->where('id', $actualId)->first();
            if ($existingConclusion) {
                $conclusionsArray[] = [
                    'id' => $existingConclusion->id,
                    'title' => 'Conclusión ' . $i,
                    'state' => $existingConclusion->state,
                    'component_practice' => $existingConclusion->component_practice,
                    'actuality' => $existingConclusion->actuality,
                    'aplication' => $existingConclusion->aplication
                ];
            } else {
                $conclusionsArray[] = [
                    'id' => $actualId,
                    'title' => 'Conclusión ' . $i,
                    'state' => '0',
                    'component_practice' => null,
                    'actuality' => null,
                    'aplication' => null
                ];
            }
        }
        
        $userData->conclusions = $conclusionsArray;

        $userData->route_id = $routeId; 
        $userData->route_name = "Ruta " . $routeNumber; 
    }

    private function addUserData($userData)
    {
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

        $hypotheses = \App\Models\Hypothesis::where('user_id', $userData->id)->get();
        $userData->future_drivers = $hypotheses->map(function($hypothesis) {
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
        
        $userData->scenarios = $scenarios->map(function($scenario) {
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
                'hypotheses' => []
            ];
        });

        $conclusions = \App\Models\Conclusion::where('user_id', $userData->id)
            ->orderBy('id', 'asc')
            ->get();
        
        $conclusionsArray = [];
        for ($i = 1; $i <= 3; $i++) {
            $actualId = 4 - $i;
            $existingConclusion = $conclusions->where('id', $actualId)->first();
            if ($existingConclusion) {
                $conclusionsArray[] = [
                    'id' => $existingConclusion->id,
                    'title' => 'Conclusión ' . $i,
                    'state' => $existingConclusion->state,
                    'component_practice' => $existingConclusion->component_practice,
                    'actuality' => $existingConclusion->actuality,
                    'aplication' => $existingConclusion->aplication
                ];
            } else {
                $conclusionsArray[] = [
                    'id' => $actualId,
                    'title' => 'Conclusión ' . $i,
                    'state' => '0',
                    'component_practice' => null,
                    'actuality' => null,
                    'aplication' => null
                ];
            }
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