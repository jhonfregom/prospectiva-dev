<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CheckUserAuth extends Command
{
    protected $signature = 'check:user-auth {user_id}';
    protected $description = 'Check the authentication status of a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return 1;
        }

        $this->info("👤 Verificando autenticación para: {$user->user} (ID: {$user->id})");
        
        // Simular autenticación
        Auth::login($user);
        
        $this->info("✅ Usuario autenticado: " . (Auth::check() ? 'SÍ' : 'NO'));
        $this->info("✅ Usuario actual: " . (Auth::user() ? Auth::user()->user : 'NINGUNO'));
        $this->info("✅ ID del usuario actual: " . (Auth::id() ?: 'NINGUNO'));
        
        // Verificar si puede acceder a las notas
        $this->info("\n📝 Verificando acceso a notas...");
        
        try {
            $notes = \App\Models\Note::where('user_id', $user->id)->get();
            $this->info("✅ Notas del usuario: {$notes->count()}");
            
            if ($notes->count() > 0) {
                foreach ($notes->take(3) as $note) {
                    $this->line("   - ID: {$note->id} | Título: '{$note->title}' | Creada: {$note->created_at}");
                }
                if ($notes->count() > 3) {
                    $this->line("   ... y " . ($notes->count() - 3) . " más");
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ Error accediendo a notas: " . $e->getMessage());
        }
        
        // Cerrar sesión
        Auth::logout();
        
        $this->info("\n🎉 Verificación completada");
        return 0;
    }
}
