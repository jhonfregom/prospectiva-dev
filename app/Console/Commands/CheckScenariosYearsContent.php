<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Scenarios;
use App\Models\User;

class CheckScenariosYearsContent extends Command
{
    protected $signature = 'check:scenarios-years {user_id?}';
    protected $description = 'Check scenarios years content in database';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info("🔍 Verificando contenido de años en escenarios...");

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Usuario con ID {$userId} no encontrado");
                return 1;
            }
            
            $this->info("📊 Verificando escenarios para usuario: {$user->user} (ID: {$user->id})");
            
            $scenarios = Scenarios::where('user_id', $user->id)->get();
            
            if ($scenarios->count() == 0) {
                $this->warn("   ⚠️  El usuario no tiene escenarios");
            } else {
                $this->info("   📋 Escenarios encontrados: {$scenarios->count()}");
                
                foreach ($scenarios as $scenario) {
                    $this->line("      - Escenario {$scenario->num_scenario} (ID: {$scenario->id})");
                    $this->line("        Título: " . ($scenario->titulo ?: 'SIN TÍTULO'));
                    $this->line("        Año 1: " . ($scenario->year1 ?: 'VACÍO'));
                    $this->line("        Año 2: " . ($scenario->year2 ?: 'VACÍO'));
                    $this->line("        Año 3: " . ($scenario->year3 ?: 'VACÍO'));
                    $this->line("");
                }
            }
        } else {
            $this->info("📊 Verificando todos los escenarios en la base de datos...");
            
            $scenarios = Scenarios::all();
            
            if ($scenarios->count() == 0) {
                $this->warn("   ⚠️  No hay escenarios en la base de datos");
            } else {
                $this->info("   📋 Total de escenarios: {$scenarios->count()}");
                
                $scenariosWithYear1 = $scenarios->filter(function($scenario) {
                    return !empty($scenario->year1);
                });
                $scenariosWithYear2 = $scenarios->filter(function($scenario) {
                    return !empty($scenario->year2);
                });
                $scenariosWithYear3 = $scenarios->filter(function($scenario) {
                    return !empty($scenario->year3);
                });
                
                $this->info("   ✅ Escenarios con Año 1: {$scenariosWithYear1->count()}");
                $this->info("   ✅ Escenarios con Año 2: {$scenariosWithYear2->count()}");
                $this->info("   ✅ Escenarios con Año 3: {$scenariosWithYear3->count()}");
                
                $scenariosWithAnyYear = $scenarios->filter(function($scenario) {
                    return !empty($scenario->year1) || !empty($scenario->year2) || !empty($scenario->year3);
                });
                
                $this->info("   📊 Escenarios con al menos un año: {$scenariosWithAnyYear->count()}");
                
                if ($scenariosWithAnyYear->count() == 0) {
                    $this->warn("   ⚠️  No hay escenarios con contenido de años");
                }
            }
        }

        $this->info("\n🎉 Verificación completada");
        return 0;
    }
}
