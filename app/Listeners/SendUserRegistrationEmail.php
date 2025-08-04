<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\UserRegistrationNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendUserRegistrationEmail
{
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
        // Obtener todos los administradores (role = 1)
        $admins = User::where('role', 1)->get();
        
        // Enviar email a cada administrador
        foreach ($admins as $admin) {
            Mail::to($admin->user)->send(new UserRegistrationNotification($event->user));
        }
    }
}
