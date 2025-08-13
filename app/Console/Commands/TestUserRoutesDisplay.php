<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Traceability;
use Illuminate\Support\Facades\Auth;

class TestUserRoutesDisplay extends Command
{
    protected $signature = 'test:user-routes-display {user_id}';
    protected $description = 'Test that a user can see all their routes in results';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return 1;
        }

        $this->info("🧪 Probando visualización de rutas para usuario: {$user->user} (ID: {$user->id}, Rol: {$user->role})");

        // Simular autenticación
        Auth::login($user);

        // Obtener todas las rutas del usuario
        $userRoutes = Traceability::where('user_id', $user->id)->get();
        
        $this->info("\n📊 Rutas del usuario:");
        foreach ($userRoutes as $route) {
            $this->line("   - Ruta {$route->tried} (ID: {$route->id})");
        }

        // Probar API apiList (que es la que usa el frontend para usuarios no admin)
        $this->info("\n🌐 Probando /results/users (apiList):");
        try {
            $controller = new \App\Http\Controllers\UserController();
            $request = new \Illuminate\Http\Request();
            
            $response = $controller->apiList($request);
            $responseData = json_decode($response->getContent(), true);
            
            if ($responseData['status'] === 200) {
                $this->info("   ✅ API exitosa");
                $this->info("   📊 Registros devueltos: " . count($responseData['data']));
                
                // Filtrar solo las rutas del usuario actual
                $userRoutesInResponse = array_filter($responseData['data'], function($record) use ($user) {
                    return $record['id'] == $user->id;
                });
                
                $this->info("   📋 Rutas del usuario en la respuesta: " . count($userRoutesInResponse));
                
                foreach ($userRoutesInResponse as $index => $record) {
                    $this->line("      " . ($index + 1) . ". Ruta: {$record['route_name']} | Estado: {$record['status']}");
                }
                
                // Verificar que se muestran todas las rutas
                if (count($userRoutesInResponse) === $userRoutes->count()) {
                    $this->info("   ✅ Se muestran todas las rutas del usuario");
                } else {
                    $this->warn("   ⚠️  No se muestran todas las rutas del usuario");
                    $this->warn("      Esperado: {$userRoutes->count()}, Obtenido: " . count($userRoutesInResponse));
                }
            } else {
                $this->error("   ❌ API falló: " . ($responseData['message'] ?? 'Error desconocido'));
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
