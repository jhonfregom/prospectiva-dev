<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\UserRegistered;
use App\Models\User;

class TestUserRegistrationEvent extends Command
{
    protected $signature = 'test:user-registration-event {email}';
    protected $description = 'Probar el evento de registro de usuario completo';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("=== PRUEBA DE EVENTO DE REGISTRO DE USUARIO ===");
        $this->info("Email: {$email}");
        $this->line('');
        
        // Crear un usuario de prueba
        $testUser = new User();
        $testUser->id = 999998;
        $testUser->first_name = 'Usuario';
        $testUser->last_name = 'Event Test';
        $testUser->user = $email;
        $testUser->document_id = '1234567891';
        $testUser->role = 0;
        $testUser->status_users_id = 2;
        $testUser->created_at = now();
        
        $this->info("✅ Usuario de prueba creado");
        $this->info("ID: {$testUser->id}");
        $this->info("Nombre: {$testUser->first_name} {$testUser->last_name}");
        $this->info("Email: {$testUser->user}");
        $this->line('');
        
        $this->info("=== RESULTADO ===");
        $this->info("📧 Se deberían haber enviado correos a todos los administradores");
        $this->info("🔑 Todos los correos deberían tener el mismo token de activación");
        $this->info("💡 Verifica las bandejas de entrada de los administradores");
    }
}
