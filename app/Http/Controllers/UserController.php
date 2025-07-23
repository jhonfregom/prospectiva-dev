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
            $this->addUserData($userData);
        }

        return response()->json([
            'status' => 200,
            'data' => $users
        ]);
    }

    /**
     * Obtiene los resultados por ruta específica
     */
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

        // Obtener todas las rutas del usuario
        $userRoutes = \App\Models\Traceability::where('user_id', $user->id)->get();
        
        if ($user->role == 1) {
            // Admin: ve todos los usuarios con todas sus rutas
            $users = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')->get();
        } else {
            // Usuario: solo ve el suyo con todas sus rutas
            $users = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')
                ->where('id', $user->id)
                ->get();
        }

        $result = [];
        
        // Para cada usuario, crear una fila por cada ruta
        foreach ($users as $userData) {
            foreach ($userRoutes as $route) {
                $userRow = clone $userData;
                
                // Agregar datos específicos de esta ruta
                $this->addUserDataByRoute($userRow, $route->id);
                
                $result[] = $userRow;
            }
        }

        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    /**
     * Agrega datos de usuario filtrados por ruta
     */
    private function addUserDataByRoute($userData, $routeId)
    {
        // Obtener la información de la ruta para mostrar el número tried
        $route = \App\Models\Traceability::find($routeId);
        $routeNumber = $route ? $route->tried : $routeId;
        
        // Obtener variables de la ruta específica
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

        // Agregar información de matriz de la ruta específica
        $matriz = \App\Models\Matriz::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->get();
        
        // Calcular dependencia e influencia para cada variable
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

        // Matriz cruzada de la ruta específica
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

        // Análisis de variables por zona de la ruta específica
        $analyses = \App\Models\VariableMapAnalisys::where('user_id', $userData->id)
            ->where('tried_id', $routeId)
            ->get();
        
        $userData->zone_analyses = $analyses->map(function($analysis) use ($userData, $matriz, $variables) {
            $zone = \App\Models\Zones::find($analysis->zone_id);
            $zoneName = $zone ? $zone->name_zones : 'Zona Desconocida';
            
            // Calcular coordenadas de cada variable
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
            
            // Calcular máximos y centro
            $maxX = max(array_column($variablesWithCoords, 'dependencia')) ?: 10;
            $maxY = max(array_column($variablesWithCoords, 'influencia')) ?: 12;
            $centroX = $maxX / 2;
            $centroY = $maxY / 2;
            
            // Determinar variables en esta zona
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

        // Hipótesis de la ruta específica
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

        // Condiciones iniciales de la ruta específica
        $userData->initial_conditions = $variables->map(function($variable) {
            return [
                'id' => $variable->id,
                'variable_id' => $variable->id_variable,
                'variable_name' => $variable->name_variable,
                'now_condition' => $variable->now_condition,
                'state' => $variable->state
            ];
        });

        // Escenarios de la ruta específica
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
                'hypotheses' => [] // Simplificado para este ejemplo
            ];
        });

        // Conclusiones de la ruta específica
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
        
        // Agregar información de la ruta para mostrar en la columna
        $userData->route_id = $routeId; // Mantener el ID para operaciones internas
        $userData->route_name = "Ruta " . $routeNumber; // Usar el número tried para mostrar
    }

    /**
     * Agrega datos de usuario (método original sin filtro por ruta)
     */
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

        // Agregar información de matriz
        $matriz = \App\Models\Matriz::where('user_id', $userData->id)->get();
        
        // Calcular dependencia e influencia para cada variable
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

        // Matriz cruzada
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

        // Análisis de variables por zona
        $analyses = \App\Models\VariableMapAnalisys::where('user_id', $userData->id)->get();
        $userData->zone_analyses = $analyses->map(function($analysis) use ($userData, $matriz, $variables) {
            $zone = \App\Models\Zones::find($analysis->zone_id);
            $zoneName = $zone ? $zone->name_zones : 'Zona Desconocida';
            
            // Calcular coordenadas de cada variable
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
            
            // Calcular máximos y centro
            $maxX = max(array_column($variablesWithCoords, 'dependencia')) ?: 10;
            $maxY = max(array_column($variablesWithCoords, 'influencia')) ?: 12;
            $centroX = $maxX / 2;
            $centroY = $maxY / 2;
            
            // Determinar variables en esta zona
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

        // Hipótesis
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

        // Condiciones iniciales
        $userData->initial_conditions = $variables->map(function($variable) {
            return [
                'id' => $variable->id,
                'variable_id' => $variable->id_variable,
                'variable_name' => $variable->name_variable,
                'now_condition' => $variable->now_condition,
                'state' => $variable->state
            ];
        });

        // Escenarios
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

        // Conclusiones
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
