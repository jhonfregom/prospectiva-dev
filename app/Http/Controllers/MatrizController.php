<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Variable;
use App\Models\Matriz;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MatrizController extends Controller
{
    public function index(): JsonResponse
    {
        // Obtener todas las variables del usuario actual
        $variables = Variable::where('user_id', Auth::id())
                           ->orderBy('id', 'asc')
                           ->get(['id', 'id_variable as codigo', 'name_variable as nombre']);

        // Obtener los valores de la matriz existentes
        $matriz = Matriz::where('user_id', Auth::id())->get();

        return response()->json([
            'variables' => $variables,
            'matriz' => $matriz,
            'status' => 200,
            'message' => 'Datos de matriz obtenidos correctamente'
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'matriz' => 'required|array',
                'matriz.*.id_matriz' => 'required|integer',
                'matriz.*.id_variable' => 'required|integer',
                'matriz.*.id_resp_depen' => 'required|integer',
                'matriz.*.id_resp_influ' => 'required|integer'
            ]);

            // Eliminar registros anteriores del usuario
            Matriz::where('user_id', Auth::id())->delete();

            // Insertar nuevos registros
            foreach ($data['matriz'] as $item) {
                Matriz::create([
                    'id_matriz' => $item['id_matriz'],
                    'id_variable' => $item['id_variable'],
                    'id_resp_depen' => $item['id_resp_depen'],
                    'id_resp_influ' => $item['id_resp_influ'],
                    'user_id' => Auth::id(),
                    'state' => '0'
                ]);
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
} 