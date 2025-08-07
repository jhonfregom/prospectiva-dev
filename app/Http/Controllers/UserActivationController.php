<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserActivationController extends Controller
{
    
    public function showActivationPage(Request $request, $userId, $token)
    {
        
        $user = User::find($userId);
        
        if (!$user) {
            return redirect('/')->with('error', 'Usuario no encontrado.');
        }
        
        if ($user->status_users_id == 1) {
            return redirect('/')->with('info', 'El usuario ya está activado.');
        }

        $expectedToken = Hash::make($user->user . $user->id);
        if (!Hash::check($user->user . $user->id, $token)) {
            return redirect('/')->with('error', 'Enlace de activación inválido.');
        }
        
        return view('user-activation', compact('user', 'token'));
    }

    public function activateUser(Request $request, $userId, $token): JsonResponse
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }
        
        if ($user->status_users_id == 1) {
            return response()->json(['success' => false, 'message' => 'El usuario ya está activado.'], 400);
        }

        $expectedToken = Hash::make($user->user . $user->id);
        if (!Hash::check($user->user . $user->id, $token)) {
            return response()->json(['success' => false, 'message' => 'Enlace de activación inválido.'], 400);
        }

        $user->status_users_id = 1;
        $user->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Usuario activado correctamente.'
        ]);
    }

    public function cancelActivation(Request $request, $userId, $token): JsonResponse
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        $expectedToken = Hash::make($user->user . $user->id);
        if (!Hash::check($user->user . $user->id, $token)) {
            return response()->json(['success' => false, 'message' => 'Enlace de activación inválido.'], 400);
        }
        
        return response()->json([
            'success' => true, 
            'message' => 'Activación cancelada.'
        ]);
    }
}