<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Variable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador que maneja todas las operaciones relacionadas con variables
 * 
 * Este controlador implementa la lógica de negocio para el módulo de variables,
 * proporcionando endpoints REST para:
 * - Listar todas las variables
 * - Crear nuevas variables
 * - Actualizar variables existentes
 * - Eliminar variables
 * 
 * Cada método retorna respuestas JSON estandarizadas con:
 * - data: Datos solicitados/modificados
 * - status: Código de estado HTTP
 * - message: Mensaje descriptivo del resultado
 */
class VariableController extends Controller
{
    /**
     * Obtiene todas las variables ordenadas por ID descendente
     * 
     * Este método:
     * 1. Consulta todas las variables en la base de datos
     * 2. Las ordena por ID de forma descendente (más recientes primero)
     * 3. Retorna la colección completa en formato JSON
     * 
     * @return JsonResponse Respuesta JSON con:
     *         - data: Array de variables
     *         - status: 200 si la operación fue exitosa
     *         - message: Mensaje de éxito
     */
    public function index(): JsonResponse
    {
        $variables = Variable::orderBy('id', 'desc')->get();
        return response()->json([
            'data' => $variables,
            'status' => 200,
            'message' => 'Variables obtenidas correctamente'
        ]);
    }

    /**
     * Crea una nueva variable
     * 
     * Este método:
     * 1. Valida los datos de entrada
     * 2. Genera un nuevo ID secuencial
     * 3. Crea la variable con valores iniciales
     * 4. Asocia la variable al usuario actual
     * 
     * @param Request $request Contiene:
     *        - name_variable: Nombre de la variable (requerido, máx 80 caracteres)
     * 
     * @return JsonResponse Respuesta JSON con:
     *         - data: Variable creada
     *         - status: 200 si la creación fue exitosa
     *         - message: Mensaje de éxito
     */
    public function store(Request $request): JsonResponse
    {
        // Valida que el nombre de la variable sea requerido y tenga máximo 80 caracteres
        $request->validate([
            'name_variable' => 'required|string|max:80',
        ]);

        // Obtiene el último ID y genera el siguiente
        $lastId = Variable::max('id') ?? 0;
        $newId = $lastId + 1;

        // Crea la nueva variable con score inicial 0
        $variable = Variable::create([
            'id' => $newId,
            'id_variable' => 'V' . $newId,
            'name_variable' => $request->name_variable,
            'description' => '',
            'score' => 0,
            'state' => '0',
            'user_id' => Auth::id() // Obtiene el ID del usuario autenticado
        ]);

        return response()->json([
            'data' => $variable,
            'status' => 200,
            'message' => 'Variable creada correctamente'
        ]);
    }

    /**
     * Actualiza una variable existente
     * 
     * Este método:
     * 1. Busca la variable por ID
     * 2. Actualiza todos los campos enviados en la request
     * 3. Maneja posibles errores durante la actualización
     * 
     * @param Request $request Contiene los campos a actualizar:
     *        - description: Nueva descripción de la variable
     *        - score: Nueva puntuación calculada
     * @param int $id ID de la variable a actualizar
     * 
     * @return JsonResponse Respuesta JSON con:
     *         - data: Variable actualizada
     *         - status: 200 si la actualización fue exitosa, 500 en caso de error
     *         - message: Mensaje de éxito o error
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $variable = Variable::findOrFail($id);
            $variable->update($request->all());
            
            return response()->json([
                'status' => 200,
                'message' => 'Variable actualizada correctamente',
                'data' => $variable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al actualizar la variable',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Elimina una variable existente
     * 
     * Este método:
     * 1. Busca la variable por ID
     * 2. Elimina la variable de la base de datos
     * 3. Maneja posibles errores durante la eliminación
     *
     * @param int $id ID de la variable a eliminar
     * 
     * @return JsonResponse Respuesta JSON con:
     *         - status: 200 si la eliminación fue exitosa, 500 en caso de error
     *         - message: Mensaje de éxito o error
     */
    public function destroy($id)
    {
        try {
            $variable = Variable::findOrFail($id);
            $variable->delete();
            
            return response()->json([
                'status' => 200,
                'message' => 'Variable eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error al eliminar la variable',
                'error' => $e->getMessage()
            ]);
        }
    }
}