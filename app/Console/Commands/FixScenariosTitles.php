<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Scenarios;

class FixScenariosTitles extends Command
{
    protected $signature = 'fix:scenarios-titles {user_id?}';
    protected $description = 'Fix empty scenario titles by assigning default titles';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info("🔧 Arreglando títulos de escenarios...");

        $query = Scenarios::whereNull('titulo')->orWhere('titulo', '');
        
        if ($userId) {
            $query->where('user_id', $userId);
            $this->info("📊 Arreglando escenarios para usuario ID: {$userId}");
        } else {
            $this->info("📊 Arreglando todos los escenarios sin título");
        }

        $scenarios = $query->get();
        
        if ($scenarios->count() == 0) {
            $this->info("✅ No hay escenarios sin título para arreglar");
            return 0;
        }

        $this->info("📋 Escenarios encontrados sin título: {$scenarios->count()}");

        $updated = 0;
        foreach ($scenarios as $scenario) {
            $defaultTitle = "ESCENARIO {$scenario->num_scenario}";
            
            $scenario->titulo = $defaultTitle;
            $scenario->save();
            
            $this->line("   ✅ Escenario {$scenario->num_scenario} (ID: {$scenario->id}) - Título: {$defaultTitle}");
            $updated++;
        }

        $this->info("\n🎉 Proceso completado. {$updated} escenarios actualizados.");
        return 0;
    }
}
