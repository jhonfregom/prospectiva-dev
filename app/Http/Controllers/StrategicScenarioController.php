<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Scenarios;

class StrategicScenarioController extends Controller
{
    // Obtener el escenario 1 del usuario autenticado
    public function show(): JsonResponse
    {
        $userId = Auth::id();
        $scenario = Scenarios::where('user_id', $userId)->where('num_scenario', 1)->first();

        if (!$scenario) {
            return response()->json([
                'data' => null,
                'status' => 404,
                'message' => 'No existe el escenario estratégico.'
            ], 404);
        }

        return response()->json([
            'data' => $scenario,
            'status' => 200,
            'message' => 'Escenario estratégico obtenido correctamente.'
        ]);
    }

    // Actualizar los campos year1, year2, year3, edits, state del escenario 1
    public function update(Request $request): JsonResponse
    {
        $userId = Auth::id();
        Log::info('StrategicScenarioController@update - userId:', ['userId' => $userId]);
        Log::info('StrategicScenarioController@update - request data:', $request->all());
        $scenario = Scenarios::where('user_id', $userId)->where('num_scenario', 1)->first();
        Log::info('StrategicScenarioController@update - scenario found:', ['scenario' => $scenario]);

        if (!$scenario) {
            Log::warning('StrategicScenarioController@update - No scenario found for user', ['userId' => $userId]);
            return response()->json([
                'data' => null,
                'status' => 404,
                'message' => 'No existe el escenario estratégico.'
            ], 404);
        }

        $data = $request->validate([
            'year1' => 'nullable|string',
            'year2' => 'nullable|string',
            'year3' => 'nullable|string',
            'edits_year1' => 'nullable|integer',
            'edits_year2' => 'nullable|integer',
            'edits_year3' => 'nullable|integer',
            'state_year1' => 'nullable|string',
            'state_year2' => 'nullable|string',
            'state_year3' => 'nullable|string',
        ]);

        $scenario->update($data);

        return response()->json([
            'data' => $scenario,
            'status' => 200,
            'message' => 'Escenario estratégico actualizado correctamente.'
        ]);
    }
} 