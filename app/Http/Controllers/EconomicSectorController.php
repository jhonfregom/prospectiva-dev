<?php

namespace App\Http\Controllers;

use App\Models\EconomicSector;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EconomicSectorController extends Controller
{
    
    public function index(): JsonResponse
    {
        try {
            $sectors = EconomicSector::active()->ordered()->get();
            
            return response()->json([
                'success' => true,
                'data' => $sectors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los sectores económicos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $sector = EconomicSector::find($id);
            
            if (!$sector) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sector económico no encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $sector
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el sector económico',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}