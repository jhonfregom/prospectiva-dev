<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Traceability;
use Illuminate\Support\Facades\Auth;

class TestResultsAPI extends Command
{
    protected $signature = 'test:results-api {user_id}';
    protected $description = 'Test the results API for a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return 1;
        }

        $this->info("🧪 Probando API de resultados para usuario: {$user->user} (ID: {$user->id}, Rol: {$user->role})");

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

        // Probar API apiList
        $this->info("\n🌐 Probando /results/users (apiList):");
        try {
            $controller = new \App\Http\Controllers\UserController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->apiList($request);
            $responseData = json_decode($response->getContent(), true);
            
            if ($responseData['status'] === 200) {
                $this->info("   ✅ API exitosa");
                $this->info("   📊 Registros devueltos: " . count($responseData['data']));
                
                foreach ($responseData['data'] as $index => $record) {
                    $this->line("      " . ($index + 1) . ". Usuario: {$record['user']} | Ruta: {$record['route_name']} | Estado: {$record['status']}");
                }
            } else {
                $this->error("   ❌ API falló: " . ($responseData['message'] ?? 'Error desconocido'));
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error en API: " . $e->getMessage());
        }

        // Probar API apiListByRoute para cada ruta
        $this->info("\n🌐 Probando /results/users-by-route (apiListByRoute):");
        foreach ($userRoutes as $route) {
            $this->line("\n   🔍 Probando ruta {$route->tried} (ID: {$route->id}):");
            
            try {
                $controller = new \App\Http\Controllers\UserController();
                $request = new \Illuminate\Http\Request();
                $request->merge(['route_id' => $route->id]);
                
                $response = $controller->apiListByRoute($request);
                $responseData = json_decode($response->getContent(), true);
                
                if ($responseData['status'] === 200) {
                    $this->info("      ✅ API exitosa");
                    $this->info("      📊 Registros devueltos: " . count($responseData['data']));
                    
                    foreach ($responseData['data'] as $index => $record) {
                        $this->line("         " . ($index + 1) . ". Usuario: {$record['user']} | Ruta: {$record['route_name']} | Estado: {$record['status']}");
                    }
                } else {
                    $this->error("      ❌ API falló: " . ($responseData['message'] ?? 'Error desconocido'));
                }
            } catch (\Exception $e) {
                $this->error("      ❌ Error en API: " . $e->getMessage());
            }
        }

        // Cerrar sesión
        Auth::logout();

        $this->info("\n🎉 Prueba completada");
        return 0;
    }
}
