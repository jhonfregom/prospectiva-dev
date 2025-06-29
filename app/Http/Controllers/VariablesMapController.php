<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VariableMapAnalisys;
use App\Models\Zones;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VariablesMapController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $analyses = VariableMapAnalisys::where('user_id', Auth::id())->get();
            
            // Convertir los IDs de zona de vuelta a nombres para el frontend
            $analyses->each(function ($analysis) {
                $zone = Zones::find($analysis->zone_id);
                if ($zone) {
                    // Mapear el nombre de la zona a la clave del frontend
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
            Log::error('Error al obtener análisis: ' . $e->getMessage());
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
            ]);

            $data['user_id'] = Auth::id();
            $isManualSave = $data['is_manual_save'] ?? false;

            // Buscar el ID de la zona por su nombre
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

            // Buscar si ya existe un análisis para este usuario y zona
            $analysis = VariableMapAnalisys::where('user_id', $data['user_id'])
                ->where('zone_id', $data['zone_id'])
                ->first();

            if ($analysis) {
                // Si ya existe, verificar si está bloqueado
                if ($analysis->state === '1') {
                    return response()->json([
                        'data' => null,
                        'status' => 400,
                        'message' => 'Este análisis ya está bloqueado y no se puede editar.'
                    ], 400);
                }
                
                // Solo contar como edición si es guardado manual
                if ($isManualSave) {
                    // Si el contenido ha cambiado, es una edición
                    $contentChanged = $analysis->description !== $data['description'] || $analysis->score !== $data['score'];
                    
                    if ($contentChanged) {
                        // Si ya se ha editado antes (updated_at es diferente de created_at), bloquear
                        if ($analysis->updated_at->gt($analysis->created_at)) {
                            $data['state'] = '1'; // Bloquear después de la segunda edición
                        }
                    }
                }
                
                $analysis->update($data);
            } else {
                // Si es nuevo, encontrar el primer ID disponible
                $nextId = $this->findNextAvailableId();
                $data['id'] = $nextId;
                $data['state'] = '0';
                
                // Crear el registro con el ID específico
                $analysis = VariableMapAnalisys::create($data);
            }

            return response()->json([
                'data' => $analysis,
                'status' => 201,
                'message' => 'Análisis guardado correctamente.'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al guardar análisis: ' . $e->getMessage());
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

            return response()->json([
                'data' => $analysis,
                'status' => 200,
                'message' => 'Análisis actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar análisis: ' . $e->getMessage());
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
            Log::error('Error al eliminar análisis: ' . $e->getMessage());
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al eliminar el análisis: ' . $e->getMessage()
            ], 500);
        }
    }

    // Función para encontrar el primer ID disponible
    private function findNextAvailableId(): int
    {
        // Obtener todos los IDs existentes ordenados
        $existingIds = VariableMapAnalisys::orderBy('id')->pluck('id')->toArray();
        
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
            $count = VariableMapAnalisys::count();
            if ($count === 0) {
                // Reiniciar el AUTO_INCREMENT
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
            \DB::table('variables_map_analiyis')->truncate();
            
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
