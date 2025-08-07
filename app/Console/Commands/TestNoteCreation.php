<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class TestNoteCreation extends Command
{
    protected $signature = 'notes:test-creation {user_id}';
    protected $description = 'Probar la creación de notas con un usuario específico';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info("🧪 Probando creación de notas con usuario ID: {$userId}");

        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ Usuario con ID {$userId} no encontrado");
            return 1;
        }
        
        $this->info("👤 Usuario: {$user->user} (ID: {$user->id})");

        Auth::login($user);

        $this->info("🔐 Autenticado: " . (Auth::check() ? 'SÍ' : 'NO'));

        $notesBefore = Note::where('user_id', $user->id)->count();
        $this->info("📝 Notas antes de crear: {$notesBefore}");

        $testNote = Note::create([
            'user_id' => $user->id,
            'title' => 'Nota de prueba - ' . now()->format('H:i:s'),
            'content' => 'Contenido de prueba creado el ' . now()->format('Y-m-d H:i:s'),
            'traceability_id' => null
        ]);
        
        $this->info("✅ Nota creada con ID: {$testNote->id}");

        $notesAfter = Note::where('user_id', $user->id)->count();
        $this->info("📝 Notas después de crear: {$notesAfter}");

        $createdNote = Note::find($testNote->id);
        if ($createdNote) {
            $this->info("✅ Nota encontrada en BD:");
            $this->line("   - ID: {$createdNote->id}");
            $this->line("   - Título: '{$createdNote->title}'");
            $this->line("   - User ID: {$createdNote->user_id}");
            $this->line("   - Creada: {$createdNote->created_at}");
        } else {
            $this->error("❌ La nota no se encontró en la BD");
        }

        $controller = new \App\Http\Controllers\NoteController();
        $request = new \Illuminate\Http\Request();
        
        try {
            $response = $controller->index($request);
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                $notes = $data['data'];
                $this->info("📝 API retorna: " . count($notes) . " notas");
                
                foreach ($notes as $note) {
                    $this->line("   - ID: {$note['id']} | Título: '{$note['title']}' | User ID: {$note['user_id']}");
                }
            } else {
                $this->error("❌ Error en la respuesta de la API");
            }
        } catch (\Exception $e) {
            $this->error("❌ Excepción: " . $e->getMessage());
        }

        $testNote->delete();
        $this->info("🗑️ Nota de prueba eliminada");
        
        Auth::logout();
        
        return 0;
    }
}