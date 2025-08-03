<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TestNotesAPICSRF extends Command
{
    protected $signature = 'notes:test-api-csrf';
    protected $description = 'Probar la API de notas con CSRF token';

    public function handle()
    {
        $this->info('🧪 Probando API de notas con CSRF token...');

        // Probar con usuario 1
        $user = User::find(1);
        Auth::login($user);
        
        $this->info("👤 Usuario: {$user->user} (ID: {$user->id})");
        
        // Obtener el token CSRF
        $csrfToken = csrf_token();
        $this->info("🔑 CSRF Token: " . substr($csrfToken, 0, 20) . "...");
        
        // Simular petición POST para crear nota con CSRF
        $this->info("\n📝 Probando POST /notes con CSRF");
        try {
            $noteData = [
                'title' => 'Nota de prueba CSRF - ' . now()->format('H:i:s'),
                'content' => 'Contenido de prueba creado vía API con CSRF el ' . now()->format('Y-m-d H:i:s'),
                '_token' => $csrfToken
            ];
            
            $response = Http::withHeaders([
                'X-CSRF-TOKEN' => $csrfToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post('http://127.0.0.1:8000/notes', $noteData);
            
            $this->info("Status: " . $response->status());
            $this->info("Response: " . $response->body());
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->info("✅ Nota creada con ID: " . $data['data']['id']);
                    
                    // Verificar que la nota se creó en la BD
                    $note = \App\Models\Note::find($data['data']['id']);
                    if ($note) {
                        $this->info("✅ Nota encontrada en BD: {$note->title}");
                        
                        // Eliminar la nota de prueba
                        $note->delete();
                        $this->info("🗑️ Nota de prueba eliminada");
                    } else {
                        $this->error("❌ Nota no encontrada en BD");
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Error POST: " . $e->getMessage());
        }

        Auth::logout();
        return 0;
    }
} 