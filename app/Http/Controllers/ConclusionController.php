<?php

namespace App\Http\Controllers;

use App\Models\Conclusion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ConclusionController extends Controller
{
    /**
     * Encontrar el siguiente ID disponible
     */
    private function findNextAvailableId(): int
    {
        $maxId = Conclusion::max('id') ?? 0;
        $usedIds = Conclusion::pluck('id')->toArray();
        
        // Buscar el primer ID disponible desde 1
        for ($id = 1; $id <= $maxId + 1; $id++) {
            if (!in_array($id, $usedIds)) {
                return $id;
            }
        }
        
        return $maxId + 1;
    }

    /**
     * Crear registro con ID específico sin afectar autoincremento
     */
    private function createWithSpecificId(array $data, int $id): Conclusion
    {
        // Usar consulta SQL directa para insertar con ID específico
        \DB::statement("INSERT INTO conclusions (id, user_id, component_practice, actuality, aplication, state, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())", [
            $id,
            $data['user_id'],
            $data['component_practice'] ?? '',
            $data['actuality'] ?? '',
            $data['aplication'] ?? '',
            $data['state'] ?? '0'
        ]);

        // Retornar el modelo creado
        return Conclusion::find($id);
    }
    /**
     * Obtener las conclusiones del usuario autenticado
     */
    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $conclusion = Conclusion::getByUser($userId);

            if (!$conclusion) {
                // Encontrar el siguiente ID disponible
                $nextId = $this->findNextAvailableId();
                
                // Crear conclusiones vacías para el usuario con el ID específico
                $conclusion = $this->createWithSpecificId([
                    'user_id' => $userId,
                    'component_practice' => '',
                    'actuality' => '',
                    'aplication' => '',
                    'state' => '0'
                ], $nextId);
            }

            return response()->json([
                'status' => 200,
                'data' => $conclusion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al obtener las conclusiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear o actualizar las conclusiones del usuario
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $validatedData = $request->validate([
                'component_practice' => 'nullable|string',
                'actuality' => 'nullable|string',
                'aplication' => 'nullable|string',
                'state' => 'nullable|string|in:0,1'
            ]);

            // Buscar conclusiones existentes del usuario
            $conclusion = Conclusion::where('user_id', $userId)->first();

            if (!$conclusion) {
                // Si no existe, crear con el siguiente ID disponible
                $nextId = $this->findNextAvailableId();
                $validatedData['user_id'] = $userId;
                $conclusion = $this->createWithSpecificId($validatedData, $nextId);
            } else {
                // Si existe, actualizar
                $conclusion->update($validatedData);
            }

            return response()->json([
                'status' => 200,
                'data' => $conclusion,
                'message' => 'Conclusiones actualizadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar las conclusiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar las conclusiones del usuario
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $validatedData = $request->validate([
                'component_practice' => 'nullable|string',
                'actuality' => 'nullable|string',
                'aplication' => 'nullable|string',
                'state' => 'nullable|string|in:0,1'
            ]);

            $conclusion = Conclusion::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$conclusion) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Conclusiones no encontradas'
                ], 404);
            }

            $conclusion->update($validatedData);

            return response()->json([
                'status' => 200,
                'data' => $conclusion,
                'message' => 'Conclusiones actualizadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar las conclusiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bloquear las conclusiones del usuario
     */
    public function block($id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $conclusion = Conclusion::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$conclusion) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Conclusiones no encontradas'
                ], 404);
            }

            $conclusion->block();

            return response()->json([
                'status' => 200,
                'data' => $conclusion,
                'message' => 'Conclusiones bloqueadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al bloquear las conclusiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desbloquear las conclusiones del usuario
     */
    public function unblock($id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $conclusion = Conclusion::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$conclusion) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Conclusiones no encontradas'
                ], 404);
            }

            $conclusion->unblock();

            return response()->json([
                'status' => 200,
                'data' => $conclusion,
                'message' => 'Conclusiones desbloqueadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al desbloquear las conclusiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar conclusiones y reiniciar auto-increment
     */
    public function destroy($id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $conclusion = Conclusion::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$conclusion) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Conclusiones no encontradas'
                ], 404);
            }

            $conclusion->delete();

            // Reiniciar auto-increment si no hay registros
            if (Conclusion::count() === 0) {
                \DB::statement('ALTER TABLE conclusions AUTO_INCREMENT = 1');
            }

            return response()->json([
                'status' => 200,
                'message' => 'Conclusiones eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar las conclusiones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reiniciar auto-increment de la tabla
     */
    public function resetAutoIncrement(): JsonResponse
    {
        try {
            \DB::statement('ALTER TABLE conclusions AUTO_INCREMENT = 1');
            
            return response()->json([
                'status' => 200,
                'message' => 'Auto-increment reiniciado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al reiniciar auto-increment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar el estado de las conclusiones
     */
    public function updateState(Request $request, $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $validatedData = $request->validate([
                'state' => 'required|string|in:0,1'
            ]);

            $conclusion = Conclusion::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$conclusion) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Conclusiones no encontradas'
                ], 404);
            }

            $conclusion->update(['state' => $validatedData['state']]);

            return response()->json([
                'status' => 200,
                'data' => $conclusion,
                'message' => 'Estado de las conclusiones actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un campo específico de las conclusiones y su contador
     */
    public function updateField(Request $request, $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $validatedData = $request->validate([
                'field' => 'required|string|in:component_practice,actuality,aplication',
                'value' => 'nullable|string'
            ]);

            $conclusion = Conclusion::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$conclusion) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Conclusiones no encontradas'
                ], 404);
            }

            // Si ya está bloqueado el campo, no permitir editar
            $editField = $validatedData['field'] . '_edits';
            if ($conclusion->$editField >= 3) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Este campo ya está bloqueado por ediciones.'
                ], 403);
            }

            // Actualizar el campo y el contador
            $conclusion->{$validatedData['field']} = $validatedData['value'];
            $conclusion->$editField += 1;

            // Si los tres contadores llegan a 3, bloquear todo
            if ($conclusion->areAllFieldsBlocked()) {
                $conclusion->state = '1';
            }

            $conclusion->save();

            return response()->json([
                'status' => 200,
                'data' => $conclusion,
                'message' => 'Campo actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar el campo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerrar todas las conclusiones del usuario (actualizar todos los campos de edits a 3 y state a 1)
     */
    public function closeAll(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            // Buscar todas las conclusiones del usuario
            $conclusions = Conclusion::where('user_id', $userId)->get();

            if ($conclusions->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No se encontraron conclusiones para cerrar'
                ], 404);
            }

            // Actualizar todas las conclusiones
            foreach ($conclusions as $conclusion) {
                $conclusion->update([
                    'component_practice_edits' => 3,
                    'actuality_edits' => 3,
                    'aplication_edits' => 3,
                    'state' => '1'
                ]);
            }

            return response()->json([
                'status' => 200,
                'message' => 'Todas las conclusiones han sido cerradas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al cerrar las conclusiones: ' . $e->getMessage()
            ], 500);
        }
    }
}
