<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class TestNotesAPI extends Command
{
    protected $signature = 'notes:test-api';
    protected $description = 'Probar la API de notas simulando el frontend';

    public function handle()
    {
        $this->info('🧪 Probando API de notas simulando frontend...');

        $user = User::find(1);
        Auth::login($user);
        
        $this->info("👤 Usuario: {$user->user} (ID: {$user->id})");

        $this->info("\n📝 Probando GET /notes");
        try {
            $response = Http::get('http:
            $this->info("Status: " . $response->status());
            $this->info("Response: " . $response->body());
        } catch (\Exception $e) {
            $this->error("Error GET: " . $e->getMessage());
        }

        $this->info("\n📝 Probando POST /notes");
        try {
            $noteData = [
                'title' => 'Nota de prueba API - ' . now()->format('H:i:s'),
                'content' => 'Contenido de prueba creado vía API el ' . now()->format('Y-m-d H:i:s')
            ];
            
            $response = Http::post('http:
            $this->info("Status: " . $response->status());
            $this->info("Response: " . $response->body());
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['success']) {
                    $this->info("✅ Nota creada con ID: " . $data['data']['id']);

                    $note = \App\Models\Note::find($data['data']['id']);
                    if ($note) {
                        $this->info("✅ Nota encontrada en BD: {$note->title}");
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