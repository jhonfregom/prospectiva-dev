<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Scenario;
use Illuminate\Support\Facades\Auth;

class ScenariosController extends Controller
{
    // Listar los 4 escenarios del usuario autenticado
    public function index()
    {
        $userId = Auth::id();
        $scenarios = Scenario::where('user_id', $userId)->orderBy('id')->get();
        $defaults = [
            ['titulo' => 'Escenario 1', 'texto' => 'Escenario 1'],
            ['titulo' => 'Escenario 2', 'texto' => 'Escenario 2'],
            ['titulo' => 'Escenario 3', 'texto' => 'Escenario 3'],
            ['titulo' => 'Escenario 4', 'texto' => 'Escenario 4'],
        ];
        $toCreate = 4 - $scenarios->count();
        for ($i = 0; $i < $toCreate; $i++) {
            Scenario::create([
                'titulo' => $defaults[$scenarios->count() + $i]['titulo'],
                'texto' => $defaults[$scenarios->count() + $i]['texto'],
                'year1' => '',
                'year2' => '',
                'year3' => '',
                'user_id' => $userId
            ]);
        }
        $scenarios = Scenario::where('user_id', $userId)->orderBy('id')->get();
        // Si por alguna razón hay menos de 4, completar con objetos vacíos para que el frontend siempre reciba 4
        while ($scenarios->count() < 4) {
            $scenarios->push(new Scenario([
                'id' => null,
                'titulo' => '',
                'texto' => '',
                'year1' => '',
                'year2' => '',
                'year3' => '',
                'user_id' => $userId
            ]));
        }
        return response()->json($scenarios);
    }

    // Actualizar un escenario
    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $scenario = Scenario::where('id', $id)->where('user_id', $userId)->firstOrFail();
        $scenario->update($request->only(['titulo', 'texto', 'year1', 'year2', 'year3']));
        return response()->json($scenario);
    }
} 