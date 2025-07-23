<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Variable;
use App\Models\Matriz;
use App\Models\Traceability;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatrizController extends Controller
{
    public function index(): JsonResponse
    {
        // Obtener la ruta actual del usuario
        $currentRoute = Traceability::getCurrentRouteForUser(Auth::id());
        
        if (!$currentRoute) {
            return response()->json([
                'status' => 400,
                'message' => 'No se encontró ruta para el usuario'
            ]);
        }
        
        // Obtener todas las variables del usuario actual de la ruta actual
        $variables = Variable::where('user_id', Auth::id())
            ->where('tried_id', $currentRoute->id)
            ->orderBy('id', 'asc')
            ->get(['id', 'id_variable as codigo', 'name_variable as nombre']);

        // Obtener los valores de la matriz existentes de la ruta actual
        $matriz = Matriz::where('user_id', Auth::id())
            ->where('tried_id', $currentRoute->id)
            ->get();

        // Comprobar el estado de la matriz. Si existe algún registro, el estado es el del primer registro.
        $matrizState = $matriz->isNotEmpty() ? $matriz->first()->state : 0;

        return response()->json([
            'variables' => $variables,
            'matriz' => $matriz,
            'state' => $matrizState, // Enviar el estado de la matriz
            'status' => 200,
            'message' => 'Datos de matriz obtenidos correctamente'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'matriz' => 'required|array',
                'matriz.*.id_variable' => 'required|integer',
                'matriz.*.id_resp_depen' => 'required|integer',
                'matriz.*.id_resp_influ' => 'required|integer|min:0|max:3'
            ]);

            $userId = Auth::id();
            
            // Obtener la ruta actual del usuario
            $currentRoute = Traceability::getCurrentRouteForUser($userId);
            
            if (!$currentRoute) {
                return response()->json([
                    'status' => 400,
                    'message' => 'No se encontró ruta para el usuario'
                ]);
            }

            foreach ($data['matriz'] as $item) {
                $matriz = Matriz::where('user_id', $userId)
                    ->where('tried_id', $currentRoute->id)
                    ->where('id_variable', $item['id_variable'])
                    ->where('id_resp_depen', $item['id_resp_depen'])
                    ->first();

                if ($matriz) {
                    // Actualizar registro existente
                    $matriz->id_resp_influ = $item['id_resp_influ'];
                    $matriz->state = '1'; // Bloquear
                    $matriz->save();
                } else {
                    // Crear nuevo registro
                $itemId = $this->findNextAvailableId();
                Matriz::create([
                    'id' => $itemId,
                        'id_matriz' => Matriz::max('id_matriz') + 1,
                    'id_variable' => $item['id_variable'],
                    'id_resp_depen' => $item['id_resp_depen'],
                    'id_resp_influ' => $item['id_resp_influ'],
                        'user_id' => $userId,
                        'tried_id' => $currentRoute->id,
                        'state' => '1'
                ]);
                }
            }

            return response()->json([
                'status' => 200,
                'message' => 'Matriz guardada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al guardar la matriz: ' . $e->getMessage()
            ], 500);
        }
    }

    // Función para encontrar el primer ID disponible
    private function findNextAvailableId(): int
    {
        $existingIds = Matriz::orderBy('id')->pluck('id')->toArray();
        $expectedId = 1;
        foreach ($existingIds as $existingId) {
            if ($existingId > $expectedId) {
                return $expectedId;
            }
            $expectedId = $existingId + 1;
        }
        return $expectedId;
    }
} 