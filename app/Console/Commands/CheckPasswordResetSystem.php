<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckPasswordResetSystem extends Command
{
    protected $signature = 'check:password-reset-system';
    protected $description = 'Verificar el estado del sistema de restablecimiento de contraseñas';

    public function handle()
    {
        $this->info("=== VERIFICACIÓN DEL SISTEMA DE RESTABLECIMIENTO DE CONTRASEÑAS ===");
        $this->line('');

        // Verificar estructura de la base de datos
        $this->info("1. Verificando estructura de la base de datos...");
        
        $hasTokenColumn = Schema::hasColumn('users', 'password_reset_token');
        $hasExpiresColumn = Schema::hasColumn('users', 'password_reset_expires_at');
        
        if ($hasTokenColumn && $hasExpiresColumn) {
            $this->info("✅ Campos de restablecimiento presentes en la tabla users");
        } else {
            $this->error("❌ Faltan campos de restablecimiento en la tabla users");
            if (!$hasTokenColumn) $this->error("   - Falta: password_reset_token");
            if (!$hasExpiresColumn) $this->error("   - Falta: password_reset_expires_at");
            $this->info("Solución: Ejecutar php artisan migrate");
            return 1;
        }
        $this->line('');

        // Verificar configuración de correo
        $this->info("2. Verificando configuración de correo...");
        $mailConfig = config('mail');
        $this->info("   - Mailer por defecto: " . $mailConfig['default']);
        $this->info("   - From address: " . $mailConfig['from']['address']);
        $this->info("   - From name: " . $mailConfig['from']['name']);
        
        if ($mailConfig['default'] === 'log') {
            $this->warn("⚠️  El mailer está configurado como 'log'");
            $this->info("   Los correos se guardarán en storage/logs/laravel.log");
        } elseif ($mailConfig['default'] === 'smtp') {
            $this->info("✅ Mailer configurado como SMTP");
        } else {
            $this->warn("⚠️  Mailer configurado como: " . $mailConfig['default']);
        }
        $this->line('');

        // Verificar rutas
        $this->info("3. Verificando rutas...");
        $routes = [
            'login_restore_password' => route('login_restore_password'),
            'password.email' => route('password.email'),
            'password.reset.form' => route('password.reset.form', ['token' => 'test', 'email' => 'test@example.com']),
            'password.reset' => route('password.reset'),
        ];

        foreach ($routes as $name => $route) {
            $this->info("   - {$name}: {$route}");
        }
        $this->line('');

        // Verificar controlador
        $this->info("4. Verificando controlador...");
        if (class_exists('App\Http\Controllers\PasswordResetController')) {
            $this->info("✅ Controlador PasswordResetController existe");
            
            $methods = ['showResetForm', 'sendResetLink', 'showResetPasswordForm', 'resetPassword'];
            foreach ($methods as $method) {
                if (method_exists('App\Http\Controllers\PasswordResetController', $method)) {
                    $this->info("   ✅ Método {$method} existe");
                } else {
                    $this->error("   ❌ Método {$method} no existe");
                }
            }
        } else {
            $this->error("❌ Controlador PasswordResetController no existe");
            return 1;
        }
        $this->line('');

        // Verificar clase Mail
        $this->info("5. Verificando clase Mail...");
        if (class_exists('App\Mail\PasswordResetMail')) {
            $this->info("✅ Clase PasswordResetMail existe");
        } else {
            $this->error("❌ Clase PasswordResetMail no existe");
            return 1;
        }
        $this->line('');

        // Verificar vistas
        $this->info("6. Verificando vistas...");
        $views = [
            'login.restore-password' => resource_path('views/login/restore-password.blade.php'),
            'login.reset-password' => resource_path('views/login/reset-password.blade.php'),
            'emails.password-reset' => resource_path('views/emails/password-reset.blade.php'),
        ];

        foreach ($views as $name => $path) {
            if (file_exists($path)) {
                $this->info("   ✅ Vista {$name} existe");
            } else {
                $this->error("   ❌ Vista {$name} no existe");
            }
        }
        $this->line('');

        // Verificar usuarios de prueba
        $this->info("7. Verificando usuarios disponibles...");
        $userCount = User::count();
        $this->info("   - Total de usuarios: {$userCount}");
        
        if ($userCount > 0) {
            $sampleUser = User::first();
            $this->info("   - Usuario de ejemplo: {$sampleUser->user}");
            $this->info("   - Nombre: {$sampleUser->first_name} {$sampleUser->last_name}");
        } else {
            $this->warn("⚠️  No hay usuarios en la base de datos");
            $this->info("Solución: Crear usuarios o ejecutar seeders");
        }
        $this->line('');

        // Verificar permisos de directorios
        $this->info("8. Verificando permisos de directorios...");
        $directories = [
            'storage/logs' => storage_path('logs'),
            'storage/framework/views' => storage_path('framework/views'),
        ];

        foreach ($directories as $name => $path) {
            if (is_dir($path) && is_writable($path)) {
                $this->info("   ✅ Directorio {$name} es escribible");
            } else {
                $this->error("   ❌ Directorio {$name} no es escribible");
            }
        }
        $this->line('');

        $this->info("🎉 VERIFICACIÓN COMPLETADA");
        $this->info("El sistema de restablecimiento de contraseñas está listo para usar.");
        $this->line('');
        $this->info("Para probar el sistema, ejecuta:");
        $this->info("php artisan test:password-reset-system correo@ejemplo.com nueva-contraseña");
        
        return 0;
    }
}



