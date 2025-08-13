<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TextsController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Incluir el archivo de configuración
            include(resource_path('config/shared-variables/main.php'));
            
            return response()->json([
                'status' => 200,
                'data' => $texts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al cargar los textos: ' . $e->getMessage()
            ], 500);
        }
    }
}
