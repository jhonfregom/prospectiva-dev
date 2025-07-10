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
        $variables = Variable::orderBy('id', 'desc');
        $variables = $variables->where('user_id', Auth::id())->get();
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
            $userVariablesCount = Variable::where('user_id', $user->id)->count();

            if ($userVariablesCount >= 15) {
                return response()->json([
                    'data' => null,
                    'status' => 400,
                    'message' => 'Has alcanzado el límite máximo de 15 variables'
                ], 400);
            }

            $request->validate([
                'name_variable' => 'required|string|max:80'
            ]);

            $nextVariableNumber = $userVariablesCount + 1;

            $variable = Variable::create([
                'id_variable' => 'V' . $nextVariableNumber,
                'name_variable' => $request->name_variable,
                'description' => '',
                'score' => 0,
                'state' => '0',
                'user_id' => $user->id
            ]);

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

            // Si ya está bloqueada, no permitir editar
            if ($variable->state === '1') {
                return response()->json([
                    'data' => $variable,
                    'status' => 200,
                    'message' => 'Esta variable ya está bloqueada y no se puede editar.'
                ]);
            }

            $validated = $request->validate([
                'description' => 'nullable|string',
                'score' => 'required|integer'
            ]);

            // Contador de ediciones en sesión (por usuario y variable)
            $sessionKey = 'variable_edit_count_' . $variable->id . '_user_' . $userId;
            $editCount = session($sessionKey, 0) + 1;
            session([$sessionKey => $editCount]);

            \Log::info('Variable update - ID: ' . $variable->id . ', Edit count: ' . $editCount . ', Current state: ' . $variable->state);

            $variable->description = $validated['description'] ?? '';
            $variable->score = $validated['score'];

            // Si es la tercera edición o más, bloquear (después de la segunda)
            if ($editCount >= 3) {
                $variable->state = '1';
                \Log::info('Variable update - Blocking variable ID: ' . $variable->id);
            }

            $variable->save();

            \Log::info('Variable update - Final state: ' . $variable->state . ', Response data: ' . json_encode($variable));

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
            $variable = Variable::findOrFail($id);
            $variable->delete();
            
            return response()->json([
                'status' => 200,
                'message' => 'Variable eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar la variable',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Devuelve todas las variables del usuario con sus condiciones iniciales
     */
    public function getInitialConditions(): JsonResponse
    {
        $variables = Variable::where('user_id', Auth::id())
            ->orderBy('id_variable', 'asc')
            ->get(['id', 'id_variable', 'name_variable', 'now_condition', 'state']);
        return response()->json([
            'data' => $variables,
            'status' => 200,
            'message' => 'Condiciones iniciales obtenidas correctamente'
        ]);
    }

    /**
     * Actualiza el campo now_condition de una variable específica
     */
    public function updateInitialCondition(Request $request, $id): JsonResponse
    {
        try {
            $variable = Variable::where('user_id', Auth::id())->findOrFail($id);
            $userId = Auth::id();

            // Si ya está bloqueada, no permitir editar
            if ($variable->state === '1') {
                return response()->json([
                    'data' => $variable,
                    'status' => 200,
                    'message' => 'Esta variable ya está bloqueada y no se puede editar.'
                ]);
            }

            $validated = $request->validate([
                'now_condition' => 'nullable|string|max:1000'
            ]);

            // Contador de ediciones en sesión (por usuario y variable para condiciones iniciales)
            $sessionKey = 'initial_condition_edit_count_' . $variable->id . '_user_' . $userId;
            $editCount = session($sessionKey, 0) + 1;
            session([$sessionKey => $editCount]);

            \Log::info('Initial condition update - ID: ' . $variable->id . ', Edit count: ' . $editCount . ', Current state: ' . $variable->state);

            $variable->now_condition = $validated['now_condition'] ?? '';

            // Si es la tercera edición o más, bloquear (después de la segunda)
            if ($editCount >= 3) {
                $variable->state = '1';
                \Log::info('Initial condition update - Blocking variable ID: ' . $variable->id);
            }

            $variable->save();

            \Log::info('Initial condition update - Final state: ' . $variable->state . ', Response data: ' . json_encode($variable));

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
}
