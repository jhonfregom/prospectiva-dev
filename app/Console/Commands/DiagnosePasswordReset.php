<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DiagnosePasswordReset extends Command
{
    protected $signature = 'diagnose:password-reset {email}';
    protected $description = 'Diagnosticar problemas con el restablecimiento de contraseñas';

    public function handle()
    {
        $email = $this->argument('email');

        $this->info("=== DIAGNÓSTICO DE RESTABLECIMIENTO DE CONTRASEÑA ===");
        $this->info("Email: {$email}");
        $this->line('');

        $user = User::where('user', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado: {$email}");
            $this->info("Solución: Verificar que el email esté registrado en el sistema");
            return 1;
        }

        $this->info("✅ Usuario encontrado: ID {$user->id}");
        $this->info("   - Email: {$user->user}");
        $this->info("   - Nombre: {$user->first_name} {$user->last_name}");
        $this->line('');

        $this->info("=== VERIFICACIÓN DE CAMPOS ===");
        $this->info("password_reset_token: " . ($user->password_reset_token ? 'Sí' : 'No'));
        $this->info("password_reset_expires_at: " . ($user->password_reset_expires_at ? $user->password_reset_expires_at : 'No'));
        
        if ($user->password_reset_expires_at && $user->password_reset_expires_at < now()) {
            $this->warn("⚠️  Token expirado: " . $user->password_reset_expires_at);
        }
        $this->line('');

        $this->info("=== VERIFICACIÓN DE CONFIGURACIÓN ===");
        $this->info("Tabla de tokens configurada: " . config('auth.passwords.users.table'));
        $this->info("Proveedor configurado: " . config('auth.passwords.users.provider'));
        $this->line('');

        $this->info("=== VERIFICACIÓN DE RUTAS ===");
        $this->info("Ruta de envío de email: " . route('password.email'));
        $this->info("Ruta de restablecimiento: " . route('password.reset'));
        $this->line('');

        $this->info("=== GENERANDO NUEVO TOKEN ===");
        $token = Str::random(64);
        
        $user->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(1),
        ]);

        $this->info("✅ Nuevo token generado: {$token}");
        $this->info("✅ Expira: " . now()->addHours(1));
        $this->line('');

        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $email]);
        $this->info("=== URL DE RESTABLECIMIENTO ===");
        $this->info("URL generada: {$resetUrl}");
        $this->line('');

        $this->info("=== RECOMENDACIONES ===");
        $this->info("1. Verificar que el correo se esté enviando correctamente");
        $this->info("2. Verificar que el enlace en el correo sea válido");
        $this->info("3. Verificar que no haya problemas de CSRF");
        $this->info("4. Verificar los logs de Laravel para errores específicos");
        $this->line('');

        $this->info("=== PRÓXIMOS PASOS ===");
        $this->info("1. Ejecutar: php artisan test:password-reset {$email} nueva123456");
        $this->info("2. Revisar logs: tail -f storage/logs/laravel.log");
        $this->info("3. Probar el enlace de restablecimiento manualmente");

        return 0;
    }
}



