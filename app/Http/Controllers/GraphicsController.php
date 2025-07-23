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
        
        // Obtener la ruta actual del usuario
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
            
            // Calcular la zona basada en dependencia e influencia
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
    
    /**
     * Calcula la zona de una variable basada en su dependencia e influencia
     */
    private function calculateZone($dependencia, $influencia, $triedId): string
    {
        // Obtener todas las variables de la ruta actual para calcular límites reales
        $variables = Variable::where('user_id', Auth::id())
            ->where('tried_id', $triedId)
            ->get();
        
        // Calcular dependencia e influencia para todas las variables
        $matriz = Matriz::where('user_id', Auth::id())
            ->where('tried_id', $triedId)
            ->get();
        
        $allDependencias = [];
        $allInfluencias = [];
        
        foreach ($variables as $var) {
            // Dependencia: suma de la fila (lo que la variable da a otras)
            $dep = $matriz->where('id_variable', $var->id)->sum('id_resp_influ');
            $allDependencias[] = $dep;
            
            // Influencia: suma de la columna (lo que la variable recibe de otras)
            $inf = $matriz->where('id_resp_depen', $var->id)->sum('id_resp_influ');
            $allInfluencias[] = $inf;
        }
        
        // Usar exactamente la misma lógica que la gráfica
        $minX = 10; // Valor mínimo por defecto (igual que en la gráfica)
        $minY = 12; // Valor mínimo por defecto (igual que en la gráfica)
        
        // Calcular máximos como en la gráfica
        $maxX = $minX;
        $maxY = $minY;
        
        foreach ($allDependencias as $dep) {
            if ($dep > $maxX) $maxX = $dep;
        }
        foreach ($allInfluencias as $inf) {
            if ($inf > $maxY) $maxY = $inf;
        }
        
        // Redondear hacia arriba como en la gráfica
        $maxX = ceil($maxX);
        $maxY = ceil($maxY);
        
        // Calcular el centro como en la gráfica
        $centroX = $maxX / 2;
        $centroY = $maxY / 2;
        
        // Determinar la zona basada en la posición relativa al centro (igual que en la gráfica)
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
    
    /**
     * Determina si una variable está en la frontera (cerca del límite entre zonas)
     */
    private function isOnFrontier($dependencia, $influencia, $triedId): bool
    {
        // Obtener todas las variables de la ruta actual para calcular límites reales
        $variables = Variable::where('user_id', Auth::id())
            ->where('tried_id', $triedId)
            ->get();
        
        // Calcular dependencia e influencia para todas las variables
        $matriz = Matriz::where('user_id', Auth::id())
            ->where('tried_id', $triedId)
            ->get();
        
        $allDependencias = [];
        $allInfluencias = [];
        
        foreach ($variables as $var) {
            // Dependencia: suma de la fila (lo que la variable da a otras)
            $dep = $matriz->where('id_variable', $var->id)->sum('id_resp_influ');
            $allDependencias[] = $dep;
            
            // Influencia: suma de la columna (lo que la variable recibe de otras)
            $inf = $matriz->where('id_resp_depen', $var->id)->sum('id_resp_influ');
            $allInfluencias[] = $inf;
        }
        
        // Usar exactamente la misma lógica que la gráfica
        $minX = 10; // Valor mínimo por defecto (igual que en la gráfica)
        $minY = 12; // Valor mínimo por defecto (igual que en la gráfica)
        
        // Calcular máximos como en la gráfica
        $maxX = $minX;
        $maxY = $minY;
        
        foreach ($allDependencias as $dep) {
            if ($dep > $maxX) $maxX = $dep;
        }
        foreach ($allInfluencias as $inf) {
            if ($inf > $maxY) $maxY = $inf;
        }
        
        // Redondear hacia arriba como en la gráfica
        $maxX = ceil($maxX);
        $maxY = ceil($maxY);
        
        // Calcular el centro como en la gráfica
        $centroX = $maxX / 2;
        $centroY = $maxY / 2;
        
        // Calcular tolerancia más estricta (10% del rango total)
        $toleranceX = ($maxX - $minX) * 0.1;
        $toleranceY = ($maxY - $minY) * 0.1;
        
        // Una variable está en frontera si está cerca de las líneas de la cruz
        // (línea vertical en centroX o línea horizontal en centroY)
        $nearVerticalLine = abs($dependencia - $centroX) <= $toleranceX;
        $nearHorizontalLine = abs($influencia - $centroY) <= $toleranceY;
        
        return $nearVerticalLine || $nearHorizontalLine;
    }
} 