<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Traceability;
use Illuminate\Support\Facades\Auth;

class TestScenariosAPI extends Command
{
    protected $signature = 'test:scenarios-api {user_id}';
    protected $description = 'Test scenarios data in results API';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuario con ID {$userId} no encontrado");
            return 1;
        }

        $this->info("🧪 Probando API de escenarios para usuario: {$user->user} (ID: {$user->id}, Rol: {$user->role})");

        // Simular autenticación
        Auth::login($user);

        // Obtener rutas del usuario
        $userRoutes = Traceability::where('user_id', $user->id)->get();
        
        if ($userRoutes->count() == 0) {
            $this->warn("El usuario no tiene rutas");
            Auth::logout();
            return 0;
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
                
                // Filtrar solo las rutas del usuario actual
                $userRoutesInResponse = array_filter($responseData['data'], function($record) use ($user) {
                    return $record['id'] == $user->id;
                });
                
                foreach ($userRoutesInResponse as $index => $record) {
                    $this->line("      " . ($index + 1) . ". Ruta: {$record['route_name']} | Estado: {$record['status']}");
                    
                    if (isset($record['scenarios']) && is_array($record['scenarios'])) {
                        $this->info("         📋 Escenarios: " . count($record['scenarios']));
                        
                        foreach ($record['scenarios'] as $scenario) {
                            $this->line("            - Escenario {$scenario['num_scenario']}: {$scenario['titulo']}");
                            
                            if (isset($scenario['hypotheses']) && is_array($scenario['hypotheses'])) {
                                $this->line("              Hipótesis: " . count($scenario['hypotheses']));
                                foreach ($scenario['hypotheses'] as $hypothesis) {
                                    $this->line("                * " . substr($hypothesis, 0, 50) . "...");
                                }
                            } else {
                                $this->line("              Hipótesis: 0");
                            }
                            
                            if (isset($scenario['years_content']) && is_array($scenario['years_content'])) {
                                $this->line("              Años con contenido: " . count($scenario['years_content']));
                            } else {
                                $this->line("              Años con contenido: 0");
                            }
                        }
                    } else {
                        $this->line("         📋 Escenarios: 0");
                    }
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
