<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Scenarios;
use Illuminate\Support\Facades\Auth;

class ScenariosController extends Controller
{
    // Actualiza un escenario existente
    public function update($id, Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string',
            'edits' => 'required|integer',
            'state' => 'required|integer',
        ]);

        $scenario = Scenarios::find($id);
        if (!$scenario) {
            return response()->json([
                'data' => null,
                'status' => 404,
                'message' => 'Escenario no encontrado.'
            ], 404);
        }

        $scenario->titulo = $data['titulo'];
        $scenario->edits = $data['edits'];
        $scenario->state = (string) $data['state'];
        $scenario->save();

        return response()->json([
            'data' => $scenario,
            'status' => 200,
            'message' => 'Escenario actualizado correctamente.'
        ]);
    }

    private function findNextAvailableId(): int
    {
        $existingIds = Scenarios::orderBy('id')->pluck('id')->toArray();
        $expectedId = 1;
        foreach ($existingIds as $existingId) {
            if ($existingId > $expectedId) {
                return $expectedId;
            }
            $expectedId = $existingId + 1;
        }
        return $expectedId;
    }

    public function store(Request $request): JsonResponse
    {
        \Log::info('ScenariosController@store - Request data:', $request->all());
        
        $data = $request->validate([
            'titulo' => 'required|string',
            'edits' => 'required|integer',
            'state' => 'required|integer',
            'num_scenario' => 'required|integer',
            'year1' => 'nullable|string',
            'year2' => 'nullable|string',
            'year3' => 'nullable|string',
            'edits_year1' => 'nullable|integer',
            'edits_year2' => 'nullable|integer',
            'edits_year3' => 'nullable|integer',
        ]);

        \Log::info('ScenariosController@store - Validated data:', $data);

        $data['user_id'] = Auth::id();
        $data['state'] = (string) $data['state'];

        // Buscar si ya existe un registro para este usuario y num_scenario
        $scenario = Scenarios::where('user_id', $data['user_id'])
            ->where('num_scenario', $data['num_scenario'])
            ->first();

        \Log::info('ScenariosController@store - Found scenario:', ['scenario' => $scenario]);

        if ($scenario) {
            // Actualizar
            $scenario->titulo = $data['titulo'];
            $scenario->edits = $data['edits'];
            // Actualizar campos de año si vienen en el request
            if (array_key_exists('year1', $data)) {
                $scenario->year1 = $data['year1'];
                \Log::info('ScenariosController@store - Updating year1:', ['year1' => $data['year1']]);
            }
            if (array_key_exists('year2', $data)) {
                $scenario->year2 = $data['year2'];
                \Log::info('ScenariosController@store - Updating year2:', ['year2' => $data['year2']]);
            }
            if (array_key_exists('year3', $data)) {
                $scenario->year3 = $data['year3'];
                \Log::info('ScenariosController@store - Updating year3:', ['year3' => $data['year3']]);
            }
            // Actualizar contadores de edición si vienen en el request
            if (array_key_exists('edits_year1', $data)) {
                $scenario->edits_year1 = $data['edits_year1'];
                \Log::info('ScenariosController@store - Updating edits_year1:', ['edits_year1' => $data['edits_year1']]);
            }
            if (array_key_exists('edits_year2', $data)) {
                $scenario->edits_year2 = $data['edits_year2'];
                \Log::info('ScenariosController@store - Updating edits_year2:', ['edits_year2' => $data['edits_year2']]);
            }
            if (array_key_exists('edits_year3', $data)) {
                $scenario->edits_year3 = $data['edits_year3'];
                \Log::info('ScenariosController@store - Updating edits_year3:', ['edits_year3' => $data['edits_year3']]);
            }
            // Lógica de bloqueo: state = 1 solo si todos los contadores están en 3
            if (
                $scenario->edits >= 3 &&
                $scenario->edits_year1 >= 3 &&
                $scenario->edits_year2 >= 3 &&
                $scenario->edits_year3 >= 3
            ) {
                $scenario->state = '1';
            } else {
                $scenario->state = '0';
            }
            $scenario->save();
            \Log::info('ScenariosController@store - Scenario saved successfully');
            $status = 200;
            $message = 'Escenario actualizado correctamente.';
        } else {
            // Crear con el primer id libre
            $nextId = $this->findNextAvailableId();
            $data['id'] = $nextId;
            $scenario = Scenarios::create($data);
            $status = 201;
            $message = 'Escenario creado correctamente.';
        }

        return response()->json([
            'data' => $scenario,
            'status' => $status,
            'message' => $message
        ], $status);
    }

    public function index(): JsonResponse
    {
        $userId = Auth::id();
        $scenarios = Scenarios::where('user_id', $userId)->orderBy('id')->get();

        return response()->json([
            'data' => $scenarios,
            'status' => 200,
            'message' => 'Escenarios obtenidos correctamente.'
        ]);
    }
} 