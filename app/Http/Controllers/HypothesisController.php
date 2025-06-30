<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Hypothesis;
use App\Models\Variable;
use App\Models\Zones;
use App\Models\Matriz;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HypothesisController extends Controller
{
    /**
     * Devuelve las 2 variables seleccionadas y sus hipótesis para el usuario autenticado.
     */
    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            Log::info('HypothesisController::index - User ID: ' . $userId);
            
            $matriz = Matriz::where('user_id', $userId)->get();
            $variables = Variable::where('user_id', $userId)->get();
            
            Log::info('HypothesisController::index - Variables count: ' . $variables->count());
            Log::info('HypothesisController::index - Matriz count: ' . $matriz->count());

            // Si no hay variables, retornar array vacío
            if ($variables->isEmpty()) {
                Log::info('HypothesisController::index - No variables found for user');
                return response()->json([
                    'data' => [],
                    'status' => 200,
                    'message' => 'No hay variables disponibles.'
                ]);
            }

            // Calcular dependencia e influencia para cada variable (misma lógica que la gráfica)
            $varCoords = [];
            foreach ($variables as $variable) {
                // Dependencia = suma de la fila (donde esta variable es el origen)
                $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
                // Influencia = suma de la columna (donde esta variable es el destino)
                $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
                
                $varCoords[] = [
                    'id' => $variable->id,
                    'name' => $variable->name_variable,
                    'dependencia' => $dependencia,
                    'influencia' => $influencia
                ];
            }

            Log::info('HypothesisController::index - Variables with coordinates: ' . json_encode($varCoords));

            // Calcular máximos y mínimos para la zona de poder
            $maxInfluencia = max(array_column($varCoords, 'influencia'));
            $minDependencia = min(array_column($varCoords, 'dependencia'));

            // 1. Variables por encima de la diagonal (influencia > dependencia)
            $porEncimaDiagonal = array_filter($varCoords, function($var) {
                return $var['influencia'] > $var['dependencia'];
            });

            // Función para calcular distancia a la zona de poder
            $distanciaZonaPoder = function($var) use ($minDependencia, $maxInfluencia) {
                return sqrt(pow($var['dependencia'] - $minDependencia, 2) + pow($var['influencia'] - $maxInfluencia, 2));
            };

            if (count($porEncimaDiagonal) >= 2) {
                // Tomar las 2 más cercanas a la zona de poder
                usort($porEncimaDiagonal, function($a, $b) use ($distanciaZonaPoder) {
                    return $distanciaZonaPoder($a) <=> $distanciaZonaPoder($b);
                });
                $seleccionados = array_slice($porEncimaDiagonal, 0, 2);
                Log::info('HypothesisController::index - 2+ arriba diagonal (más cerca zona poder): ' . json_encode($seleccionados));
            } elseif (count($porEncimaDiagonal) === 1) {
                // Tomar esa y la más cercana a la zona de poder de las demás
                $primera = array_values($porEncimaDiagonal)[0];
                $resto = array_filter($varCoords, function($var) use ($primera) {
                    return $var['id'] !== $primera['id'];
                });
                usort($resto, function($a, $b) use ($distanciaZonaPoder) {
                    return $distanciaZonaPoder($a) <=> $distanciaZonaPoder($b);
                });
                $seleccionados = [$primera, $resto[0]];
                Log::info('HypothesisController::index - 1 arriba diagonal + 1 más cerca zona poder: ' . json_encode($seleccionados));
            } else {
                // Ninguna arriba de la diagonal: tomar las 2 más cercanas a zona de poder
                $varCoordsCopia = $varCoords;
                usort($varCoordsCopia, function($a, $b) use ($distanciaZonaPoder) {
                    return $distanciaZonaPoder($a) <=> $distanciaZonaPoder($b);
                });
                $seleccionados = array_slice($varCoordsCopia, 0, 2);
                Log::info('HypothesisController::index - 0 arriba diagonal, 2 más cerca zona poder: ' . json_encode($seleccionados));
            }

            // Calcular la zona de cada variable seleccionada
            $dependencias = array_column($varCoords, 'dependencia');
            $influencias = array_column($varCoords, 'influencia');
            $maxX = max($dependencias);
            $maxY = max($influencias);
            $maxX = max($maxX, 10); // Mínimo 10
            $maxY = max($maxY, 12); // Mínimo 12
            $centroX = $maxX / 2;
            $centroY = $maxY / 2;

            foreach ($seleccionados as &$variable) {
                $zona = '';
                
                // Detectar frontera
                if ($variable['dependencia'] == $centroX || $variable['influencia'] == $centroY) {
                    // Prioridad: Conflicto > Poder > Salida > Indiferencia
                    if ($variable['dependencia'] > $centroX && $variable['influencia'] >= $centroY) {
                        $zona = 'conflicto';
                    } elseif ($variable['dependencia'] <= $centroX && $variable['influencia'] > $centroY) {
                        $zona = 'poder';
                    } elseif ($variable['dependencia'] > $centroX && $variable['influencia'] < $centroY) {
                        $zona = 'salida';
                    } else {
                        $zona = 'indiferencia';
                    }
                } else {
                    if ($variable['dependencia'] <= $centroX && $variable['influencia'] > $centroY) {
                        $zona = 'poder';
                    } elseif ($variable['dependencia'] > $centroX && $variable['influencia'] >= $centroY) {
                        $zona = 'conflicto';
                    } elseif ($variable['dependencia'] <= $centroX && $variable['influencia'] <= $centroY) {
                        $zona = 'indiferencia';
                    } elseif ($variable['dependencia'] > $centroX && $variable['influencia'] < $centroY) {
                        $zona = 'salida';
                    }
                }

                // Mapear la zona a su ID en la base de datos
                $zoneMapping = [
                    'poder' => 'ZONA DE PODER',
                    'conflicto' => 'ZONA DE CONFLICTO',
                    'salida' => 'ZONA DE SALIDA',
                    'indiferencia' => 'ZONA DE INDIFERENCIA'
                ];

                $zoneName = $zoneMapping[$zona] ?? 'ZONA DE PODER';
                $zone = Zones::where('name_zones', $zoneName)->first();
                $variable['zone_id'] = $zone ? $zone->id : 1; // Por defecto zona 1 si no se encuentra
            }

            // Obtener hipótesis existentes para el usuario y las variables seleccionadas
            $selectedIds = array_map(fn($v) => $v['id'], $seleccionados);
            $selectedVars = $variables->whereIn('id', $selectedIds);
            $hypotheses = Hypothesis::where('user_id', $userId)
                ->whereIn('id_variable', $selectedVars->pluck('id'))
                ->get();

            Log::info('HypothesisController::index - Existing hypotheses count: ' . $hypotheses->count());

            // Preparar respuesta
            $result = collect($seleccionados)->map(function ($v) use ($hypotheses) {
                $hypo = $hypotheses->firstWhere('id_variable', $v['id']);
                return [
                    'id' => $hypo ? $hypo->id : null,
                    'variable_id' => $v['id'],
                    'variable_name' => $v['name'],
                    'zone_id' => $hypo ? $hypo->zone_id : $v['zone_id'],
                    'descriptionH0' => $hypo ? $hypo->descriptionH0 : '',
                    'descriptionH1' => $hypo ? $hypo->descriptionH1 : '',
                    'state' => $hypo ? $hypo->state : '0',
                ];
            });

            Log::info('HypothesisController::index - Final result: ' . json_encode($result));

            // Log de distancias a la zona de poder
            foreach ($varCoords as $v) {
                $dist = sqrt(pow($v['dependencia'] - $minDependencia, 2) + pow($v['influencia'] - $maxInfluencia, 2));
                Log::info('Var ' . $v['id'] . ' (' . $v['name'] . ') - Dependencia: ' . $v['dependencia'] . ', Influencia: ' . $v['influencia'] . ', Distancia zona poder: ' . $dist);
            }

            return response()->json([
                'data' => $result,
                'status' => 200,
                'message' => 'Hipótesis obtenidas correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener hipótesis: ' . $e->getMessage());
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al obtener hipótesis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guarda o actualiza una hipótesis para el usuario autenticado.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $data = $request->validate([
                'variable_id' => 'required|integer',
                'descriptionH0' => 'nullable|string',
                'descriptionH1' => 'nullable|string',
                'is_manual_save' => 'boolean',
            ]);

            $data['user_id'] = $userId;
            $isManualSave = $data['is_manual_save'] ?? false;

            // Calcular la zona de la variable automáticamente
            $matriz = Matriz::where('user_id', $userId)->get();
            $variables = Variable::where('user_id', $userId)->get();

            // Calcular dependencia e influencia de la variable
            $variable = $variables->find($data['variable_id']);
            if (!$variable) {
                return response()->json([
                    'data' => null,
                    'status' => 400,
                    'message' => 'Variable no encontrada.'
                ], 400);
            }

            $dependencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
            $influencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');

            // Calcular zona usando la misma lógica
            $allVars = [];
            foreach ($variables as $var) {
                $dep = $matriz->where('id_variable', $var->id)->sum('id_resp_influ');
                $inf = $matriz->where('id_resp_depen', $var->id)->sum('id_resp_influ');
                $allVars[] = ['dependencia' => $dep, 'influencia' => $inf];
            }

            $dependencias = array_column($allVars, 'dependencia');
            $influencias = array_column($allVars, 'influencia');
            $maxX = max($dependencias);
            $maxY = max($influencias);
            $maxX = max($maxX, 10); // Mínimo 10
            $maxY = max($maxY, 12); // Mínimo 12
            $centroX = $maxX / 2;
            $centroY = $maxY / 2;

            $zona = '';
            if ($dependencia == $centroX || $influencia == $centroY) {
                if ($dependencia > $centroX && $influencia >= $centroY) $zona = 'conflicto';
                elseif ($dependencia <= $centroX && $influencia > $centroY) $zona = 'poder';
                elseif ($dependencia > $centroX && $influencia < $centroY) $zona = 'salida';
                else $zona = 'indiferencia';
            } else {
                if ($dependencia <= $centroX && $influencia > $centroY) $zona = 'poder';
                elseif ($dependencia > $centroX && $influencia >= $centroY) $zona = 'conflicto';
                elseif ($dependencia <= $centroX && $influencia <= $centroY) $zona = 'indiferencia';
                elseif ($dependencia > $centroX && $influencia < $centroY) $zona = 'salida';
            }

            $zoneMapping = [
                'poder' => 'ZONA DE PODER',
                'conflicto' => 'ZONA DE CONFLICTO',
                'salida' => 'ZONA DE SALIDA',
                'indiferencia' => 'ZONA DE INDIFERENCIA'
            ];

            $zoneName = $zoneMapping[$zona] ?? 'ZONA DE PODER';
            $zone = Zones::where('name_zones', $zoneName)->first();
            $data['zone_id'] = $zone ? $zone->id : 1;

            // Buscar hipótesis existente
            $hypothesis = Hypothesis::where('user_id', $userId)
                ->where('id_variable', $data['variable_id'])
                ->first();

            if ($hypothesis) {
                // Si ya está bloqueada, no permitir editar
                if ($hypothesis->state === '1') {
                    return response()->json([
                        'data' => $hypothesis,
                        'status' => 200,
                        'message' => 'Esta hipótesis ya está bloqueada y no se puede editar.'
                    ]);
                }

                // Contador de ediciones en sesión (por usuario e hipótesis)
                $sessionKey = 'hypothesis_edit_count_' . $hypothesis->id . '_user_' . $userId;
                $editCount = session($sessionKey, 0) + 1;
                session([$sessionKey => $editCount]);

                // Forzar actualización de updated_at SIEMPRE
                $updateData = $data;
                $updateData['id_variable'] = $data['variable_id'];
                unset($updateData['variable_id']);
                $updateData['updated_at'] = now();

                // Si es la segunda edición, bloquear
                if ($editCount >= 2) {
                    $updateData['state'] = '1';
                }

                $hypothesis->update($updateData);

                return response()->json([
                    'data' => $hypothesis->fresh(),
                    'status' => 201,
                    'message' => 'Hipótesis guardada correctamente.'
                ], 201);
            } else {
                // Si es nuevo, encontrar el primer ID disponible
                $nextId = $this->findNextAvailableId();
                $data['id'] = $nextId;
                $data['state'] = '0';
                
                // Mapear variable_id a id_variable para la tabla
                $data['id_variable'] = $data['variable_id'];
                unset($data['variable_id']); // Remover el campo que no existe en la tabla
                
                // Crear el registro con el ID específico
                $hypothesis = Hypothesis::create($data);

                return response()->json([
                    'data' => $hypothesis,
                    'status' => 201,
                    'message' => 'Hipótesis guardada correctamente.'
                ], 201);
            }
        } catch (\Exception $e) {
            Log::error('Error al guardar hipótesis: ' . $e->getMessage());
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al guardar hipótesis: ' . $e->getMessage()
            ], 500);
        }
    }

    // Función para encontrar el primer ID disponible
    private function findNextAvailableId(): int
    {
        // Obtener todos los IDs existentes ordenados
        $existingIds = Hypothesis::orderBy('id')->pluck('id')->toArray();
        
        if (empty($existingIds)) {
            return 1; // Si no hay registros, empezar con 1
        }
        
        // Buscar el primer hueco en la secuencia
        $expectedId = 1;
        foreach ($existingIds as $existingId) {
            if ($existingId > $expectedId) {
                // Encontramos un hueco, usar este ID
                return $expectedId;
            }
            $expectedId = $existingId + 1;
        }
        
        // Si no hay huecos, usar el siguiente ID después del último
        return $expectedId;
    }

    // Función para reiniciar el AUTO_INCREMENT
    public function resetAutoIncrement(): JsonResponse
    {
        try {
            // Verificar si no hay registros
            $count = Hypothesis::count();
            if ($count === 0) {
                // Reiniciar el AUTO_INCREMENT
                \DB::statement("ALTER TABLE hypothesis AUTO_INCREMENT = 1");
                return response()->json([
                    'data' => null,
                    'status' => 200,
                    'message' => 'AUTO_INCREMENT reiniciado correctamente.'
                ]);
            }
            
            return response()->json([
                'data' => null,
                'status' => 400,
                'message' => 'No se puede reiniciar AUTO_INCREMENT mientras hay registros.'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error al reiniciar AUTO_INCREMENT: ' . $e->getMessage());
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al reiniciar AUTO_INCREMENT: ' . $e->getMessage()
            ], 500);
        }
    }

    // Función para borrar todos los registros y reiniciar AUTO_INCREMENT
    public function deleteAllAndReset(): JsonResponse
    {
        try {
            // Usar TRUNCATE para borrar todos los registros y reiniciar AUTO_INCREMENT
            \DB::table('hypothesis')->truncate();
            
            return response()->json([
                'data' => null,
                'status' => 200,
                'message' => 'Todos los registros borrados y AUTO_INCREMENT reiniciado.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al borrar registros: ' . $e->getMessage());
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al borrar registros: ' . $e->getMessage()
            ], 500);
        }
    }
} 