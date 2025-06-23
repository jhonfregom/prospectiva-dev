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
            $userVariablesCount = Variable::where('user_id', Auth::id())->count();
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

            $existingIds = Variable::where('user_id', Auth::id())
                ->pluck('id')
                ->toArray();

            $newId = 1;
            while (in_array($newId, $existingIds) && $newId <= 15) {
                $newId++;
            }

            $variable = Variable::create([
                'id' => $newId,
                'id_variable' => 'V' . $newId,
                'name_variable' => $request->name_variable,
                'description' => '',
                'score' => 0,
                'state' => '0',
                'user_id' => Auth::id()
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

            $validated = $request->validate([
                'description' => 'required|string',
                'score' => 'required|integer'
            ]);

            $variable->description = $validated['description'];
            $variable->score = $validated['score'];
            $variable->save();

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
}
