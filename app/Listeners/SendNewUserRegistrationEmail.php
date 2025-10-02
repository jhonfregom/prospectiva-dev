<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\NewUserRegistrationMail;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendNewUserRegistrationEmail
{
    use InteractsWithQueue;
    
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        $listenerId = uniqid();
        \Log::info('SendNewUserRegistrationEmail listener ejecutándose', [
            'listener_id' => $listenerId,
            'new_user_id' => $event->user->id,
            'new_user_email' => $event->user->user,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Verificar si ya se procesó este usuario (evitar duplicados)
        if ($event->user->activation_token) {
            \Log::info('Usuario ya tiene token de activación, saltando procesamiento', [
                'user_id' => $event->user->id,
                'existing_token' => $event->user->activation_token
            ]);
            return;
        }

        // Obtener todos los administradores (role = 1)
        $admins = User::where('role', 1)->get();
        
        \Log::info('Administradores encontrados: ' . $admins->count());
        
        // Generar el token UNA SOLA VEZ
        $activationToken = \Illuminate\Support\Str::random(64);
        // Construir URL usando las rutas de Laravel (respeta APP_URL del .env)
        $activationUrl = route('user.activation.show', [
            'userId' => $event->user->id,
            'token' => $activationToken,
        ]);
        
        // Guardar el token en el usuario
        $event->user->update([
            'activation_token' => $activationToken,
            'activation_token_expires_at' => now()->addHours(24)
        ]);
        
        \Log::info('Token generado y guardado: ' . $activationToken);
        
        // Enviar UN SOLO EMAIL a cada administrador con el MISMO TOKEN
        foreach ($admins as $admin) {
            try {
                // Crear el correo con el token ya generado
                $mail = new NewUserRegistrationMail($event->user, $activationToken, $activationUrl);
                
                Mail::to($admin->user)->send($mail);
                
                \Log::info('Email enviado a: ' . $admin->user . ' con URL: ' . $activationUrl);
            } catch (\Exception $e) {
                \Log::error('Error enviando email a ' . $admin->user . ': ' . $e->getMessage());
            }
        }
    }
}
