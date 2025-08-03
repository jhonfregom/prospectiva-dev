<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;
use App\Models\User;

class CheckNotesCreation extends Command
{
    protected $signature = 'notes:check-creation';
    protected $description = 'Verificar cómo se están creando las notas';

    public function handle()
    {
        $this->info('🔍 Verificando creación de notas...');

        // Verificar todas las notas ordenadas por fecha de creación
        $notes = Note::orderBy('created_at', 'desc')->get();
        
        $this->info("📊 Total de notas: {$notes->count()}");
        
        foreach ($notes as $note) {
            $user = User::find($note->user_id);
            $userName = $user ? $user->user : 'Usuario no encontrado';
            
            $this->line("ID: {$note->id} | Título: '{$note->title}' | User: {$userName} (ID: {$note->user_id}) | Creada: {$note->created_at}");
        }

        // Verificar si hay notas recientes (últimas 24 horas)
        $recentNotes = Note::where('created_at', '>=', now()->subDay())->get();
        $this->info("\n📅 Notas creadas en las últimas 24 horas: {$recentNotes->count()}");
        
        foreach ($recentNotes as $note) {
            $user = User::find($note->user_id);
            $userName = $user ? $user->user : 'Usuario no encontrado';
            
            $this->line("   - ID: {$note->id} | Título: '{$note->title}' | User: {$userName} | Creada: {$note->created_at}");
        }

        // Verificar si hay notas sin user_id
        $orphanNotes = Note::whereNull('user_id')->get();
        if ($orphanNotes->count() > 0) {
            $this->error("\n❌ ERROR: Se encontraron {$orphanNotes->count()} notas sin user_id:");
            foreach ($orphanNotes as $note) {
                $this->line("   - ID: {$note->id} | Título: '{$note->title}' | Creada: {$note->created_at}");
            }
        } else {
            $this->info("\n✅ Todas las notas tienen user_id asignado");
        }

        // Verificar si hay notas con user_id inválido
        $invalidUserNotes = Note::whereNotIn('user_id', User::pluck('id'))->get();
        if ($invalidUserNotes->count() > 0) {
            $this->error("\n❌ ERROR: Se encontraron {$invalidUserNotes->count()} notas con user_id inválido:");
            foreach ($invalidUserNotes as $note) {
                $this->line("   - ID: {$note->id} | Título: '{$note->title}' | User ID: {$note->user_id}");
            }
        } else {
            $this->info("\n✅ Todas las notas tienen user_id válido");
        }

        return 0;
    }
} 