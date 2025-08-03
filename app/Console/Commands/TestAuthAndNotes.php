<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class TestAuthAndNotes extends Command
{
    protected $signature = 'notes:test-auth';
    protected $description = 'Probar autenticación y notas';

    public function handle()
    {
        $this->info('🧪 Probando autenticación y notas...');

        // Probar con diferentes usuarios
        $users = User::whereIn('id', [1, 2, 3, 4])->get();
        
        foreach ($users as $user) {
            $this->line("\n👤 Probando con usuario: {$user->user} (ID: {$user->id})");
            
            // Simular login del usuario
            Auth::login($user);
            
            // Verificar autenticación
            $this->info("   🔐 Autenticado: " . (Auth::check() ? 'SÍ' : 'NO'));
            $this->info("   👤 Usuario actual: " . (Auth::user() ? Auth::user()->user : 'NINGUNO'));
            
            // Obtener notas del usuario
            $userNotes = Note::where('user_id', $user->id)->get();
            $this->info("   📝 Notas del usuario: {$userNotes->count()}");
            
            foreach ($userNotes as $note) {
                $this->line("      - ID: {$note->id} | Título: '{$note->title}'");
            }
            
            // Probar el controlador
            $controller = new \App\Http\Controllers\NoteController();
            $request = new \Illuminate\Http\Request();
            
            try {
                $response = $controller->index($request);
                $data = json_decode($response->getContent(), true);
                
                if ($data['success']) {
                    $notes = $data['data'];
                    $this->info("   📝 API retorna: " . count($notes) . " notas");
                    
                    foreach ($notes as $note) {
                        $this->line("      - ID: {$note['id']} | Título: '{$note['title']}' | User ID: {$note['user_id']}");
                    }
                } else {
                    $this->error("   ❌ Error en API");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Excepción: " . $e->getMessage());
            }
            
            Auth::logout();
        }

        // Verificar todas las notas
        $this->info("\n📊 Todas las notas en la BD:");
        $allNotes = Note::all();
        foreach ($allNotes as $note) {
            $this->line("   - ID: {$note->id} | Título: '{$note->title}' | User ID: {$note->user_id}");
        }

        return 0;
    }
} 