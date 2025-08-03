<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TestNotesAPIReal extends Command
{
    protected $signature = 'notes:test-api-real {user_id}';
    protected $description = 'Probar la API de notas de forma realista';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info("🧪 Probando API de notas de forma realista para usuario ID: {$userId}");
        
        // Buscar el usuario
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ Usuario con ID {$userId} no encontrado");
            return 1;
        }
        
        $this->info("👤 Usuario: {$user->user} (ID: {$user->id})");
        
        // Simular login del usuario
        Auth::login($user);
        
        // Obtener el token CSRF
        $csrfToken = csrf_token();
        $this->info("🔑 CSRF Token: " . substr($csrfToken, 0, 20) . "...");
        
        // Simular petición GET para obtener notas
        $this->info("\n📝 Probando GET /notes");
        try {
            $response = Http::withHeaders([
                'X-CSRF-TOKEN' => $csrfToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->get('http://127.0.0.1:8000/notes');
            
            $this->info("Status: " . $response->status());
            $this->info("Response: " . $response->body());
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->info("✅ Notas obtenidas: " . count($data['data']));
                    foreach ($data['data'] as $note) {
                        $this->line("   - ID: {$note['id']} | Título: '{$note['title']}' | User ID: {$note['user_id']}");
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("Error GET: " . $e->getMessage());
        }

        // Simular petición POST para crear nota
        $this->info("\n📝 Probando POST /notes");
        try {
            $noteData = [
                'title' => 'Nota de prueba real - ' . now()->format('H:i:s'),
                'content' => 'Contenido de prueba creado vía API real el ' . now()->format('Y-m-d H:i:s'),
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
                        
                        // Probar GET nuevamente para ver si aparece
                        $this->info("\n📝 Probando GET /notes después de crear");
                        $response2 = Http::withHeaders([
                            'X-CSRF-TOKEN' => $csrfToken,
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json'
                        ])->get('http://127.0.0.1:8000/notes');
                        
                        if ($response2->successful()) {
                            $data2 = $response2->json();
                            if ($data2['success']) {
                                $this->info("✅ Notas después de crear: " . count($data2['data']));
                                foreach ($data2['data'] as $note2) {
                                    $this->line("   - ID: {$note2['id']} | Título: '{$note2['title']}' | User ID: {$note2['user_id']}");
                                }
                            }
                        }
                        
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