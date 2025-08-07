<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;

class CheckAllNotes extends Command
{
    protected $signature = 'notes:check-all';
    protected $description = 'Verificar todas las notas en la base de datos';

    public function handle()
    {
        $this->info('🔍 Verificando todas las notas en la base de datos...');

        $notes = Note::orderBy('created_at', 'desc')->get();
        
        $this->info("📊 Total de notas en la BD: {$notes->count()}");
        
        foreach ($notes as $note) {
            $traceabilityId = $note->traceability_id ?? 'NULL';
            $this->line("ID: {$note->id} | Título: '{$note->title}' | User ID: {$note->user_id} | Traceability ID: {$traceabilityId} | Creada: {$note->created_at}");
        }

        $orphanNotes = Note::whereNull('user_id')->get();
        if ($orphanNotes->count() > 0) {
            $this->error("\n❌ ERROR: Se encontraron {$orphanNotes->count()} notas sin user_id:");
            foreach ($orphanNotes as $note) {
                $this->line("   - ID: {$note->id} | Título: {$note->title}");
            }
        } else {
            $this->info("\n✅ Todas las notas tienen user_id asignado");
        }

        return 0;
    }
}