<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TestPasswordResetFlow extends Command
{
    protected $signature = 'test:password-reset-flow {email} {new_password}';
    protected $description = 'Probar el flujo completo de restablecimiento de contraseña';

    public function handle()
    {
        $email = $this->argument('email');
        $newPassword = $this->argument('new_password');

        $this->info("=== PRUEBA DE FLUJO DE RESTABLECIMIENTO DE CONTRASEÑA ===");
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
        $this->info("   - Email: {$user->user}");
        $this->info("   - Nombre: {$user->first_name} {$user->last_name}");
        $this->line('');

        $this->info("2. Verificando campos de restablecimiento...");
        $this->info("   - password_reset_token: " . ($user->password_reset_token ? 'Sí' : 'No'));
        $this->info("   - password_reset_expires_at: " . ($user->password_reset_expires_at ? $user->password_reset_expires_at : 'No'));
        $this->line('');

        $this->info("3. Generando token de restablecimiento...");
        $token = Str::random(64);
        
        $user->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(1),
        ]);

        $this->info("✅ Token generado: {$token}");
        $this->info("   - Expira: " . now()->addHours(1));
        $this->line('');

        $this->info("4. Simulando búsqueda de usuario con token...");
        $userWithToken = User::where('user', $email)
                           ->where('password_reset_token', $token)
                           ->where('password_reset_expires_at', '>', now())
                           ->first();

        if (!$userWithToken) {
            $this->error("❌ No se pudo encontrar el usuario con el token válido");
            return 1;
        }

        $this->info("✅ Usuario encontrado con token válido");
        $this->line('');

        $this->info("5. Actualizando contraseña...");
        $oldPasswordHash = $userWithToken->password;
        
        $userWithToken->update([
            'password' => Hash::make($newPassword),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        $this->info("✅ Contraseña actualizada");
        $this->info("   - Hash anterior: " . substr($oldPasswordHash, 0, 20) . "...");
        $this->info("   - Hash nuevo: " . substr($userWithToken->password, 0, 20) . "...");
        $this->line('');

        $this->info("6. Verificando nueva contraseña...");
        if (Hash::check($newPassword, $userWithToken->password)) {
            $this->info("✅ Verificación de contraseña exitosa");
        } else {
            $this->error("❌ Error en la verificación de contraseña");
            return 1;
        }
        $this->line('');

        $this->info("7. Verificando limpieza de token...");
        $user->refresh();
        $this->info("   - password_reset_token: " . ($user->password_reset_token ? 'No se limpió' : 'Limpiado'));
        $this->info("   - password_reset_expires_at: " . ($user->password_reset_expires_at ? 'No se limpió' : 'Limpiado'));
        $this->line('');

        $this->info("8. Probando autenticación...");
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
        $this->info("=== PRUEBA COMPLETADA EXITOSAMENTE ===");
        $this->info("El flujo de restablecimiento de contraseña funciona correctamente.");

        return 0;
    }
}