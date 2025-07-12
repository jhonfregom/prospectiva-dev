<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Variable;
use App\Models\Matriz;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            // Se elimina la validación de bloqueo para permitir guardar nuevas matrices.
            // La lógica para decidir si se puede guardar o no, eventualmente
            // dependerá de la nueva funcionalidad de "reiniciar".

            $data = $request->validate([
                'matriz' => 'required|array',
                'matriz.*.id_variable' => 'required|integer',
                'matriz.*.id_resp_depen' => 'required|integer',
                'matriz.*.id_resp_influ' => 'required|integer|min:0|max:3'
            ]);

            // Determinar el siguiente id_matriz
            $nextIdMatriz = Matriz::max('id_matriz') + 1;

            // Se elimina la sección que borraba los registros antiguos.
            // Ahora, cada guardado creará un nuevo conjunto de registros con un nuevo id_matriz.

            // Si la tabla está vacía, reiniciar el auto-incremento del ID
            if (Matriz::count() === 0) {
                DB::statement('ALTER TABLE matriz AUTO_INCREMENT = 1');
            }

            // Insertar nuevos registros
            foreach ($data['matriz'] as $item) {
                $itemId = $this->findNextAvailableId();
                Matriz::create([
                    'id' => $itemId,
                    'id_matriz' => $nextIdMatriz, // Usar el nuevo id_matriz
                    'id_variable' => $item['id_variable'],
                    'id_resp_depen' => $item['id_resp_depen'],
                    'id_resp_influ' => $item['id_resp_influ'],
                    'user_id' => Auth::id(),
                    'state' => '1' // Establecer el estado a 1 para bloquear
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