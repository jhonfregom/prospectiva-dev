<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Traceability;
use App\Models\User;

class CheckTraceability extends Command
{
    protected $signature = 'traceability:check {user_id}';
    protected $description = 'Verificar el estado de traceability para un usuario';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info("Verificando traceability para usuario ID: {$userId}");
        
        // Verificar si el usuario existe
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return;
        }
        
        $this->info("Usuario: {$user->user} (Rol: {$user->role})");
        
        // Verificar traceability
        $traceability = Traceability::where('user_id', $userId)->first();
        
        if (!$traceability) {
            $this->warn("No se encontró registro de traceability para el usuario");
            $this->info("Creando registro de traceability...");
            $traceability = Traceability::getOrCreateForUser($userId);
        }
        
        $this->info("Estado actual de traceability:");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['variables', $traceability->variables],
                ['matriz', $traceability->matriz],
                ['maps', $traceability->maps],
                ['hypothesis', $traceability->hypothesis],
                ['shwartz', $traceability->shwartz],
                ['conditions', $traceability->conditions],
                ['scenarios', $traceability->scenarios],
                ['conclusions', $traceability->conclusions],
                ['results', $traceability->results],
                ['state', $traceability->state],
            ]
        );
        
        // Probar marcar variables como completada
        $this->info("Probando marcar variables como completada...");
        $traceability->markSectionCompleted('variables');
        
        $this->info("Estado después de marcar variables:");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['variables', $traceability->variables],
                ['matriz', $traceability->matriz],
            ]
        );
    }
} 