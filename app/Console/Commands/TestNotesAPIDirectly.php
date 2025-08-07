<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class TestNotesAPIDirectly extends Command
{
    protected $signature = 'notes:test-api-directly';
    protected $description = 'Probar la API de notas directamente para verificar el filtrado';

    public function handle()
    {
        $this->info('🧪 Probando API de notas directamente...');

        $users = User::whereIn('id', [1, 2, 3, 4])->get();
        
        foreach ($users as $user) {
            $this->line("\n👤 Probando con usuario: {$user->user} (ID: {$user->id})");

            Auth::login($user);

            $currentUser = Auth::user();
            $this->info("   🔐 Usuario autenticado: {$currentUser->user} (ID: {$currentUser->id})");

            $controller = new \App\Http\Controllers\NoteController();
            $request = new \Illuminate\Http\Request();
            
            try {
                $response = $controller->index($request);
                $data = json_decode($response->getContent(), true);
                
                if ($data['success']) {
                    $notes = $data['data'];
                    $this->info("   📝 Notas retornadas por API: " . count($notes));
                    
                    foreach ($notes as $note) {
                        $this->line("      - ID: {$note['id']} | Título: {$note['title']} | User ID: {$note['user_id']}");

                        if ($note['user_id'] != $user->id) {
                            $this->error("      ❌ ERROR: La nota no pertenece al usuario actual!");
                        } else {
                            $this->info("      ✅ Nota pertenece al usuario correcto");
                        }
                    }
                } else {
                    $this->error("   ❌ Error en la respuesta de la API");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Excepción: " . $e->getMessage());
            }

            $dbNotes = Note::where('user_id', $user->id)->get();
            $this->info("   🗄️ Notas en BD para este usuario: " . $dbNotes->count());
            
            foreach ($dbNotes as $note) {
                $this->line("      - ID: {$note->id} | Título: {$note->title} | User ID: {$note->user_id}");
            }
            
            Auth::logout();
        }

        $this->info("\n✅ Prueba completada");
        return 0;
    }
}