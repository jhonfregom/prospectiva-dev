<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\PasswordResetNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TestEmailSending extends Command
{
    protected $signature = 'test:email-sending {email}';
    protected $description = 'Probar el envío de correos de restablecimiento de contraseña';

    public function handle()
    {
        $email = $this->argument('email');

        $this->info("=== PRUEBA DE ENVÍO DE CORREO ===");
        $this->info("Email: {$email}");
        $this->line('');

        $user = User::where('user', $email)->first();
        
        if (!$user) {
            $this->error("❌ Usuario no encontrado: {$email}");
            return 1;
        }

        $this->info("✅ Usuario encontrado: ID {$user->id}");
        $this->line('');

        $token = Str::random(64);
        
        $user->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(1),
        ]);

        $this->info("✅ Token generado: {$token}");
        $this->line('');

        $resetUrl = route('password.reset.form', ['token' => $token, 'email' => $email]);
        $this->info("URL de restablecimiento: {$resetUrl}");
        $this->line('');

        $this->info("Enviando correo de prueba...");
        
        try {
            Mail::to($email)->send(new PasswordResetNotification($resetUrl, $email));
            $this->info("✅ Correo enviado exitosamente");
        } catch (\Exception $e) {
            $this->error("❌ Error al enviar correo: " . $e->getMessage());
            return 1;
        }

        $this->line('');
        $this->info("=== VERIFICACIÓN DE CONFIGURACIÓN DE CORREO ===");
        $this->info("Driver: " . config('mail.default'));
        $this->info("Host: " . config('mail.mailers.smtp.host'));
        $this->info("Puerto: " . config('mail.mailers.smtp.port'));
        $this->info("Usuario: " . config('mail.mailers.smtp.username'));
        $this->info("Encriptación: " . config('mail.mailers.smtp.encryption'));

        return 0;
    }
}