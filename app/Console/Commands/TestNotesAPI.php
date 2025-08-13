<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class TestNotesAPI extends Command
{
    protected $signature = 'test:notes-api {user_id?}';
    protected $description = 'Test the notes API to verify it returns data correctly';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $users = User::where('id', $userId)->get();
        } else {
            $users = User::all();
        }

        $this->info("🧪 Probando API de notas para {$users->count()} usuarios...");

        foreach ($users as $user) {
            $this->line("\n👤 Usuario: {$user->user} (ID: {$user->id})");
            
            // Verificar notas en la base de datos
            $dbNotes = Note::where('user_id', $user->id)->get();
            $this->info("   📊 Notas en BD: {$dbNotes->count()}");
            
            if ($dbNotes->count() > 0) {
                foreach ($dbNotes->take(3) as $note) {
                    $this->line("      - ID: {$note->id} | Título: '{$note->title}' | Creada: {$note->created_at}");
                }
                if ($dbNotes->count() > 3) {
                    $this->line("      ... y " . ($dbNotes->count() - 3) . " más");
                }
            }
            
            // Simular petición HTTP a la API
            $this->info("   🌐 Probando API...");
            
            try {
                // Simular la petición HTTP usando el controlador directamente
                $request = new \Illuminate\Http\Request();
                $request->merge(['user_id' => $user->id]);
                
                // Crear una instancia del controlador
                $controller = new \App\Http\Controllers\NoteController();
                
                // Simular autenticación
                \Illuminate\Support\Facades\Auth::login($user);
                
                // Llamar al método index
                $response = $controller->index($request);
                $responseData = json_decode($response->getContent(), true);
                
                if ($responseData['success']) {
                    $apiNotes = $responseData['data'];
                    $this->info("   ✅ API exitosa - Notas devueltas: " . (is_array($apiNotes) ? count($apiNotes) : 'N/A'));
                    
                    if (is_array($apiNotes) && count($apiNotes) > 0) {
                        foreach (array_slice($apiNotes, 0, 3) as $note) {
                            $this->line("      - ID: {$note['id']} | Título: '{$note['title']}' | Creada: {$note['created_at']}");
                        }
                        if (count($apiNotes) > 3) {
                            $this->line("      ... y " . (count($apiNotes) - 3) . " más");
                        }
                    }
                } else {
                    $this->error("   ❌ API falló: " . ($responseData['message'] ?? 'Error desconocido'));
                }
                
                // Cerrar sesión
                \Illuminate\Support\Facades\Auth::logout();
                
            } catch (\Exception $e) {
                $this->error("   ❌ Error en API: " . $e->getMessage());
            }
        }

        $this->info("\n🎉 Prueba completada");
        return 0;
    }
}
