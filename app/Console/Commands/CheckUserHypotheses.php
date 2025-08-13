<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Hypothesis;
use App\Models\User;
use App\Models\Variable;

class CheckUserHypotheses extends Command
{
    protected $signature = 'check:user-hypotheses {user_id}';
    protected $description = 'Check hypotheses for a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return 1;
        }

        $this->info("🔍 Verificando hipótesis para usuario: {$user->user} (ID: {$user->id})");

        // Obtener variables del usuario
        $variables = Variable::where('user_id', $user->id)->get();
        $this->info("📊 Variables encontradas: {$variables->count()}");
        
        foreach ($variables as $variable) {
            $this->line("   - {$variable->id_variable}: {$variable->name_variable}");
        }

        // Obtener hipótesis del usuario
        $hypotheses = Hypothesis::where('user_id', $user->id)->get();
        $this->info("\n📊 Hipótesis encontradas: {$hypotheses->count()}");
        
        if ($hypotheses->count() > 0) {
            foreach ($hypotheses as $hypothesis) {
                $variable = Variable::find($hypothesis->id_variable);
                $variableName = $variable ? $variable->name_variable : 'Variable desconocida';
                
                $this->line("   - Variable: {$variableName}");
                $this->line("     Tipo: {$hypothesis->secondary_hypotheses}");
                $this->line("     Descripción: " . ($hypothesis->description ?: 'SIN DESCRIPCIÓN'));
                $this->line("     Estado: {$hypothesis->state}");
                $this->line("");
            }
        } else {
            $this->warn("   ⚠️  El usuario no tiene hipótesis");
        }

        $this->info("🎉 Verificación completada");
        return 0;
    }
}
