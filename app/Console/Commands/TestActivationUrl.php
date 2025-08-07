<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestActivationUrl extends Command
{
    protected $signature = 'test:activation-url {user_id} {token?}';
    protected $description = 'Probar la generación de URL de activación para un usuario';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $token = $this->argument('token');
        
        $this->info("=== PRUEBA DE URL DE ACTIVACIÓN ===");
        $this->info("Usuario ID: {$userId}");
        if ($token) {
            $this->info("Token: {$token}");
        }
        $this->line('');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado con ID: {$userId}");
            return 1;
        }
        
        $this->info("=== INFORMACIÓN DEL USUARIO ===");
        $this->info("Nombre: {$user->first_name} {$user->last_name}");
        $this->info("Email: {$user->user}");
        $this->info("Estado: {$user->status_users_id}");
        $this->info("Token de activación: " . ($user->activation_token ?: 'No tiene'));
        $this->info("Token expira: " . ($user->activation_token_expires_at ?: 'No expira'));
        $this->line('');
        
        if ($token) {
            $this->info("=== VERIFICACIÓN DE TOKEN ===");
            $this->info("Token proporcionado: {$token}");
            $this->info("Token en BD: " . ($user->activation_token ?: 'NULL'));
            $this->info("¿Coinciden?: " . ($user->activation_token === $token ? 'SÍ' : 'NO'));
            $this->info("¿Token expirado?: " . ($user->activation_token_expires_at && now()->isAfter($user->activation_token_expires_at) ? 'SÍ' : 'NO'));
            $this->line('');
        }
        
        // Generar URL de activación
        $activationUrl = 'http://localhost:8000/user-activation/' . $user->id . '/' . ($token ?: 'test-token');
        
        $this->info("=== URL DE ACTIVACIÓN ===");
        $this->info("URL: {$activationUrl}");
        $this->line('');
        
        $this->info("=== DIAGNÓSTICO ===");
        if ($user->status_users_id == 1) {
            $this->warn("⚠️  El usuario ya está activado (status_users_id = 1)");
        }
        if (!$user->activation_token) {
            $this->warn("⚠️  El usuario no tiene token de activación");
        }
        if ($token && $user->activation_token !== $token) {
            $this->warn("⚠️  El token no coincide");
        }
        if ($user->activation_token_expires_at && now()->isAfter($user->activation_token_expires_at)) {
            $this->warn("⚠️  El token ha expirado");
        }
        
        if ($user->status_users_id != 1 && $user->activation_token && (!$token || $user->activation_token === $token) && (!$user->activation_token_expires_at || !now()->isAfter($user->activation_token_expires_at))) {
            $this->info("✅ El usuario debería poder ser activado correctamente");
        } else {
            $this->error("❌ El usuario no puede ser activado con los datos actuales");
        }
    }
}
