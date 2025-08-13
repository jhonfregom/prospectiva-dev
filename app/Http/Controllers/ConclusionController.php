<?php

namespace App\Http\Controllers;

use App\Models\Conclusion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ConclusionController extends Controller
{
    
    private function findNextAvailableId(): int
    {
        $maxId = Conclusion::max('id') ?? 0;
        $usedIds = Conclusion::pluck('id')->toArray();

        for ($id = 1; $id <= $maxId + 1; $id++) {
            if (!in_array($id, $usedIds)) {
                return $id;
            }
        }
        
        return $maxId + 1;
    }

    private function createWithSpecificId(array $data, int $id): Conclusion
    {
        
        $traceability = \App\Models\Traceability::getCurrentRouteForUser($data['user_id']);
        $triedId = $traceability ? $traceability->id : null;

        \DB::statement("INSERT INTO conclusions (id, user_id, component_practice, actuality, aplication, state, tried_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())", [
            $id,
            $data['user_id'],
            $data['component_practice'] ?? '',
            $data['actuality'] ?? '',
            $data['aplication'] ?? '',
            $data['state'] ?? '0',
            $triedId
        ]);

        return Conclusion::find($id);
    }
    
    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $traceability = \App\Models\Traceability::getCurrentRouteForUser($userId);
            $triedId = $traceability ? $traceability->id : null;
            
            $conclusion = Conclusion::where('user_id', $userId)
                ->where('tried_id', $triedId)
                ->first();

            if (!$conclusion) {
                
                $nextId = $this->findNextAvailableId();

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

    public function store(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $traceability = \App\Models\Traceability::getCurrentRouteForUser($userId);
            $triedId = $traceability ? $traceability->id : null;
            
            $validatedData = $request->validate([
                'component_practice' => 'nullable|string',
                'actuality' => 'nullable|string',
                'aplication' => 'nullable|string',
                'state' => 'nullable|string|in:0,1'
            ]);

            $conclusion = Conclusion::where('user_id', $userId)
                ->where('tried_id', $triedId)
                ->first();

            if (!$conclusion) {
                
                $nextId = $this->findNextAvailableId();
                $validatedData['user_id'] = $userId;
                $conclusion = $this->createWithSpecificId($validatedData, $nextId);
            } else {
                
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

            $editField = $validatedData['field'] . '_edits';
            if ($conclusion->$editField >= 3) {
                return response()->json([
                    'status' => 403,
                    'message' => 'Este campo ya está bloqueado por ediciones.'
                ], 403);
            }

            $conclusion->{$validatedData['field']} = $validatedData['value'];
            $conclusion->$editField += 1;

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

    public function closeAll(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $conclusions = Conclusion::where('user_id', $userId)->get();

            if ($conclusions->isEmpty()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'No se encontraron conclusiones para cerrar'
                ], 404);
            }

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