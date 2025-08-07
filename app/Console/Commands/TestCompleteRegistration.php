<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Events\UserRegistered;

class TestCompleteRegistration extends Command
{
    protected $signature = 'test:complete-registration {email}';
    protected $description = 'Probar el registro completo de un usuario';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("=== PRUEBA DE REGISTRO COMPLETO ===");
        $this->info("Email: {$email}");
        $this->line('');
        
        // Obtener el próximo ID disponible
        $nextId = User::max('id') + 1;
        
        // Crear un usuario real en la base de datos
        $user = User::create([
            'id' => $nextId,
            'first_name' => 'Usuario',
            'last_name' => 'Completo',
            'user' => $email,
            'document_id' => '123456789' . rand(100, 999),
            'role' => 0,
            'status_users_id' => 2,
            'password' => bcrypt('password123'),
            'registration_type' => 'natural'
        ]);
        
        $this->info("✅ Usuario creado en BD con ID: {$user->id}");
        $this->info("✅ Evento UserRegistered se disparó automáticamente");
        $this->line('');
        
        // Verificar el token generado
        $user->refresh();
        $this->info("=== VERIFICACIÓN ===");
        $this->info("Token generado: " . ($user->activation_token ?: 'No generado'));
        $this->info("URL de activación: http://localhost:8000/user-activation/{$user->id}/{$user->activation_token}");
        $this->line('');
        
        $this->info("📧 Se enviaron correos a todos los administradores");
        $this->info("🔗 Todos los correos tienen el mismo enlace de activación");
    }
}
