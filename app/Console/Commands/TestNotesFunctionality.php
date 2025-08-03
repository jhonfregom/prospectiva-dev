<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;

class TestNotesFunctionality extends Command
{
    protected $signature = 'test:notes-functionality';
    protected $description = 'Probar la funcionalidad de notas con diferentes usuarios';

    public function handle()
    {
        $this->info('🧪 Probando funcionalidad de notas...');
        
        $users = User::all();
        
        foreach ($users as $user) {
            $this->info("\n👤 Probando usuario: {$user->first_name} {$user->last_name} (ID: {$user->id})");
            
            // Simular autenticación
            Auth::login($user);
            
            // Obtener notas del usuario
            $notes = Note::getByUser($user->id);
            $this->line("   📝 Notas encontradas: " . $notes->count());
            
            // Mostrar detalles de las notas
            foreach ($notes as $note) {
                $this->line("      - ID: {$note->id}, Título: {$note->title}, Contenido: " . substr($note->content, 0, 50) . "...");
            }
            
            // Verificar que las notas pertenecen al usuario correcto
            $wrongNotes = Note::where('user_id', '!=', $user->id)->count();
            if ($wrongNotes > 0) {
                $this->error("   ❌ ERROR: Se encontraron {$wrongNotes} notas que no pertenecen a este usuario");
            } else {
                $this->info("   ✅ Verificación de propiedad correcta");
            }
        }
        
        $this->info("\n✅ Prueba completada");
    }
} 