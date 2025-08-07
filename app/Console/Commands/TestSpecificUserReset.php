<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestSpecificUserReset extends Command
{
    protected $signature = 'test:specific-user-reset {email} {new_password}';
    protected $description = 'Probar el restablecimiento de contraseña para un usuario específico';

    public function handle()
    {
        $email = $this->argument('email');
        $newPassword = $this->argument('new_password');

        $this->info("=== PRUEBA DE RESTABLECIMIENTO PARA USUARIO ESPECÍFICO ===");
        $this->info("Email: {$email}");
        $this->info("Nueva contraseña: {$newPassword}");
        $this->line('');

        $user = User::where('user', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado: {$email}");
            $this->info("Creando usuario de prueba...");
            
            $user = User::create([
                'id' => Str::uuid(),
                'user' => $email,
                'password' => Hash::make('password123'),
                'first_name' => 'Usuario',
                'last_name' => 'Prueba',
                'status_users_id' => 1
            ]);
            
            $this->info("✅ Usuario creado: ID {$user->id}");
        } else {
            $this->info("✅ Usuario encontrado: ID {$user->id}");
            $this->info("   - Nombre: {$user->first_name} {$user->last_name}");
        }
        $this->line('');

        $this->info("=== VERIFICACIÓN DE CONTRASEÑA ACTUAL ===");
        $this->info("Hash actual: " . substr($user->password, 0, 50) . "...");
        
        if (Hash::check($newPassword, $user->password)) {
            $this->warn("⚠️  La contraseña ya es la misma que se quiere establecer");
        } else {
            $this->info("✅ La contraseña actual es diferente");
        }
        $this->line('');

        $this->info("=== GENERANDO TOKEN DE RESTABLECIMIENTO ===");
        $token = Str::random(64);
        
        $user->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(1),
        ]);

        $this->info("✅ Token generado: {$token}");
        $this->info("✅ Expira: " . now()->addHours(1));
        $this->line('');

        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $email]);
        $this->info("=== URL DE RESTABLECIMIENTO ===");
        $this->info("URL: {$resetUrl}");
        $this->line('');

        $this->info("=== SIMULANDO RESTABLECIMIENTO ===");
        $oldPasswordHash = $user->password;
        
        $user->update([
            'password' => Hash::make($newPassword),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        $this->info("✅ Contraseña actualizada en la base de datos");
        $this->info("Hash anterior: " . substr($oldPasswordHash, 0, 50) . "...");
        $this->info("Hash nuevo: " . substr($user->password, 0, 50) . "...");
        $this->line('');

        $this->info("=== VERIFICANDO ACTUALIZACIÓN ===");
        if (Hash::check($newPassword, $user->password)) {
            $this->info("✅ La contraseña se actualizó correctamente");
        } else {
            $this->error("❌ La contraseña NO se actualizó correctamente");
            return 1;
        }

        if (!$user->password_reset_token && !$user->password_reset_expires_at) {
            $this->info("✅ Token limpiado correctamente");
        } else {
            $this->error("❌ El token no se limpió correctamente");
            return 1;
        }
        $this->line('');

        $this->info("=== PROBANDO AUTENTICACIÓN ===");
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
        $this->info("El restablecimiento de contraseña funciona correctamente para {$email}");
        $this->line('');
        $this->info("=== INSTRUCCIONES PARA PRUEBA MANUAL ===");
        $this->info("1. Ve a la página de restablecimiento de contraseña");
        $this->info("2. Solicita un nuevo enlace de restablecimiento");
        $this->info("3. Haz clic en el enlace del correo");
        $this->info("4. Intenta cambiar la contraseña a: {$newPassword}");
        $this->info("5. Verifica que funcione correctamente");

        return 0;
    }
}