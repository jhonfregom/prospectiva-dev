<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;

class CheckNotesDB extends Command
{
    protected $signature = 'check:notes-db {user_id?}';
    protected $description = 'Check notes in database for a specific user or all users';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $this->info("🔍 Verificando notas para usuario ID: {$userId}");
            $notes = Note::where('user_id', $userId)->get();
        } else {
            $this->info("🔍 Verificando todas las notas en la base de datos");
            $notes = Note::all();
        }

        $this->info("📊 Total de notas encontradas: {$notes->count()}");
        
        if ($notes->count() > 0) {
            $this->table(
                ['ID', 'User ID', 'Título', 'Contenido', 'Creada'],
                $notes->map(function($note) {
                    return [
                        $note->id,
                        $note->user_id,
                        $note->title ?: 'Sin título',
                        substr($note->content, 0, 50) . (strlen($note->content) > 50 ? '...' : ''),
                        $note->created_at->format('Y-m-d H:i:s')
                    ];
                })->toArray()
            );
        }

        return 0;
    }
}
