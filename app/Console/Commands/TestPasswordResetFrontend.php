<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

class TestPasswordResetFrontend extends Command
{
    protected $signature = 'test:password-reset-frontend {email} {new_password}';
    protected $description = 'Simular el flujo completo del frontend de restablecimiento de contraseña';

    public function handle()
    {
        $email = $this->argument('email');
        $newPassword = $this->argument('new_password');

        $this->info("=== SIMULACIÓN DE FLUJO FRONTEND ===");
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

        $this->info("3. Generando URL de restablecimiento...");
        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $email]);
        $this->info("URL: {$resetUrl}");
        $this->line('');

        $this->info("4. Simulando solicitud del formulario...");

        $request = new Request();
        $request->merge([
            'token' => $token,
            'email' => $email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->info("Datos de la solicitud:");
        $this->info("  - Token: {$token}");
        $this->info("  - Email: {$email}");
        $this->info("  - Password: {$newPassword}");
        $this->info("  - Password Confirmation: {$newPassword}");
        $this->line('');

        $this->info("5. Ejecutando controlador...");
        $controller = new PasswordResetController();
        
        try {
            $response = $controller->resetPassword($request);
            $responseData = json_decode($response->getContent(), true);
            
            $this->info("✅ Respuesta del controlador:");
            $this->info("   - Status Code: " . $response->getStatusCode());
            $this->info("   - Response Status: " . ($responseData['status'] ?? 'N/A'));
            $this->info("   - Message: " . ($responseData['message'] ?? 'N/A'));
            
            if ($responseData['status'] === 'success') {
                $this->info("✅ Restablecimiento exitoso desde el frontend");
            } else {
                $this->error("❌ Error en restablecimiento: " . ($responseData['message'] ?? 'Error desconocido'));
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Excepción: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }

        $this->line('');

        $this->info("6. Verificando actualización de contraseña...");
        $user->refresh();
        
        if (Hash::check($newPassword, $user->password)) {
            $this->info("✅ Contraseña actualizada correctamente");
        } else {
            $this->error("❌ La contraseña no se actualizó correctamente");
            return 1;
        }

        $this->info("7. Verificando limpieza de token...");
        if (!$user->password_reset_token && !$user->password_reset_expires_at) {
            $this->info("✅ Token limpiado correctamente");
        } else {
            $this->error("❌ El token no se limpió correctamente");
            return 1;
        }

        $this->info("8. Probando autenticación con nueva contraseña...");
        $credentials = [
            'user' => $email,
            'password' => $newPassword
        ];

        if (auth()->attempt($credentials)) {
            $this->info("✅ Autenticación exitosa con nueva contraseña");
            auth()->logout();
        } else {
            $this->error("❌ Error en autenticación con nueva contraseña");
            return 1;
        }

        $this->line('');
        $this->info("=== SIMULACIÓN COMPLETADA EXITOSAMENTE ===");
        $this->info("El flujo del frontend funciona correctamente.");

        return 0;
    }
}