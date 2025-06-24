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

        // Calcular dependencia e influencia para cada variable
        $result = [];
        foreach ($variables as $variable) {
            $dependencia = $matriz->where('id_resp_depen', $variable->id)->sum('id_resp_influ');
            $influencia = $matriz->where('id_variable', $variable->id)->sum('id_resp_influ');
            $result[] = [
                'codigo' => $variable->codigo,
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