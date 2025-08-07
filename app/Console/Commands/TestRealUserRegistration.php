<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\RegisterController;
use Illuminate\Http\Request;

class TestRealUserRegistration extends Command
{
    protected $signature = 'test:real-user-registration';
    protected $description = 'Simular el registro de un usuario real usando el RegisterController';

    public function handle()
    {
        $this->info("=== PRUEBA DE REGISTRO REAL DE USUARIO ===");
        
        // Crear una request simulada para persona natural
        $request = new Request([
            'registration_type' => 'natural',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'document_id' => '5432109876',
            'user' => 'juan.perez.test5@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123',
            'city' => 'Bogotá',
            'data_authorization' => true
        ]);
        
        $this->info("Datos de registro:");
        $this->info("Tipo: " . $request->registration_type);
        $this->info("Nombre: " . $request->first_name . " " . $request->last_name);
        $this->info("Documento: " . $request->document_id);
        $this->info("Email: " . $request->user);
        $this->line('');
        
        try {
            $controller = new RegisterController();
            $response = $controller->register($request);
            
            $this->info("✅ Registro completado");
            $this->info("Respuesta: " . json_encode($response->getData()));
            
        } catch (\Exception $e) {
            $this->error("❌ Error en registro: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
        
        $this->line('');
        $this->info("📋 Revisa los logs en storage/logs/laravel.log para ver el flujo completo");
    }
}
