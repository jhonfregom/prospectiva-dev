<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\NewUserRegistrationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TestLocalEmail extends Command
{
    protected $signature = 'test:local-email {email}';
    protected $description = 'Probar envío de email con configuración local';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("=== PRUEBA DE EMAIL CON CONFIGURACIÓN LOCAL ===");
        $this->info("Email de prueba: {$email}");
        $this->line('');
        
        // Cambiar temporalmente la configuración a local
        config(['app.url' => 'http://localhost:8000']);
        
        $this->info("=== CONFIGURACIÓN TEMPORAL ===");
        $this->info("APP_URL: " . config('app.url'));
        $this->info("Current URL: " . url('/'));
        $this->line('');
        
        // Crear un usuario de prueba
        $testUser = new User();
        $testUser->id = 999997;
        $testUser->first_name = 'Usuario';
        $testUser->last_name = 'Local Test';
        $testUser->user = $email;
        $testUser->document_id = '6543210987';
        $testUser->role = 0;
        $testUser->status_users_id = 2;
        $testUser->created_at = now();
        
        $this->info("=== ENVIANDO EMAIL CON URL LOCAL ===");
        
        try {
            Mail::to($email)->send(new NewUserRegistrationMail($testUser));
            $this->info("✅ Email enviado exitosamente con configuración local");
            $this->info("📧 Verifica tu bandeja de entrada");
            $this->info("🔗 El enlace de activación debería apuntar a localhost:8000");
        } catch (\Exception $e) {
            $this->error("❌ Error enviando email: " . $e->getMessage());
        }
        
        // Restaurar configuración original
        config(['app.url' => 'http://prospectiva.com']);
        
        $this->line('');
        $this->info("💡 Para usar localmente permanentemente:");
        $this->info("   1. Cambia APP_URL en .env a: APP_URL=http://localhost:8000");
        $this->info("   2. Ejecuta: php artisan config:cache");
    }
}




