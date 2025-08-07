<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\PasswordResetMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class TestPasswordResetSystem extends Command
{
    protected $signature = 'test:password-reset-system {email} {new_password}';
    protected $description = 'Probar el sistema completo de restablecimiento de contraseñas';

    public function handle()
    {
        $email = $this->argument('email');
        $newPassword = $this->argument('new_password');

        $this->info("=== PRUEBA DEL SISTEMA DE RESTABLECIMIENTO DE CONTRASEÑAS ===");
        $this->info("Email: {$email}");
        $this->info("Nueva contraseña: {$newPassword}");
        $this->line('');

        // Paso 1: Verificar que el usuario existe
        $this->info("1. Verificando existencia del usuario...");
        $user = User::where('user', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado: {$email}");
            $this->info("Solución: Verificar que el email esté registrado en la base de datos");
            return 1;
        }

        $this->info("✅ Usuario encontrado: ID {$user->id}");
        $this->info("   - Email: {$user->user}");
        $this->info("   - Nombre: {$user->first_name} {$user->last_name}");
        $this->line('');

        // Paso 2: Verificar configuración de correo
        $this->info("2. Verificando configuración de correo...");
        $mailConfig = config('mail');
        $this->info("   - Mailer por defecto: " . $mailConfig['default']);
        $this->info("   - From address: " . $mailConfig['from']['address']);
        $this->info("   - From name: " . $mailConfig['from']['name']);
        
        if ($mailConfig['default'] === 'log') {
            $this->warn("⚠️  El mailer está configurado como 'log'. Los correos se guardarán en storage/logs/laravel.log");
        }
        $this->line('');

        // Paso 3: Generar token de restablecimiento
        $this->info("3. Generando token de restablecimiento...");
        $token = Str::random(64);
        
        $user->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(1),
        ]);

        $this->info("✅ Token generado: {$token}");
        $this->info("   - Expira: " . now()->addHours(1)->format('Y-m-d H:i:s'));
        $this->line('');

        // Paso 4: Generar URL de restablecimiento
        $this->info("4. Generando URL de restablecimiento...");
        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $email]);
        $this->info("URL: {$resetUrl}");
        $this->line('');

        // Paso 5: Probar envío de correo
        $this->info("5. Probando envío de correo...");
        try {
            $userName = $user->first_name ? $user->first_name . ' ' . $user->last_name : null;
            Mail::to($email)->send(new PasswordResetMail($resetUrl, $userName));
            
            if ($mailConfig['default'] === 'log') {
                $this->info("✅ Correo enviado (guardado en log)");
                $this->info("   Revisa: storage/logs/laravel.log");
            } else {
                $this->info("✅ Correo enviado exitosamente");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar correo: " . $e->getMessage());
            $this->info("Solución: Verificar configuración SMTP en .env");
            return 1;
        }
        $this->line('');

        // Paso 6: Simular restablecimiento de contraseña
        $this->info("6. Simulando restablecimiento de contraseña...");
        
        // Verificar token válido
        $validUser = User::where('user', $email)
                        ->where('password_reset_token', $token)
                        ->where('password_reset_expires_at', '>', now())
                        ->first();

        if (!$validUser) {
            $this->error("❌ Token no válido o expirado");
            return 1;
        }

        $this->info("✅ Token válido");
        
        // Actualizar contraseña
        $validUser->update([
            'password' => Hash::make($newPassword),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        $this->info("✅ Contraseña actualizada exitosamente");
        $this->line('');

        // Paso 7: Verificar que la contraseña se actualizó
        $this->info("7. Verificando actualización de contraseña...");
        $updatedUser = User::where('user', $email)->first();
        
        if (Hash::check($newPassword, $updatedUser->password)) {
            $this->info("✅ Contraseña verificada correctamente");
        } else {
            $this->error("❌ Error en la verificación de contraseña");
            return 1;
        }
        
        $this->info("   - password_reset_token: " . ($updatedUser->password_reset_token ? 'Sí' : 'No (limpiado)'));
        $this->info("   - password_reset_expires_at: " . ($updatedUser->password_reset_expires_at ? $updatedUser->password_reset_expires_at : 'No (limpiado)'));
        $this->line('');

        $this->info("🎉 ¡SISTEMA DE RESTABLECIMIENTO FUNCIONANDO CORRECTAMENTE!");
        $this->info("El usuario {$email} ahora puede iniciar sesión con la nueva contraseña.");
        
        return 0;
    }
}
