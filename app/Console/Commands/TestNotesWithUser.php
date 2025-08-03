<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class TestNotesWithUser extends Command
{
    protected $signature = 'notes:test-with-user {user_id}';
    protected $description = 'Probar las notas con un usuario específico';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info("🧪 Probando notas con usuario ID: {$userId}");
        
        // Buscar el usuario
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ Usuario con ID {$userId} no encontrado");
            return 1;
        }
        
        $this->info("👤 Usuario: {$user->user} (ID: {$user->id})");
        
        // Simular login del usuario
        Auth::login($user);
        
        // Verificar que el usuario está autenticado
        $currentUser = Auth::user();
        $this->info("🔐 Usuario autenticado: {$currentUser->user} (ID: {$currentUser->id})");
        
        // Obtener notas directamente del modelo
        $userNotes = Note::where('user_id', $user->id)->get();
        $this->info("📝 Notas del usuario en BD: {$userNotes->count()}");
        
        foreach ($userNotes as $note) {
            $this->line("   - ID: {$note->id} | Título: '{$note->title}' | User ID: {$note->user_id}");
        }
        
        // Probar el método del controlador
        $controller = new \App\Http\Controllers\NoteController();
        $request = new \Illuminate\Http\Request();
        
        try {
            $response = $controller->index($request);
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                $notes = $data['data'];
                $this->info("📝 Notas retornadas por API: " . count($notes));
                
                foreach ($notes as $note) {
                    $this->line("   - ID: {$note['id']} | Título: '{$note['title']}' | User ID: {$note['user_id']}");
                }
            } else {
                $this->error("❌ Error en la respuesta de la API");
            }
        } catch (\Exception $e) {
            $this->error("❌ Excepción: " . $e->getMessage());
        }
        
        // Verificar todas las notas en la BD
        $allNotes = Note::all();
        $this->info("\n📊 Todas las notas en la BD: {$allNotes->count()}");
        
        foreach ($allNotes as $note) {
            $this->line("   - ID: {$note->id} | Título: '{$note->title}' | User ID: {$note->user_id}");
        }
        
        Auth::logout();
        
        return 0;
    }
} 