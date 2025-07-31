<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Traceability;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    /**
     * Obtener notas del usuario actual
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $traceabilityId = $request->input('traceability_id');
        
        $notes = Note::getByUserAndRoute($user->id, $traceabilityId);
        
        return response()->json([
            'success' => true,
            'data' => $notes
        ]);
    }

    /**
     * Obtener la nota más reciente del usuario
     */
    public function getLatest(Request $request): JsonResponse
    {
        $user = Auth::user();
        $traceabilityId = $request->input('traceability_id');
        
        $note = Note::getLatestByUser($user->id, $traceabilityId);
        
        return response()->json([
            'success' => true,
            'data' => $note
        ]);
    }

    /**
     * Crear una nueva nota
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'content' => 'required|string|max:10000', // Máximo 10,000 caracteres
            'title' => 'nullable|string|max:255',
            'traceability_id' => 'nullable|exists:traceability,id'
        ]);

        // Verificar que el traceability_id pertenece al usuario (si se proporciona)
        if ($request->input('traceability_id')) {
            $traceability = Traceability::where('id', $request->input('traceability_id'))
                ->where('user_id', $user->id)
                ->first();
                
            if (!$traceability) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruta no válida'
                ], 400);
            }
        }

        $note = Note::create([
            'user_id' => $user->id,
            'traceability_id' => $request->input('traceability_id'),
            'content' => $request->input('content'),
            'title' => $request->input('title')
        ]);

        return response()->json([
            'success' => true,
            'data' => $note,
            'message' => 'Nota guardada correctamente'
        ]);
    }

    /**
     * Actualizar una nota existente
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'content' => 'required|string|max:10000',
            'title' => 'nullable|string|max:255'
        ]);

        $note = Note::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada'
            ], 404);
        }

        $note->update([
            'content' => $request->input('content'),
            'title' => $request->input('title')
        ]);

        return response()->json([
            'success' => true,
            'data' => $note,
            'message' => 'Nota actualizada correctamente'
        ]);
    }

    /**
     * Eliminar una nota
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        
        $note = Note::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$note) {
            return response()->json([
                'success' => false,
                'message' => 'Nota no encontrada'
            ], 404);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Nota eliminada correctamente'
        ]);
    }
} 