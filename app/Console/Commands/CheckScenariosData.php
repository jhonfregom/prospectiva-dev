<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Scenarios;
use App\Models\User;

class CheckScenariosData extends Command
{
    protected $signature = 'check:scenarios-data {user_id?}';
    protected $description = 'Check scenarios data in database';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $this->info("🔍 Verificando datos de escenarios...");

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
                    $this->line("        Estado: {$scenario->state}");
                    $this->line("        Ruta ID: {$scenario->tried_id}");
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
                
                $scenariosWithTitle = $scenarios->filter(function($scenario) {
                    return !empty($scenario->titulo);
                });
                $scenariosWithoutTitle = $scenarios->filter(function($scenario) {
                    return empty($scenario->titulo);
                });
                
                $this->info("   ✅ Escenarios con título: {$scenariosWithTitle->count()}");
                $this->info("   ❌ Escenarios sin título: {$scenariosWithoutTitle->count()}");
                
                if ($scenariosWithoutTitle->count() > 0) {
                    $this->warn("\n   📋 Escenarios sin título:");
                    foreach ($scenariosWithoutTitle as $scenario) {
                        $user = User::find($scenario->user_id);
                        $userName = $user ? $user->user : 'Usuario desconocido';
                        $this->line("      - Usuario: {$userName} | Escenario {$scenario->num_scenario} (ID: {$scenario->id}) | Ruta: {$scenario->tried_id}");
                    }
                }
            }
        }

        $this->info("\n🎉 Verificación completada");
        return 0;
    }
}
