<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Variable;
use App\Models\Matriz;
use App\Models\Traceability;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class GraphicsController extends Controller
{
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        $currentRoute = Traceability::getCurrentRouteForUser($userId);
        
        if (!$currentRoute) {
            return response()->json([
                'status' => 400,
                'message' => 'No se encontró ruta para el usuario'
            ]);
        }
        
        $variables = Variable::where('user_id', $userId)
            ->where('tried_id', $currentRoute->id)
            ->orderBy('id', 'asc')
            ->get(['id', 'id_variable as codigo', 'name_variable as nombre']);

        $matriz = Matriz::where('user_id', $userId)
            ->where('tried_id', $currentRoute->id)
            ->get();

        $matrizCruzada = [];
        foreach ($variables as $origen) {
            foreach ($variables as $destino) {
                if ($origen->codigo === $destino->codigo) {
                    $matrizCruzada[$origen->codigo][$destino->codigo] = null; 
                } else {
                    $valor = $matriz->where('id_variable', $origen->id)
                                    ->where('id_resp_depen', $destino->id)
                                    ->value('id_resp_influ') ?? 0;
                    $matrizCruzada[$origen->codigo][$destino->codigo] = $valor;
                }
            }
        }

        $result = [];
        foreach ($variables as $var) {
            $codigo = $var->codigo;
            
            $dependencia = 0;
            foreach ($variables as $destino) {
                if ($codigo !== $destino->codigo) {
                    $dependencia += $matrizCruzada[$codigo][$destino->codigo] ?? 0;
                }
            }
            
            $influencia = 0;
            foreach ($variables as $origen) {
                if ($origen->codigo !== $codigo) {
                    $influencia += $matrizCruzada[$origen->codigo][$codigo] ?? 0;
                }
            }

            $zone = $this->calculateZone($dependencia, $influencia, $currentRoute->id);
            
            $result[] = [
                'codigo' => $codigo,
                'dependencia' => $dependencia,
                'influencia' => $influencia,
                'zone' => $zone,
                'frontera' => $this->isOnFrontier($dependencia, $influencia, $currentRoute->id)
            ];
        }

        return response()->json([
            'status' => 200,
            'data' => $result
        ]);
    }

    private function calculateZone($dependencia, $influencia, $triedId): string
    {
        
        $variables = Variable::where('user_id', Auth::id())
            ->where('tried_id', $triedId)
            ->get();

        $matriz = Matriz::where('user_id', Auth::id())
            ->where('tried_id', $triedId)
            ->get();
        
        $allDependencias = [];
        $allInfluencias = [];
        
        foreach ($variables as $var) {
            
            $dep = $matriz->where('id_variable', $var->id)->sum('id_resp_influ');
            $allDependencias[] = $dep;

            $inf = $matriz->where('id_resp_depen', $var->id)->sum('id_resp_influ');
            $allInfluencias[] = $inf;
        }

        $minX = 10; 
        $minY = 12; 

        $maxX = $minX;
        $maxY = $minY;
        
        foreach ($allDependencias as $dep) {
            if ($dep > $maxX) $maxX = $dep;
        }
        foreach ($allInfluencias as $inf) {
            if ($inf > $maxY) $maxY = $inf;
        }

        $maxX = ceil($maxX);
        $maxY = ceil($maxY);

        $centroX = $maxX / 2;
        $centroY = $maxY / 2;

        if ($dependencia <= $centroX && $influencia > $centroY) {
            return 'ZONA DE PODER';
        } elseif ($dependencia > $centroX && $influencia >= $centroY) {
            return 'ZONA DE CONFLICTO';
        } elseif ($dependencia <= $centroX && $influencia <= $centroY) {
            return 'ZONA DE INDIFERENCIA';
        } else {
            return 'ZONA DE SALIDA';
        }
    }

    private function isOnFrontier($dependencia, $influencia, $triedId): bool
    {
        
        $variables = Variable::where('user_id', Auth::id())
            ->where('tried_id', $triedId)
            ->get();

        $matriz = Matriz::where('user_id', Auth::id())
            ->where('tried_id', $triedId)
            ->get();
        
        $allDependencias = [];
        $allInfluencias = [];
        
        foreach ($variables as $var) {
            
            $dep = $matriz->where('id_variable', $var->id)->sum('id_resp_influ');
            $allDependencias[] = $dep;

            $inf = $matriz->where('id_resp_depen', $var->id)->sum('id_resp_influ');
            $allInfluencias[] = $inf;
        }

        $minX = 10; 
        $minY = 12; 

        $maxX = $minX;
        $maxY = $minY;
        
        foreach ($allDependencias as $dep) {
            if ($dep > $maxX) $maxX = $dep;
        }
        foreach ($allInfluencias as $inf) {
            if ($inf > $maxY) $maxY = $inf;
        }

        $maxX = ceil($maxX);
        $maxY = ceil($maxY);

        $centroX = $maxX / 2;
        $centroY = $maxY / 2;

        $toleranceX = ($maxX - $minX) * 0.1;
        $toleranceY = ($maxY - $minY) * 0.1;

        $nearVerticalLine = abs($dependencia - $centroX) <= $toleranceX;
        $nearHorizontalLine = abs($influencia - $centroY) <= $toleranceY;
        
        return $nearVerticalLine || $nearHorizontalLine;
    }
}