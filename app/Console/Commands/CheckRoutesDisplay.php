<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Traceability;

class CheckRoutesDisplay extends Command
{
    protected $signature = 'check:routes-display {user_id?}';
    protected $description = 'Check that routes are displayed correctly according to user role';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $users = User::where('id', $userId)->get();
        } else {
            $users = User::all();
        }

        $this->info("🔍 Verificando visualización de rutas para {$users->count()} usuarios...");

        foreach ($users as $user) {
            $this->line("\n👤 Usuario: {$user->user} (ID: {$user->id}, Rol: {$user->role})");
            
            // Obtener rutas del usuario
            $userRoutes = Traceability::where('user_id', $user->id)->get();
            
            if ($userRoutes->count() > 0) {
                $this->info("   📊 Rutas encontradas: {$userRoutes->count()}");
                
                foreach ($userRoutes as $route) {
                    $status = $route->tried == '2' 
                        ? (($route->results === '1' && $route->conclusions === '1') ? 'Completado' : 'Sin terminar')
                        : ($route->results === '1' ? 'Completado' : 'Sin terminar');
                    
                    $this->line("      - Ruta {$route->tried} (ID: {$route->id}): {$status}");
                    $this->line("        Variables: {$route->variables} | Matriz: {$route->matriz} | Maps: {$route->maps}");
                    $this->line("        Hypothesis: {$route->hypothesis} | Schwartz: {$route->shwartz} | Conditions: {$route->conditions}");
                    $this->line("        Scenarios: {$route->scenarios} | Conclusions: {$route->conclusions} | Results: {$route->results}");
                }
            } else {
                $this->warn("   ⚠️ No tiene rutas");
            }

            // Simular lo que vería este usuario
            $this->info("   👁️ Lo que vería este usuario:");
            
            if ($user->role == 1) {
                $this->line("      - Como ADMIN: Vería todas las rutas de todos los usuarios");
                
                $allUsers = User::all();
                $totalRoutes = 0;
                foreach ($allUsers as $otherUser) {
                    $otherUserRoutes = Traceability::where('user_id', $otherUser->id)->count();
                    $totalRoutes += $otherUserRoutes;
                }
                $this->line("      - Total de rutas visibles: {$totalRoutes}");
                
            } else {
                $this->line("      - Como USUARIO NORMAL: Solo vería sus propias rutas");
                $this->line("      - Rutas visibles: {$userRoutes->count()}");
            }
        }

        // Verificar rutas totales en el sistema
        $this->info("\n📊 Resumen del sistema:");
        $totalRoutes = Traceability::count();
        $totalUsers = User::count();
        $usersWithRoutes = Traceability::distinct('user_id')->count();
        
        $this->info("   - Total de rutas en el sistema: {$totalRoutes}");
        $this->info("   - Total de usuarios: {$totalUsers}");
        $this->info("   - Usuarios con rutas: {$usersWithRoutes}");
        $this->info("   - Usuarios sin rutas: " . ($totalUsers - $usersWithRoutes));

        // Verificar rutas por tipo
        $route1Count = Traceability::where('tried', '1')->count();
        $route2Count = Traceability::where('tried', '2')->count();
        
        $this->info("   - Rutas tipo 1: {$route1Count}");
        $this->info("   - Rutas tipo 2: {$route2Count}");

        $this->info("\n🎉 Verificación completada");
        return 0;
    }
}
