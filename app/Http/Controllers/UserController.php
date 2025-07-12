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

            // Agregar información de análisis de variables por zona
            $analyses = \App\Models\VariableMapAnalisys::where('user_id', $userData->id)->get();
            $userData->zone_analyses = $analyses->map(function($analysis) use ($userData) {
                // Obtener el nombre de la zona
                $zone = \App\Models\Zones::find($analysis->zone_id);
                $zoneName = $zone ? $zone->name_zones : 'Zona Desconocida';
                
                // Obtener las variables que están en esta zona para este usuario
                $variablesInZone = \App\Models\Variable::where('user_id', $userData->id)->get();
                
                // Calcular en qué zona está cada variable (lógica del mapa de variables)
                $variablesInThisZone = [];
                if ($variablesInZone->count() > 0) {
                    $maxX = $variablesInZone->max('dependencia') ?: 10;
                    $maxY = $variablesInZone->max('influencia') ?: 12;
                    $centroX = $maxX / 2;
                    $centroY = $maxY / 2;
                    
                    foreach ($variablesInZone as $variable) {
                        $zona = '';
                        $esFrontera = false;
                        
                        // Lógica para determinar la zona de cada variable
                        if ($variable->dependencia === $centroX || $variable->influencia === $centroY) {
                            $esFrontera = true;
                            if ($variable->dependencia > $centroX && $variable->influencia >= $centroY) $zona = 'conflicto';
                            else if ($variable->dependencia <= $centroX && $variable->influencia > $centroY) $zona = 'poder';
                            else if ($variable->dependencia > $centroX && $variable->influencia < $centroY) $zona = 'salida';
                            else $zona = 'indiferencia';
                        } else {
                            if ($variable->dependencia <= $centroX && $variable->influencia > $centroY) $zona = 'poder';
                            else if ($variable->dependencia > $centroX && $variable->influencia >= $centroY) $zona = 'conflicto';
                            else if ($variable->dependencia <= $centroX && $variable->influencia <= $centroY) $zona = 'indiferencia';
                            else if ($variable->dependencia > $centroX && $variable->influencia < $centroY) $zona = 'salida';
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
                                'id_variable' => $variable->id_variable,
                                'name_variable' => $variable->name_variable,
                                'dependencia' => $variable->dependencia,
                                'influencia' => $variable->influencia,
                                'frontera' => $esFrontera
                            ];
                        }
                    }
                }
                
                return [
                    'zone_id' => $analysis->zone_id,
                    'zone_name' => $zoneName,
                    'description' => $analysis->description,
                    'score' => $analysis->score,
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
                    // Obtener las dos hipótesis (data[0] y data[1])
                    $hypothesesArray = $allHypotheses->toArray();
                    $h0_0 = $allHypotheses->where('secondary_hypotheses', 'H0')->first();
                    $h0_1 = $allHypotheses->where('secondary_hypotheses', 'H0')->skip(1)->first();
                    $h1_0 = $allHypotheses->where('secondary_hypotheses', 'H1')->first();
                    $h1_1 = $allHypotheses->where('secondary_hypotheses', 'H1')->skip(1)->first();
                    
                    // Obtener información de las variables
                    $variable0 = $h0_0 ? \App\Models\Variable::find($h0_0->id_variable) : null;
                    $variable1 = $h1_0 ? \App\Models\Variable::find($h1_0->id_variable) : null;
                    
                    switch ($scenario->num_scenario) {
                        case 1: // H1, H1 (data[0].descriptionH1, data[1].descriptionH1)
                            $associatedHypotheses = [
                                [
                                    'id' => $h1_0 ? $h1_0->id : null,
                                    'name_hypothesis' => 'HIPÓTESIS 1+',
                                    'variable_name' => $variable1 ? $variable1->name_variable : 'Variable Desconocida',
                                    'description' => $h1_0 ? $h1_0->description : ''
                                ],
                                [
                                    'id' => $h1_1 ? $h1_1->id : null,
                                    'name_hypothesis' => 'HIPÓTESIS 2+',
                                    'variable_name' => $h1_1 ? (\App\Models\Variable::find($h1_1->id_variable) ? \App\Models\Variable::find($h1_1->id_variable)->name_variable : 'Variable Desconocida') : 'Variable Desconocida',
                                    'description' => $h1_1 ? $h1_1->description : ''
                                ]
                            ];
                            break;
                        case 2: // H1, H0 (data[1].descriptionH1, data[0].descriptionH0)
                            $associatedHypotheses = [
                                [
                                    'id' => $h1_1 ? $h1_1->id : null,
                                    'name_hypothesis' => 'HIPÓTESIS 2+',
                                    'variable_name' => $h1_1 ? (\App\Models\Variable::find($h1_1->id_variable) ? \App\Models\Variable::find($h1_1->id_variable)->name_variable : 'Variable Desconocida') : 'Variable Desconocida',
                                    'description' => $h1_1 ? $h1_1->description : ''
                                ],
                                [
                                    'id' => $h0_0 ? $h0_0->id : null,
                                    'name_hypothesis' => 'HIPÓTESIS 1-',
                                    'variable_name' => $variable0 ? $variable0->name_variable : 'Variable Desconocida',
                                    'description' => $h0_0 ? $h0_0->description : ''
                                ]
                            ];
                            break;
                        case 3: // H0, H0 (data[0].descriptionH0, data[1].descriptionH0)
                            $associatedHypotheses = [
                                [
                                    'id' => $h0_0 ? $h0_0->id : null,
                                    'name_hypothesis' => 'HIPÓTESIS 1-',
                                    'variable_name' => $variable0 ? $variable0->name_variable : 'Variable Desconocida',
                                    'description' => $h0_0 ? $h0_0->description : ''
                                ],
                                [
                                    'id' => $h0_1 ? $h0_1->id : null,
                                    'name_hypothesis' => 'HIPÓTESIS 2-',
                                    'variable_name' => $h0_1 ? (\App\Models\Variable::find($h0_1->id_variable) ? \App\Models\Variable::find($h0_1->id_variable)->name_variable : 'Variable Desconocida') : 'Variable Desconocida',
                                    'description' => $h0_1 ? $h0_1->description : ''
                                ]
                            ];
                            break;
                        case 4: // H0, H1 (data[1].descriptionH0, data[0].descriptionH1)
                            $associatedHypotheses = [
                                [
                                    'id' => $h0_1 ? $h0_1->id : null,
                                    'name_hypothesis' => 'HIPÓTESIS 2-',
                                    'variable_name' => $h0_1 ? (\App\Models\Variable::find($h0_1->id_variable) ? \App\Models\Variable::find($h0_1->id_variable)->name_variable : 'Variable Desconocida') : 'Variable Desconocida',
                                    'description' => $h0_1 ? $h0_1->description : ''
                                ],
                                [
                                    'id' => $h1_0 ? $h1_0->id : null,
                                    'name_hypothesis' => 'HIPÓTESIS 1+',
                                    'variable_name' => $variable1 ? $variable1->name_variable : 'Variable Desconocida',
                                    'description' => $h1_0 ? $h1_0->description : ''
                                ]
                            ];
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
}
