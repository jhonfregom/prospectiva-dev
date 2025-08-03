<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;
use App\Models\User;

class CheckNotesFiltering extends Command
{
    protected $signature = 'notes:check-filtering';
    protected $description = 'Verificar que las notas estén correctamente filtradas por usuario';

    public function handle()
    {
        $this->info('🔍 Verificando filtrado de notas por usuario...');

        // Obtener todos los usuarios
        $users = User::all();
        
        foreach ($users as $user) {
            $this->line("\n👤 Usuario: {$user->user} (ID: {$user->id})");
            
            // Obtener notas del usuario
            $userNotes = Note::where('user_id', $user->id)->get();
            
            if ($userNotes->count() > 0) {
                $this->info("   📝 Notas encontradas: {$userNotes->count()}");
                foreach ($userNotes as $note) {
                    $this->line("      - ID: {$note->id} | Título: {$note->title} | Creada: {$note->created_at}");
                }
            } else {
                $this->warn("   ⚠️ No tiene notas");
            }
        }

        // Verificar que no hay notas sin user_id
        $orphanNotes = Note::whereNull('user_id')->get();
        if ($orphanNotes->count() > 0) {
            $this->error("\n❌ ERROR: Se encontraron {$orphanNotes->count()} notas sin user_id:");
            foreach ($orphanNotes as $note) {
                $this->line("   - ID: {$note->id} | Título: {$note->title}");
            }
        } else {
            $this->info("\n✅ Todas las notas tienen user_id asignado");
        }

        // Verificar total de notas
        $totalNotes = Note::count();
        $this->info("\n📊 Resumen:");
        $this->info("   Total de notas en la BD: {$totalNotes}");
        $this->info("   Total de usuarios: {$users->count()}");

        return 0;
    }
} 