<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Traceability;

class FixRoute2Status extends Command
{
    protected $signature = 'fix:route2-status {user_id?}';
    protected $description = 'Fix the status of existing route 2 that have results=1 but conclusions=0';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            // Corregir un usuario específico
            $routes = Traceability::where('user_id', $userId)
                ->where('tried', '2')
                ->get();
        } else {
            // Corregir todas las rutas 2
            $routes = Traceability::where('tried', '2')->get();
        }

        $this->info("Encontradas {$routes->count()} rutas 2 para verificar...");

        $fixedCount = 0;
        foreach ($routes as $route) {
            $this->info("Verificando ruta ID: {$route->id}, Usuario: {$route->user_id}");
            $this->info("  - results: {$route->results}");
            $this->info("  - conclusions: {$route->conclusions}");
            
            // Para la ruta 2, results debe estar en '1' para permitir acceso, pero el estado "completado" 
            // solo se marca cuando conclusions = '1'
            if ($route->results === '0') {
                $this->warn("  ❌ Ruta 2 con results = '0' - corrigiendo para permitir acceso...");
                $route->results = '1';
                $route->save();
                $fixedCount++;
                $this->info("  ✅ Corregida: results = '1' (habilitado para acceso)");
            } else {
                $this->info("  ✅ Estado correcto");
            }
        }

        $this->info("\n🎉 Proceso completado:");
        $this->info("  - Rutas verificadas: {$routes->count()}");
        $this->info("  - Rutas corregidas: {$fixedCount}");

        return 0;
    }
}
