<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Variable;
use App\Models\Matriz;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GraphicsController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        $variables = Variable::where('user_id', $userId)
            ->orderBy('id', 'asc')
            ->get(['id', 'id_variable as codigo', 'name_variable as nombre']);

        $matriz = Matriz::where('user_id', $userId)->get();

        // Construir matriz cruzada: [origen][destino] = valor
        $matrizCruzada = [];
        foreach ($variables as $origen) {
            foreach ($variables as $destino) {
                if ($origen->codigo === $destino->codigo) {
                    $matrizCruzada[$origen->codigo][$destino->codigo] = null; // Diagonal
                } else {
                    $valor = $matriz->where('id_variable', $origen->id)
                                    ->where('id_resp_depen', $destino->id)
                                    ->value('id_resp_influ') ?? 0;
                    $matrizCruzada[$origen->codigo][$destino->codigo] = $valor;
                }
            }
        }

        // Calcular totales igual que en el frontend
        $result = [];
        foreach ($variables as $var) {
            $codigo = $var->codigo;
            // Dependencia: suma de la fila (lo que la variable da a otras)
            $dependencia = 0;
            foreach ($variables as $destino) {
                if ($codigo !== $destino->codigo) {
                    $dependencia += $matrizCruzada[$codigo][$destino->codigo] ?? 0;
                }
            }
            // Influencia: suma de la columna (lo que la variable recibe de otras)
            $influencia = 0;
            foreach ($variables as $origen) {
                if ($origen->codigo !== $codigo) {
                    $influencia += $matrizCruzada[$origen->codigo][$codigo] ?? 0;
                }
            }
            $result[] = [
                'codigo' => $codigo,
                'dependencia' => $dependencia,
                'influencia' => $influencia
            ];
        }

        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }
} 