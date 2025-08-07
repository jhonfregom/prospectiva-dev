<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TestNotesFrontend extends Command
{
    protected $signature = 'notes:test-frontend {user_id}';
    protected $description = 'Simular exactamente lo que hace el frontend';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info("🧪 Simulando frontend para usuario ID: {$userId}");

        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ Usuario con ID {$userId} no encontrado");
            return 1;
        }
        
        $this->info("👤 Usuario: {$user->user} (ID: {$user->id})");

        Auth::login($user);

        $csrfToken = csrf_token();
        $this->info("🔑 CSRF Token: " . substr($csrfToken, 0, 20) . "...");

        $this->info("\n📝 Simulando GET /notes (como frontend)");
        try {
            $response = Http::withHeaders([
                'X-CSRF-TOKEN' => $csrfToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache'
            ])->get('http://localhost:8000/api/notes');
            
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

        $this->info("\n📝 Simulando POST /notes (como frontend)");
        try {
            $noteData = [
                'title' => 'Nota de prueba frontend - ' . now()->format('H:i:s'),
                'content' => 'Contenido de prueba creado simulando frontend el ' . now()->format('Y-m-d H:i:s')
            ];
            
            $response = Http::withHeaders([
                'X-CSRF-TOKEN' => $csrfToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post('http://localhost:8000/api/notes', $noteData);
            
            $this->info("Status: " . $response->status());
            $this->info("Response: " . $response->body());
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->info("✅ Nota creada con ID: " . $data['data']['id']);

                    $note = \App\Models\Note::find($data['data']['id']);
                    if ($note) {
                        $this->info("✅ Nota encontrada en BD: {$note->title}");

                        $this->info("\n📝 Probando GET /notes después de crear");
                        $response2 = Http::withHeaders([
                            'X-CSRF-TOKEN' => $csrfToken,
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Cache-Control' => 'no-cache',
                            'Pragma' => 'no-cache'
                        ])->get('http://localhost:8000/api/notes');
                        
                        if ($response2->successful()) {
                            $data2 = $response2->json();
                            if ($data2['success']) {
                                $this->info("✅ Notas después de crear: " . count($data2['data']));
                                foreach ($data2['data'] as $note2) {
                                    $this->line("   - ID: {$note2['id']} | Título: '{$note2['title']}' | User ID: {$note2['user_id']}");
                                }
                            }
                        }

                        $this->info("\n📊 Verificando directamente en la BD:");
                        $notesInDB = \App\Models\Note::where('user_id', $user->id)->get();
                        $this->info("   Notas del usuario en BD: " . $notesInDB->count());
                        foreach ($notesInDB as $noteDB) {
                            $this->line("   - ID: {$noteDB->id} | Título: '{$noteDB->title}' | User ID: {$noteDB->user_id}");
                        }

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