<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VariableMapAnalisys;
use App\Models\Zones;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VariablesMapController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            
            $currentRoute = \App\Models\Traceability::getCurrentRouteForUser(Auth::id());
            
            if (!$currentRoute) {
                return response()->json([
                    'data' => [],
                    'status' => 200,
                    'message' => 'No se encontró ruta para el usuario'
                ]);
            }
            
            $analyses = VariableMapAnalisys::where('user_id', Auth::id())
                ->where('tried_id', $currentRoute->id)
                ->get();

            $analyses->each(function ($analysis) {
                $zone = Zones::find($analysis->zone_id);
                if ($zone) {
                    
                    $zoneMapping = [
                        'ZONA DE PODER' => 'poder',
                        'ZONA DE CONFLICTO' => 'conflicto',
                        'ZONA DE SALIDA' => 'salida',
                        'ZONA DE INDIFERENCIA' => 'indiferencia'
                    ];
                    
                    $analysis->zone_id = $zoneMapping[$zone->name_zones] ?? $zone->name_zones;
                }
            });

            return response()->json([
                'data' => $analyses,
                'status' => 200,
                'message' => 'Análisis obtenidos correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al obtener los análisis: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'description' => 'nullable|string',
                'score' => 'required|integer',
                'zone_id' => 'required|string',
                'state' => 'required|string|in:0,1',
                'is_manual_save' => 'boolean',
                'edits' => 'nullable|integer',
            ]);

            $data['user_id'] = Auth::id();
            $isManualSave = $data['is_manual_save'] ?? false;

            $currentRoute = \App\Models\Traceability::getCurrentRouteForUser($data['user_id']);
            
            if (!$currentRoute) {
                return response()->json([
                    'data' => null,
                    'status' => 400,
                    'message' => 'No se encontró ruta para el usuario'
                ]);
            }

            $zoneName = $data['zone_id'];
            $zone = Zones::where('name_zones', 'LIKE', '%' . strtoupper($zoneName) . '%')->first();
            
            if (!$zone) {
                return response()->json([
                    'data' => null,
                    'status' => 400,
                    'message' => 'Zona no encontrada: ' . $zoneName
                ], 400);
            }

            $data['zone_id'] = $zone->id;

            $analysis = VariableMapAnalisys::where('user_id', $data['user_id'])
                ->where('tried_id', $currentRoute->id)
                ->where('zone_id', $data['zone_id'])
                ->first();

            if ($analysis) {
                
                if ($analysis->state === '1') {
                    return response()->json([
                        'data' => $analysis,
                        'status' => 200,
                        'message' => 'Este análisis ya está bloqueado y no se puede editar.'
                    ]);
                }

                if ($isManualSave) {
                    
                    $analysis->edits = ($analysis->edits ?? 0) + 1;

                    if ($analysis->edits >= 3) {
                        $data['state'] = '1';
                    }
                }
                
                $analysis->update($data);
            } else {
                
                $nextId = $this->findNextAvailableId();
                $data['id'] = $nextId;
                $data['state'] = '0';
                $data['tried_id'] = $currentRoute->id;

                if ($isManualSave) {
                    $data['edits'] = 1;
                } else {
                    $data['edits'] = 0;
                }

                $analysis = VariableMapAnalisys::create($data);
            }

            return response()->json([
                'data' => $analysis,
                'status' => 201,
                'message' => 'Análisis guardado correctamente.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al guardar el análisis: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $analysis = VariableMapAnalisys::findOrFail($id);

            $validated = $request->validate([
                'description' => 'nullable|string',
                'score' => 'required|integer',
                'state' => 'required|string|in:0,1'
            ]);

            $analysis->update($validated);

            $zone = Zones::find($analysis->zone_id);
            if ($zone) {
                
                $zoneMapping = [
                    'ZONA DE PODER' => 'poder',
                    'ZONA DE CONFLICTO' => 'conflicto',
                    'ZONA DE SALIDA' => 'salida',
                    'ZONA DE INDIFERENCIA' => 'indiferencia'
                ];
                
                $analysis->zone_id = $zoneMapping[$zone->name_zones] ?? $zone->name_zones;
            }

            return response()->json([
                'data' => $analysis,
                'status' => 200,
                'message' => 'Análisis actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al actualizar el análisis: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $analysis = VariableMapAnalisys::findOrFail($id);
            $analysis->delete();
            
            return response()->json([
                'data' => null,
                'status' => 200,
                'message' => 'Análisis eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al eliminar el análisis: ' . $e->getMessage()
            ], 500);
        }
    }

    private function findNextAvailableId(): int
    {
        
        $existingIds = VariableMapAnalisys::orderBy('id')->pluck('id')->toArray();
        
        if (empty($existingIds)) {
            return 1; 
        }

        $expectedId = 1;
        foreach ($existingIds as $existingId) {
            if ($existingId > $expectedId) {
                
                return $expectedId;
            }
            $expectedId = $existingId + 1;
        }

        return $expectedId;
    }

    public function resetAutoIncrement(): JsonResponse
    {
        try {
            
            $count = VariableMapAnalisys::count();
            if ($count === 0) {
                
                \DB::statement("ALTER TABLE variables_map_analiyis AUTO_INCREMENT = 1");
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
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al reiniciar AUTO_INCREMENT: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAllAndReset(): JsonResponse
    {
        try {
            
            \DB::table('variables_map_analiyis')->truncate();
            
            return response()->json([
                'data' => null,
                'status' => 200,
                'message' => 'Todos los registros borrados y AUTO_INCREMENT reiniciado.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al borrar registros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function closeAllAnalyses(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $zones = Zones::all();
            $traceability = \App\Models\Traceability::getOrCreateForUser($userId);
            
            foreach ($zones as $zone) {
                
                $analysis = VariableMapAnalisys::where('user_id', $userId)
                    ->where('zone_id', $zone->id)
                    ->where('tried_id', $traceability->id)
                    ->first();

                if ($analysis) {
                    
                    $analysis->update([
                        'state' => '1',
                        'edits' => 3
                    ]);
                } else {
                    
                    $nextId = $this->findNextAvailableId();
                    VariableMapAnalisys::create([
                        'id' => $nextId,
                        'description' => '',
                        'score' => 0,
                        'zone_id' => $zone->id,
                        'user_id' => $userId,
                        'state' => '1',
                        'edits' => 3,
                        'tried_id' => $traceability->id
                    ]);
                }
            }

            return response()->json([
                'data' => null,
                'status' => 200,
                'message' => 'Todos los análisis han sido cerrados correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al cerrar análisis: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reopenAllAnalyses(): JsonResponse
    {
        try {
            $userId = Auth::id();

            $analyses = VariableMapAnalisys::where('user_id', $userId)->get();
            
            foreach ($analyses as $analysis) {
                
                $analysis->update([
                    'state' => '0',
                    'edits' => 0
                ]);
            }

            return response()->json([
                'data' => null,
                'status' => 200,
                'message' => 'Todos los análisis han sido reabiertos correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al reabrir análisis: ' . $e->getMessage()
            ], 500);
        }
    }
}