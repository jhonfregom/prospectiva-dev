<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewUserNotification;
use App\Models\User;

class TestEmailSending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email-sending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el envío de correos usando sendmail o mail()';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Probando envío de correos...');
        
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
        
        // Obtener administradores
        $admins = User::where('role', 1)->get();
        
        if ($admins->isEmpty()) {
            $this->warn('No hay administradores registrados. Creando uno de prueba...');
            
            // Crear un administrador de prueba
            $admin = new User();
            $admin->id = User::findNextAvailableId();
            $admin->first_name = 'Admin';
            $admin->last_name = 'Prueba';
            $admin->user = 'admin@prospectiva.com';
            $admin->document_id = '987654321';
            $admin->city = 'Bogotá';
            $admin->registration_type = 'natural';
            $admin->economic_sector = 'Tecnología';
            $admin->data_authorization = true;
            $admin->role = 1;
            $admin->status_users_id = 1;
            $admin->save();
            
            $this->info('Administrador de prueba creado: ' . $admin->user);
        }
        
        // Intentar enviar correo usando la configuración actual
        try {
            $this->info('Intentando enviar correo usando ' . config('mail.default') . '...');
            Mail::to('admin@prospectiva.com')->send(new NewUserNotification($testUser, $activationUrl));
            $this->info('✅ Correo procesado exitosamente usando ' . config('mail.default') . '!');
            
            if (config('mail.default') === 'log') {
                $this->info('📧 El correo se ha guardado en el log. Revisa: storage/logs/laravel.log');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error enviando correo: ' . $e->getMessage());
            $this->info('Configuración actual: ' . config('mail.default'));
        }
    }
}
