<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetNotification;
use App\Models\User;
use Illuminate\Support\Str;

class CheckEmailConfiguration extends Command
{
    protected $signature = 'check:email-config {email}';
    protected $description = 'Verificar la configuración de correo y enviar un correo de prueba';

    public function handle()
    {
        $email = $this->argument('email');

        $this->info("=== VERIFICACIÓN DE CONFIGURACIÓN DE CORREO ===");
        $this->info("Email de prueba: {$email}");
        $this->line('');

        $this->info("=== CONFIGURACIÓN DE CORREO ===");
        $this->info("Driver: " . config('mail.default'));
        $this->info("Host: " . config('mail.mailers.smtp.host'));
        $this->info("Puerto: " . config('mail.mailers.smtp.port'));
        $this->info("Usuario: " . config('mail.mailers.smtp.username'));
        $this->info("Encriptación: " . config('mail.mailers.smtp.encryption'));
        $this->info("From Address: " . config('mail.from.address'));
        $this->info("From Name: " . config('mail.from.name'));
        $this->line('');

        $user = User::where('user', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado: {$email}");
            return 1;
        }

        $token = Str::random(64);
        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $email]);
        
        $this->info("=== URL DE RESTABLECIMIENTO ===");
        $this->info("URL generada: {$resetUrl}");
        $this->line('');

        $this->info("=== ENVIANDO CORREO DE PRUEBA ===");
        
        try {
            Mail::to($email)->send(new PasswordResetNotification($resetUrl, $email));
            $this->info("✅ Correo enviado exitosamente");
            $this->info("Verifica tu bandeja de entrada y spam");
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar correo: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }

        $this->line('');
        $this->info("=== INSTRUCCIONES ===");
        $this->info("1. Verifica tu bandeja de entrada");
        $this->info("2. Verifica la carpeta de spam");
        $this->info("3. Haz clic en el enlace del correo");
        $this->info("4. Intenta cambiar la contraseña");
        $this->info("5. Si hay errores, revisa los logs de Laravel");

        return 0;
    }
}



