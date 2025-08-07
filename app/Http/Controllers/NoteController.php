<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search');
            
            if ($search) {
                $notes = Note::searchByUser($user->id, $search);
            } else {
                $notes = Note::getByUserPaginated($user->id, $perPage);
            }
            
            return response()->json([
                'success' => true,
                'data' => $notes,
                'stats' => Note::getStatsByUser($user->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener notas', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las notas'
            ], 500);
        }
    }

    public function getLatest(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $note = Note::getLatestByUser($user->id);
            
            return response()->json([
                'success' => true,
                'data' => $note
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener última nota', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la última nota'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'content' => 'required|string|max:10000|min:1', 
                'title' => 'nullable|string|max:255'
            ]);

            $note = Note::create([
                'user_id' => $user->id,
                'content' => trim($validated['content']),
                'title' => $validated['title'] ? trim($validated['title']) : null
            ]);

            Log::info('Nota creada exitosamente', [
                'user_id' => $user->id,
                'note_id' => $note->id,
                'title_length' => strlen($note->title ?? ''),
                'content_length' => strlen($note->content)
            ]);

            return response()->json([
                'success' => true,
                'data' => $note->load('user'),
                'message' => 'Nota guardada correctamente'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear nota', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la nota'
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'content' => 'required|string|max:10000|min:1',
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
                'content' => trim($validated['content']),
                'title' => $validated['title'] ? trim($validated['title']) : null
            ]);

            Log::info('Nota actualizada exitosamente', [
                'user_id' => $user->id,
                'note_id' => $note->id
            ]);

            return response()->json([
                'success' => true,
                'data' => $note->load('user'),
                'message' => 'Nota actualizada correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar nota', [
                'user_id' => Auth::id(),
                'note_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la nota'
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
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

            $noteId = $note->id;
            $note->delete();

            Log::info('Nota eliminada exitosamente', [
                'user_id' => $user->id,
                'note_id' => $noteId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nota eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar nota', [
                'user_id' => Auth::id(),
                'note_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la nota'
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $validated = $request->validate([
                'query' => 'required|string|min:2|max:100'
            ]);

            $notes = Note::searchByUser($user->id, $validated['query']);

            return response()->json([
                'success' => true,
                'data' => $notes,
                'query' => $validated['query'],
                'count' => $notes->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error en búsqueda de notas', [
                'user_id' => Auth::id(),
                'query' => $request->get('query'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda'
            ], 500);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $stats = Note::getStatsByUser($user->id);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de notas', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }
}