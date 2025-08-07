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
        
        $currentRoute = Traceability::getCurrentRouteForUser(Auth::id());
        
        if (!$currentRoute) {
            return response()->json([
                'status' => 400,
                'message' => 'No se encontró ruta para el usuario'
            ]);
        }

        $variables = Variable::where('user_id', Auth::id())
            ->where('tried_id', $currentRoute->id)
            ->orderBy('id', 'asc')
            ->get(['id', 'id_variable as codigo', 'name_variable as nombre']);

        $matriz = Matriz::where('user_id', Auth::id())
            ->where('tried_id', $currentRoute->id)
            ->get();

        $matrizState = $matriz->isNotEmpty() ? $matriz->first()->state : 0;

        return response()->json([
            'variables' => $variables,
            'matriz' => $matriz,
            'state' => $matrizState, 
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
                    
                    $matriz->id_resp_influ = $item['id_resp_influ'];
                    $matriz->state = '1'; 
                    $matriz->save();
                } else {
                    
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