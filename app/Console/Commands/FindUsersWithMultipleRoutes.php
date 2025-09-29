<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Traceability;

class FindUsersWithMultipleRoutes extends Command
{
    protected $signature = 'find:users-multiple-routes';
    protected $description = 'Find users with multiple routes';

    public function handle()
    {
        $this->info("🔍 Buscando usuarios con múltiples rutas...");

        $users = User::where('role', 0)->get();
        
        foreach ($users as $user) {
            $routes = Traceability::where('user_id', $user->id)->get();
            
            if ($routes->count() > 1) {
                $this->info("✅ Usuario: {$user->user} (ID: {$user->id}) - Rutas: {$routes->count()}");
                foreach ($routes as $route) {
                    $this->line("   - Ruta {$route->tried} (ID: {$route->id})");
                }
            }
        }

        $this->info("\n🎉 Búsqueda completada");
        return 0;
    }
}
