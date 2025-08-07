<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Mail\UserRegistrationNotification;
use Illuminate\Support\Facades\Mail;

class TestEmailSystem extends Command
{
    protected $signature = 'test:email-system';
    protected $description = 'Probar el sistema de emails enviando una notificación de prueba';

    public function handle()
    {
        $this->info('🧪 Probando sistema de emails...');

        $this->info('📧 Configuración de email:');
        $this->line('   MAIL_MAILER: ' . config('mail.default'));
        $this->line('   MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('   MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('   MAIL_USERNAME: ' . config('mail.mailers.smtp.username'));
        $this->line('   MAIL_FROM_ADDRESS: ' . config('mail.from.address'));

        $admins = User::where('role', 1)->get();
        $this->info("\n👥 Administradores encontrados: " . $admins->count());
        
        if ($admins->count() === 0) {
            $this->error('❌ No hay administradores (role = 1) en el sistema');
            return 1;
        }
        
        foreach ($admins as $admin) {
            $this->line("   - {$admin->first_name} {$admin->last_name} ({$admin->user})");
        }

        $testUser = User::where('role', 0)->first();
        
        if (!$testUser) {
            $this->error('❌ No hay usuarios de prueba (role = 0) en el sistema');
            return 1;
        }
        
        $this->info("\n📤 Enviando email de prueba...");
        $this->line("   Usuario de prueba: {$testUser->first_name} {$testUser->last_name}");
        
        try {
            
            foreach ($admins as $admin) {
                Mail::to($admin->user)->send(new UserRegistrationNotification($testUser));
                $this->line("   ✅ Email enviado a: {$admin->user}");
            }
            
            $this->info("\n🎉 Sistema de emails funcionando correctamente!");
            $this->info("   Se enviaron " . $admins->count() . " emails de prueba");
            
        } catch (\Exception $e) {
            $this->error("\n❌ Error enviando emails: " . $e->getMessage());
            $this->line("   Verifica la configuración de Gmail SMTP");
            return 1;
        }
        
        return 0;
    }
}