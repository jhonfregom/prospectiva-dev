<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;

class CheckNotesTraceability extends Command
{
    protected $signature = 'notes:check-traceability';
    protected $description = 'Verificar el traceability_id de las notas';

    public function handle()
    {
        $this->info('🔍 Verificando traceability_id de las notas...');

        $notes = Note::all();
        
        foreach ($notes as $note) {
            $traceabilityId = $note->traceability_id ?? 'NULL';
            $this->line("ID: {$note->id} | Título: {$note->title} | User ID: {$note->user_id} | Traceability ID: {$traceabilityId}");
        }

        // Verificar cuántas notas tienen traceability_id NULL
        $nullTraceabilityNotes = Note::whereNull('traceability_id')->count();
        $this->info("\n📊 Resumen:");
        $this->info("   Total de notas: {$notes->count()}");
        $this->info("   Notas con traceability_id NULL: {$nullTraceabilityNotes}");
        $this->info("   Notas con traceability_id: " . ($notes->count() - $nullTraceabilityNotes));

        return 0;
    }
} 