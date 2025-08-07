<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\PasswordResetController;

class TestPasswordResetRequest extends Command
{
    protected $signature = 'test:password-reset-request {email} {new_password}';
    protected $description = 'Probar la solicitud de restablecimiento de contraseña';

    public function handle()
    {
        $email = $this->argument('email');
        $newPassword = $this->argument('new_password');

        $this->info("=== PRUEBA DE SOLICITUD DE RESTABLECIMIENTO ===");
        $this->info("Email: {$email}");
        $this->info("Nueva contraseña: {$newPassword}");
        $this->line('');

        $this->info("1. Buscando usuario...");
        $user = User::where('user', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado: {$email}");
            return 1;
        }

        $this->info("✅ Usuario encontrado: ID {$user->id}");
        $this->line('');

        $this->info("2. Generando token de restablecimiento...");
        $token = Str::random(64);
        
        $user->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(1),
        ]);

        $this->info("✅ Token generado: {$token}");
        $this->line('');

        $this->info("3. Simulando solicitud de restablecimiento...");
        
        $request = new Request();
        $request->merge([
            'token' => $token,
            'email' => $email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $controller = new PasswordResetController();
        
        try {
            $response = $controller->resetPassword($request);
            $responseData = json_decode($response->getContent(), true);
            
            $this->info("✅ Respuesta del controlador:");
            $this->info("   - Status: " . $response->getStatusCode());
            $this->info("   - Response status: " . ($responseData['status'] ?? 'N/A'));
            $this->info("   - Message: " . ($responseData['message'] ?? 'N/A'));
            
            if ($responseData['status'] === 'success') {
                $this->info("✅ Restablecimiento exitoso");
            } else {
                $this->error("❌ Error en restablecimiento: " . ($responseData['message'] ?? 'Error desconocido'));
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Excepción: " . $e->getMessage());
            return 1;
        }

        $this->line('');

        $this->info("4. Verificando actualización de contraseña...");
        $user->refresh();
        
        if (Hash::check($newPassword, $user->password)) {
            $this->info("✅ Contraseña actualizada correctamente");
        } else {
            $this->error("❌ La contraseña no se actualizó correctamente");
            return 1;
        }

        $this->info("5. Verificando limpieza de token...");
        if (!$user->password_reset_token && !$user->password_reset_expires_at) {
            $this->info("✅ Token limpiado correctamente");
        } else {
            $this->error("❌ El token no se limpió correctamente");
            return 1;
        }

        $this->line('');
        $this->info("=== PRUEBA COMPLETADA EXITOSAMENTE ===");
        $this->info("La solicitud de restablecimiento de contraseña funciona correctamente.");

        return 0;
    }
}