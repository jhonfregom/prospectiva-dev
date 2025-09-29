<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserActivationController extends Controller
{
    /**
     * Mostrar página de activación de usuario
     */
    public function showActivationPage(Request $request, $userId, $token)
    {
        $user = User::find($userId);
        
        if (!$user) {
            return redirect('/')->with('error', 'Usuario no encontrado.');
        }
        
        // Verificar si el usuario ya está activado
        if ($user->status_users_id == 1) {
            return redirect('/')->with('info', 'El usuario ya está activado.');
        }

        // Verificar si el token es válido y no ha expirado
        if ($user->activation_token !== $token) {
            return redirect('/')->with('error', 'Enlace de activación inválido.');
        }

        if ($user->activation_token_expires_at && now()->isAfter($user->activation_token_expires_at)) {
            return redirect('/')->with('error', 'El enlace de activación ha expirado.');
        }
        
        return view('user-activation', compact('user', 'token'));
    }

    /**
     * Activar usuario
     */
    public function activateUser(Request $request, $userId, $token): JsonResponse
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }
        
        // Verificar si el usuario ya está activado
        if ($user->status_users_id == 1) {
            return response()->json(['success' => false, 'message' => 'El usuario ya está activado.'], 400);
        }

        // Verificar si el token es válido y no ha expirado
        if ($user->activation_token !== $token) {
            return response()->json(['success' => false, 'message' => 'Enlace de activación inválido.'], 400);
        }

        if ($user->activation_token_expires_at && now()->isAfter($user->activation_token_expires_at)) {
            return response()->json(['success' => false, 'message' => 'El enlace de activación ha expirado.'], 400);
        }

        // Activar el usuario
        $user->status_users_id = 1;
        $user->activation_token = null; // Invalidar el token
        $user->activation_token_expires_at = null;
        $user->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Usuario activado correctamente.'
        ]);
    }

    /**
     * Cancelar activación
     */
    public function cancelActivation(Request $request, $userId, $token): JsonResponse
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        // Verificar si el token es válido y no ha expirado
        if ($user->activation_token !== $token) {
            return response()->json(['success' => false, 'message' => 'Enlace de activación inválido.'], 400);
        }

        if ($user->activation_token_expires_at && now()->isAfter($user->activation_token_expires_at)) {
            return response()->json(['success' => false, 'message' => 'El enlace de activación ha expirado.'], 400);
        }

        // Invalidar el token para que no se pueda usar nuevamente
        $user->activation_token = null;
        $user->activation_token_expires_at = null;
        $user->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Activación cancelada. El enlace ya no es válido.'
        ]);
    }
}





