<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $notes = Note::getByUser($user->id);
        
        return response()->json([
            'success' => true,
            'data' => $notes
        ]);
    }

    public function getLatest(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $note = Note::getLatestByUser($user->id);
        
        return response()->json([
            'success' => true,
            'data' => $note
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'content' => 'required|string|max:10000', 
            'title' => 'nullable|string|max:255'
        ]);

        $note = Note::create([
            'user_id' => $user->id,
            'content' => $request->input('content'),
            'title' => $request->input('title')
        ]);

        return response()->json([
            'success' => true,
            'data' => $note,
            'message' => 'Nota guardada correctamente'
        ]);
    }

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