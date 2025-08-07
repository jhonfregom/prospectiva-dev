<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserRegistrationMail;
use App\Models\User;

class TestNewUserRegistrationEmail extends Command
{
    protected $signature = 'test:new-user-email {email} {--user-id=}';
    protected $description = 'Probar envío de email de nuevo usuario registrado';

    public function handle()
    {
        $email = $this->argument('email');
        $userId = $this->option('user-id');
        
        $this->info("=== PRUEBA DE EMAIL DE NUEVO USUARIO ===");
        $this->info("Email: {$email}");
        if ($userId) {
            $this->info("Usuario ID: {$userId}");
        }
        $this->line('');
        
        $user = null;
        
        if ($userId) {
            // Usar usuario existente
            $user = User::find($userId);
            if (!$user) {
                $this->error("❌ Usuario no encontrado con ID: {$userId}");
                return 1;
            }
            $this->info("✅ Usando usuario existente: {$user->first_name} {$user->last_name}");
        } else {
            // Crear usuario de prueba
            $user = new User();
            $user->id = 999999;
            $user->first_name = 'Usuario';
            $user->last_name = 'Test';
            $user->user = $email;
            $user->document_id = '1234567890';
            $user->role = 0;
            $user->status_users_id = 2;
            $user->created_at = now();
            $this->info("✅ Usando usuario de prueba");
        }
        
        $this->info("=== ENVIANDO EMAIL ===");
        
        try {
            Mail::to($email)->send(new NewUserRegistrationMail($user));
            $this->info("✅ Email enviado exitosamente");
            $this->info("📧 Verifica tu bandeja de entrada");
            
            // Mostrar información del token generado
            if ($userId) {
                $user->refresh(); // Recargar desde BD
                $this->info("🔑 Token generado: " . $user->activation_token);
                $this->info("🔗 URL de activación: http://localhost:8000/user-activation/{$user->id}/{$user->activation_token}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error enviando email: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
