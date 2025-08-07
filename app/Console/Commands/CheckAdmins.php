<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckAdmins extends Command
{
    protected $signature = 'check:admins';
    protected $description = 'Verificar administradores en el sistema';

    public function handle()
    {
        $this->info("=== VERIFICACIÓN DE ADMINISTRADORES ===");
        
        $admins = User::where('role', 1)->get();
        
        $this->info("Total de administradores: " . $admins->count());
        $this->line('');
        
        if ($admins->count() > 0) {
            $this->info("=== LISTA DE ADMINISTRADORES ===");
            foreach ($admins as $admin) {
                $this->info("ID: {$admin->id} - Email: {$admin->user} - Nombre: {$admin->first_name} {$admin->last_name}");
            }
            $this->line('');
            
            if ($admins->count() > 1) {
                $this->warn("⚠️  Hay múltiples administradores. Esto causará que se envíen múltiples correos de activación.");
                $this->info("💡 Solución: Modificar el listener para enviar solo a un administrador principal.");
            }
        } else {
            $this->error("❌ No hay administradores en el sistema");
        }
    }
}