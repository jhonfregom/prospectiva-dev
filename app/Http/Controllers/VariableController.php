<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Variable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class VariableController extends Controller
{
    public function index(): JsonResponse
    {
        
        $currentRoute = \App\Models\Traceability::getCurrentRouteForUser(Auth::id());
        
        if (!$currentRoute) {
            return response()->json([
                'data' => [],
                'status' => 200,
                'message' => 'No se encontró ruta para el usuario'
            ]);
        }
        
        $variables = Variable::where('user_id', Auth::id())
            ->where('tried_id', $currentRoute->id)
            ->orderBy('id', 'desc')
            ->get();
            
        return response()->json([
            'data' => $variables,
            'status' => 200,
            'message' => 'Variables obtenidas correctamente'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $currentRoute = \App\Models\Traceability::getCurrentRouteForUser($user->id);
            
            if (!$currentRoute) {
                return response()->json([
                    'data' => null,
                    'status' => 400,
                    'message' => 'No se encontró ruta para el usuario'
                ], 400);
            }

            $userVariablesCount = Variable::where('user_id', $user->id)
                ->where('tried_id', $currentRoute->id)
                ->count();

            if ($userVariablesCount >= 15) {
                return response()->json([
                    'data' => null,
                    'status' => 400,
                    'message' => 'Has alcanzado el límite máximo de 15 variables para esta ruta'
                ], 400);
            }

            $request->validate([
                'name_variable' => 'required|string|max:80'
            ]);

            $existingVariables = Variable::where('user_id', $user->id)
                ->where('tried_id', $currentRoute->id)
                ->pluck('id_variable')
                ->toArray();
            $nextVariableNumber = 1;
            while (in_array('V' . $nextVariableNumber, $existingVariables)) {
                $nextVariableNumber++;
            }

            $existingIds = Variable::orderBy('id')->pluck('id')->toArray();
            $nextId = 1;
            foreach ($existingIds as $existingId) {
                if ($existingId > $nextId) {
                    break;
                }
                $nextId = $existingId + 1;
            }

            $variable = Variable::create([
                'id' => $nextId,
                'id_variable' => 'V' . $nextVariableNumber,
                'name_variable' => $request->name_variable,
                'description' => '',
                'score' => 0,
                'state' => '0',
                'edits_variable' => 0, 
                'edits_now_condition' => 0, 
                'user_id' => $user->id,
                'tried_id' => $currentRoute->id
            ]);
            
            $sessionKey = 'variable_edit_count_' . $variable->id . '_user_' . $user->id;
            session([$sessionKey => 0]);

            return response()->json([
                'data' => $variable,
                'status' => 201,
                'message' => 'Variable creada correctamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'data' => null,
                'status' => 500,
                'message' => 'Error al crear la variable: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $variable = Variable::findOrFail($id);
            $userId = Auth::id();

            \Log::info('Variable update - Request data: ' . json_encode($request->all()));
            \Log::info('Variable update - Current edits_variable: ' . $variable->edits_variable);

            $validated = $request->validate([
                'description' => 'nullable|string',
                'score' => 'nullable|integer',
                'edits_variable' => 'nullable|integer',
                'state' => 'nullable|integer', 
            ]);

            $variable->description = $validated['description'] ?? '';
            if (isset($validated['score'])) {
            $variable->score = $validated['score'];
            }
            
            if (isset($validated['edits_variable'])) {
                $variable->edits_variable = $validated['edits_variable'];
                \Log::info('Variable update - Using frontend edits_variable: ' . $validated['edits_variable']);
            } else {
                
                $oldValue = $variable->edits_variable ?? 0;
                $variable->edits_variable = $oldValue + 1;
                \Log::info('Variable update - Incrementing edits_variable from ' . $oldValue . ' to ' . $variable->edits_variable);
            }
            
            if (isset($validated['state'])) {
                $variable->state = (string) $validated['state'];
            }
            
            if ($variable->edits_variable >= 3) {
                \Log::info('Variable update - Bloqueando edición de variable ID: ' . $variable->id);
            }
            $variable->save();

            \Log::info('Variable update - Final edits_variable: ' . $variable->edits_variable . ', Response data: ' . json_encode($variable));

            return response()->json([
                'status' => 200,
                'message' => 'Variable actualizada correctamente',
                'data' => $variable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar la variable: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            
            $user = Auth::user();
            if ($user->role !== 1) {
                return response()->json([
                    'status' => 403,
                    'message' => 'No tienes permisos para eliminar variables. Solo los administradores pueden realizar esta acción.'
                ], 403);
            }

            \Log::info('Intentando borrar variable ID: ' . $id);
            $variable = Variable::findOrFail($id);
            $variable->delete();
            \Log::info('Variable borrada ID: ' . $id);
            
            return response()->json([
                'status' => 200,
                'message' => 'Variable eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar variable ID: ' . $id . ' - ' . $e->getMessage());
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar la variable',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getInitialConditions(): JsonResponse
    {
        
        $currentRoute = \App\Models\Traceability::getCurrentRouteForUser(Auth::id());
        
        if (!$currentRoute) {
            return response()->json([
                'data' => [],
                'status' => 200,
                'message' => 'No se encontró ruta para el usuario'
            ]);
        }
        
        $variables = Variable::where('user_id', Auth::id())
            ->where('tried_id', $currentRoute->id)
            ->orderByRaw('CAST(SUBSTRING(id_variable, 2) AS UNSIGNED) ASC')
            ->get(['id', 'id_variable', 'name_variable', 'now_condition', 'state', 'edits_now_condition']);
        return response()->json([
            'data' => $variables,
            'status' => 200,
            'message' => 'Condiciones iniciales obtenidas correctamente'
        ]);
    }

    public function updateInitialCondition(Request $request, $id): JsonResponse
    {
        try {
            $variable = Variable::where('user_id', Auth::id())->findOrFail($id);
            $userId = Auth::id();

            \Log::info('Initial condition update - Request data: ' . json_encode($request->all()));
            \Log::info('Initial condition update - Current edits_now_condition: ' . $variable->edits_now_condition);

            if ($variable->edits_now_condition >= 3) {
                return response()->json([
                    'data' => $variable,
                    'status' => 200,
                    'message' => 'La condición inicial ya está bloqueada y no se puede editar.'
                ]);
            }

            $validated = $request->validate([
                'now_condition' => 'nullable|string|max:1000',
                'edits_now_condition' => 'nullable|integer',
                'state' => 'nullable|integer'
            ]);

            $variable->now_condition = $validated['now_condition'] ?? '';

            if (isset($validated['edits_now_condition'])) {
                $variable->edits_now_condition = $validated['edits_now_condition'];
                \Log::info('Initial condition update - Using frontend edits_now_condition: ' . $validated['edits_now_condition']);
            } else {
                
                $oldValue = $variable->edits_now_condition ?? 0;
                $variable->edits_now_condition = $oldValue + 1;
                \Log::info('Initial condition update - Incrementing edits_now_condition from ' . $oldValue . ' to ' . $variable->edits_now_condition);
            }

            if ($variable->edits_now_condition >= 3) {
                $variable->state = '1';
            } else if (isset($validated['state'])) {
                
                $variable->state = (string) $validated['state'];
            }

            $variable->save();

            \Log::info('Initial condition update - Final edits_now_condition: ' . $variable->edits_now_condition . ', Response data: ' . json_encode($variable));

            return response()->json([
                'status' => 200,
                'message' => 'Condición inicial actualizada correctamente',
                'data' => $variable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar la condición inicial: ' . $e->getMessage()
            ], 500);
        }
    }

    public function closeAllInitialConditions(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $updated = \App\Models\Variable::where('user_id', $userId)
                ->update([
                    'edits_now_condition' => 3,
                    'state' => '1' 
                ]);
            return response()->json([
                'status' => 200,
                'message' => 'Todas las condiciones iniciales han sido cerradas correctamente',
                'updated' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al cerrar las condiciones iniciales: ' . $e->getMessage()
            ], 500);
        }
    }
}