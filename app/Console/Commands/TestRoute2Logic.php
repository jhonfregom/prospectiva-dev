<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Traceability;
use App\Models\User;

class TestRoute2Logic extends Command
{
    protected $signature = 'test:route2-logic {user_id?}';
    protected $description = 'Test the logic for route 2 completion status';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if (!$userId) {
            $this->error('Debe proporcionar un user_id');
            return 1;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return 1;
        }

        $this->info("🧪 Probando lógica de ruta 2 para usuario: {$user->user} (ID: {$user->id})");

        // Buscar ruta 2 del usuario
        $route2 = Traceability::where('user_id', $user->id)
            ->where('tried', '2')
            ->first();

        if (!$route2) {
            $this->error("No se encontró ruta 2 para el usuario");
            return 1;
        }

        $this->info("\n📊 Estado actual de la ruta 2:");
        $this->table(
            ['Campo', 'Valor', 'Estado'],
            [
                ['tried', $route2->tried, 'Ruta 2'],
                ['variables', $route2->variables, $route2->variables == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['matriz', $route2->matriz, $route2->matriz == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['maps', $route2->maps, $route2->maps == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['hypothesis', $route2->hypothesis, $route2->hypothesis == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['shwartz', $route2->shwartz, $route2->shwartz == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['conditions', $route2->conditions, $route2->conditions == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['scenarios', $route2->scenarios, $route2->scenarios == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['conclusions', $route2->conclusions, $route2->conclusions == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
                ['results', $route2->results, $route2->results == '1' ? '✅ Habilitado' : '❌ Deshabilitado'],
            ]
        );

        // Verificar lógica de estado "Completado"
        $this->info("\n🔍 Verificando lógica de estado 'Completado':");
        
        if ($route2->tried == '2') {
            $isCompleted = ($route2->results === '1' && $route2->conclusions === '1');
            $status = $isCompleted ? 'Completado' : 'Sin terminar';
            
            $this->info("   - results = '{$route2->results}'");
            $this->info("   - conclusions = '{$route2->conclusions}'");
            $this->info("   - Lógica: results === '1' && conclusions === '1' = " . ($isCompleted ? 'true' : 'false'));
            $this->info("   - Estado final: {$status}");
            
            if ($isCompleted) {
                $this->info("   ✅ Ruta 2 marcada como 'Completado' (correcto)");
            } else {
                $this->info("   ⚠️ Ruta 2 marcada como 'Sin terminar' (correcto si conclusions no está completado)");
            }
        }

        // Simular completar conclusions
        $this->info("\n🧪 Simulando completar conclusions...");
        
        $originalConclusions = $route2->conclusions;
        $originalResults = $route2->results;
        
        // Simular completar conclusions
        $route2->conclusions = '1';
        $route2->save();
        
        // Verificar que results se marque automáticamente como '1'
        $route2->refresh();
        
        $this->info("   - conclusions cambiado a: '{$route2->conclusions}'");
        $this->info("   - results después de completar conclusions: '{$route2->results}'");
        
        if ($route2->results === '1') {
            $this->info("   ✅ Lógica correcta: results se marcó automáticamente como '1'");
        } else {
            $this->warn("   ⚠️ Lógica incorrecta: results debería haberse marcado como '1'");
        }

        // Restaurar estado original
        $route2->conclusions = $originalConclusions;
        $route2->results = $originalResults;
        $route2->save();
        
        $this->info("   - Estado restaurado al original");

        $this->info("\n🎉 Prueba de lógica completada");
        return 0;
    }
}
