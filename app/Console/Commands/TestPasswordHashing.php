<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TestPasswordHashing extends Command
{
    protected $signature = 'test:password-hashing {email} {password}';
    protected $description = 'Probar el hashing de contraseñas y autenticación';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("=== PRUEBA DE HASHING DE CONTRASEÑA ===");
        $this->info("Email: {$email}");
        $this->info("Contraseña: {$password}");
        $this->line('');

        $user = User::where('user', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado: {$email}");
            return 1;
        }

        $this->info("✅ Usuario encontrado: ID {$user->id}");
        $this->line('');

        $this->info("=== VERIFICACIÓN DE CONTRASEÑA ACTUAL ===");
        $this->info("Hash actual: " . substr($user->password, 0, 50) . "...");
        $this->info("Longitud del hash: " . strlen($user->password));
        
        if (Hash::check($password, $user->password)) {
            $this->info("✅ La contraseña actual coincide");
        } else {
            $this->warn("⚠️  La contraseña actual NO coincide");
        }
        $this->line('');

        $this->info("=== PRUEBA DE AUTENTICACIÓN ACTUAL ===");
        $credentials = [
            'user' => $email,
            'password' => $password
        ];

        if (Auth::attempt($credentials)) {
            $this->info("✅ Autenticación exitosa con contraseña actual");
            Auth::logout();
        } else {
            $this->error("❌ Error en autenticación con contraseña actual");
        }
        $this->line('');

        $this->info("=== GENERANDO NUEVO HASH ===");
        $newHash = Hash::make($password);
        $this->info("Nuevo hash: " . substr($newHash, 0, 50) . "...");
        $this->info("Longitud del nuevo hash: " . strlen($newHash));
        $this->line('');

        $this->info("=== VERIFICANDO NUEVO HASH ===");
        if (Hash::check($password, $newHash)) {
            $this->info("✅ El nuevo hash es válido");
        } else {
            $this->error("❌ El nuevo hash NO es válido");
            return 1;
        }
        $this->line('');

        $this->info("=== ACTUALIZANDO CONTRASEÑA EN BD ===");
        $oldHash = $user->password;
        
        $user->update([
            'password' => $newHash
        ]);

        $this->info("✅ Contraseña actualizada en la base de datos");
        $this->info("Hash anterior: " . substr($oldHash, 0, 50) . "...");
        $this->info("Hash nuevo: " . substr($user->password, 0, 50) . "...");
        $this->line('');

        $this->info("=== VERIFICANDO CONTRASEÑA ACTUALIZADA ===");
        if (Hash::check($password, $user->password)) {
            $this->info("✅ La contraseña actualizada es válida");
        } else {
            $this->error("❌ La contraseña actualizada NO es válida");
            return 1;
        }
        $this->line('');

        $this->info("=== PRUEBA DE AUTENTICACIÓN CON CONTRASEÑA ACTUALIZADA ===");
        if (Auth::attempt($credentials)) {
            $this->info("✅ Autenticación exitosa con contraseña actualizada");
            Auth::logout();
        } else {
            $this->error("❌ Error en autenticación con contraseña actualizada");
            return 1;
        }
        $this->line('');

        $this->info("=== CONFIGURACIÓN DE AUTENTICACIÓN ===");
        $this->info("Driver de autenticación: " . config('auth.defaults.guard'));
        $this->info("Proveedor de usuarios: " . config('auth.guards.web.provider'));
        $this->info("Modelo de usuario: " . config('auth.providers.users.model'));
        $this->line('');

        $this->info("=== PRUEBA COMPLETADA EXITOSAMENTE ===");
        $this->info("El hashing y autenticación de contraseñas funciona correctamente.");

        return 0;
    }
}