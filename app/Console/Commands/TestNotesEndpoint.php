<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\NoteController;
use Illuminate\Http\Request;

class TestNotesEndpoint extends Command
{
    protected $signature = 'test:notes-endpoint';
    protected $description = 'Probar el endpoint de notas directamente';

    public function handle()
    {
        $this->info('🧪 Probando endpoint de notas...');
        
        $users = User::all();
        
        foreach ($users as $user) {
            $this->info("\n👤 Probando endpoint para usuario: {$user->first_name} {$user->last_name} (ID: {$user->id})");
            
            // Simular autenticación
            Auth::login($user);
            
            // Crear una instancia del controlador
            $controller = new NoteController();
            
            // Crear una request simulada
            $request = new Request();
            
            // Llamar al método index
            $response = $controller->index($request);
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                $notes = $data['data'];
                $this->line("   📝 Notas retornadas por el endpoint: " . count($notes));
                
                // Verificar que todas las notas pertenecen al usuario
                $wrongNotes = collect($notes)->filter(function($note) use ($user) {
                    return $note['user_id'] != $user->id;
                })->count();
                
                if ($wrongNotes > 0) {
                    $this->error("   ❌ ERROR: El endpoint retornó {$wrongNotes} notas que no pertenecen a este usuario");
                } else {
                    $this->info("   ✅ Endpoint funcionando correctamente");
                }
                
                // Mostrar las notas
                foreach ($notes as $note) {
                    $this->line("      - ID: {$note['id']}, Título: {$note['title']}, User ID: {$note['user_id']}");
                }
            } else {
                $this->error("   ❌ Error en el endpoint: " . ($data['message'] ?? 'Error desconocido'));
            }
        }
        
        $this->info("\n✅ Prueba del endpoint completada");
    }
} 