<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserNotification;
use App\Models\User;

class TestMailConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-mail-configuration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba diferentes configuraciones de correo (sendmail, mail, log)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Probando configuraciones de correo...');
        
        // Crear un usuario de prueba
        $testUser = new User();
        $testUser->id = User::findNextAvailableId();
        $testUser->first_name = 'Usuario';
        $testUser->last_name = 'Prueba';
        $testUser->user = 'test@example.com';
        $testUser->document_id = '123456789';
        $testUser->city = 'Bogotá';
        $testUser->registration_type = 'natural';
        $testUser->economic_sector = 'Tecnología';
        $testUser->data_authorization = true;
        $testUser->role = 0;
        $testUser->status_users_id = 2;
        
        // Generar URL de activación de prueba
        $token = \Illuminate\Support\Facades\Hash::make($testUser->user);
        $activationUrl = route('user.activation', [
            'userId' => $testUser->id,
            'token' => $token
        ]);
        
        $this->info('Usuario de prueba creado: ' . $testUser->first_name . ' ' . $testUser->last_name);
        $this->info('URL de activación: ' . $activationUrl);
        
        // Probar diferentes configuraciones de correo
        $mailers = ['sendmail', 'mail', 'log', 'array'];
        
        foreach ($mailers as $mailer) {
            $this->info("\n📧 Probando mailer: " . strtoupper($mailer));
            
            try {
                // Limpiar el array de correos si existe
                if ($mailer === 'array') {
                    // No usar flush() ya que no existe en esta versión
                }
                
                // Intentar enviar correo
                Mail::mailer($mailer)->to('admin@prospectiva.com')->send(new NewUserNotification($testUser, $activationUrl));
                
                $this->info('✅ Correo enviado exitosamente usando ' . $mailer . '!');
                
                // Si es array, mostrar el correo capturado
                if ($mailer === 'array') {
                    $this->info('📨 Correo capturado en memoria (array)');
                }
                
            } catch (\Exception $e) {
                $this->error('❌ Error con ' . $mailer . ': ' . $e->getMessage());
            }
        }
        
        $this->info("\n🎯 Configuración actual por defecto: " . config('mail.default'));
        $this->info("📝 Los correos enviados con 'log' se guardan en: storage/logs/laravel.log");
        $this->info("💡 Para producción, configura MAIL_MAILER=sendmail en tu archivo .env");
    }
}
