<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StateUser;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserActivationController extends Controller
{
    /**
     * Muestra la página de activación de usuario
     */
    public function showActivationPage($userId, $token): View
    {
        $user = User::find($userId);
        
        if (!$user) {
            abort(404, 'Usuario no encontrado');
        }
        
        // Verificar si el token es válido (usando el hash del email como token simple)
        $expectedToken = Hash::make($user->user);
        if (!Hash::check($user->user, $token)) {
            abort(404, 'Enlace de activación inválido');
        }
        
        // Verificar si el usuario ya está activado
        $isActive = $user->status_users_id == StateUser::STATUS_ACTIVE;
        
        return view('user-activation', compact('user', 'isActive', 'token'));
    }
    
    /**
     * Activa el usuario
     */
    public function activateUser(Request $request, $userId, $token): JsonResponse
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
        
        // Verificar si el token es válido
        $expectedToken = Hash::make($user->user);
        if (!Hash::check($user->user, $token)) {
            return response()->json([
                'success' => false,
                'message' => 'Enlace de activación inválido'
            ], 400);
        }
        
        // Verificar si el usuario ya está activado
        if ($user->status_users_id == StateUser::STATUS_ACTIVE) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario ya está activado'
            ], 400);
        }
        
        // Activar el usuario
        $user->status_users_id = StateUser::STATUS_ACTIVE;
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Usuario activado correctamente'
        ]);
    }
}
