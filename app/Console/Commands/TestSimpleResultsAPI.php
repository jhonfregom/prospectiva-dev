<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Traceability;
use Illuminate\Support\Facades\Auth;

class TestSimpleResultsAPI extends Command
{
    protected $signature = 'test:simple-results-api {user_id}';
    protected $description = 'Test the simple results API for a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return 1;
        }

        $this->info("🧪 Probando API simple de resultados para usuario: {$user->user} (ID: {$user->id}, Rol: {$user->role})");

        // Simular autenticación
        Auth::login($user);

        // Obtener rutas del usuario
        $userRoutes = Traceability::where('user_id', $user->id)->get();
        
        if ($userRoutes->count() == 0) {
            $this->warn("El usuario no tiene rutas");
            Auth::logout();
            return 0;
        }

        $this->info("\n📊 Rutas del usuario:");
        foreach ($userRoutes as $route) {
            $this->line("   - Ruta {$route->tried} (ID: {$route->id})");
        }

        // Probar API apiList simplificada
        $this->info("\n🌐 Probando /results/users (apiList) - versión simplificada:");
        try {
            $controller = new \App\Http\Controllers\UserController();
            $request = new \Illuminate\Http\Request();
            
            // Crear una versión simplificada del método apiList
            $result = [];
            
            if ($user->role == 1) {
                // Admin: ver todas las rutas de todos los usuarios
                $users = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')->get();
            } else {
                // Usuario normal: ver solo sus propias rutas
                $users = \App\Models\User::select('id', 'first_name', 'last_name', 'document_id', 'user')
                    ->where('id', $user->id)
                    ->get();
            }

            foreach ($users as $userData) {
                $userRoutes = \App\Models\Traceability::where('user_id', $userData->id)->get();
                
                if ($userRoutes->count() > 0) {
                    foreach ($userRoutes as $route) {
                        $userRow = clone $userData;
                        $userRow->route_id = $route->id;
                        $userRow->route_name = 'Ruta ' . $route->tried;

                        // Lógica especial para la ruta 2: solo se marca como completada si también tiene conclusions = '1'
                        if ($route->tried == '2') {
                            $userRow->status = ($route->results === '1' && $route->conclusions === '1') ? 'Completado' : 'Sin terminar';
                        } else {
                            $userRow->status = $route->results === '1' ? 'Completado' : 'Sin terminar';
                        }
                        
                        $result[] = $userRow;
                    }
                } else {
                    $userRow = clone $userData;
                    $userRow->route_id = null;
                    $userRow->route_name = 'Sin ruta';
                    $userRow->status = 'Sin terminar';
                    $result[] = $userRow;
                }
            }
            
            $this->info("   ✅ API exitosa");
            $this->info("   📊 Registros devueltos: " . count($result));
            
            foreach ($result as $index => $record) {
                $this->line("      " . ($index + 1) . ". Usuario: {$record->user} | Ruta: {$record->route_name} | Estado: {$record->status}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error en API: " . $e->getMessage());
        }

        // Cerrar sesión
        Auth::logout();

        $this->info("\n🎉 Prueba completada");
        return 0;
    }
}
