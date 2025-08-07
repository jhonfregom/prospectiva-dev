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

            Auth::login($user);

            $notes = Note::getByUser($user->id);
            $this->line("   📝 Notas encontradas: " . $notes->count());

            foreach ($notes as $note) {
                $this->line("      - ID: {$note->id}, Título: {$note->title}, Contenido: " . substr($note->content, 0, 50) . "...");
            }

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