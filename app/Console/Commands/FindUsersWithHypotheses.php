<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hypothesis;
use App\Models\User;

class FindUsersWithHypotheses extends Command
{
    protected $signature = 'find:users-hypotheses';
    protected $description = 'Find users with hypotheses';

    public function handle()
    {
        $this->info("🔍 Buscando usuarios con hipótesis...");

        $hypotheses = Hypothesis::all();
        
        if ($hypotheses->count() == 0) {
            $this->warn("⚠️  No hay hipótesis en la base de datos");
            return 0;
        }

        $this->info("📊 Total de hipótesis: {$hypotheses->count()}");
        
        $userIds = $hypotheses->pluck('user_id')->unique();
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            $userName = $user ? $user->user : 'Usuario desconocido';
            
            $userHypotheses = $hypotheses->where('user_id', $userId);
            $this->info("✅ Usuario: {$userName} (ID: {$userId}) - Hipótesis: {$userHypotheses->count()}");
        }

        $this->info("\n🎉 Búsqueda completada");
        return 0;
    }
}
