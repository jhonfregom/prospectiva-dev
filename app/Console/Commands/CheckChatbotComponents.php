<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckChatbotComponents extends Command
{
    protected $signature = 'check:chatbot-components';
    protected $description = 'Verificar componentes de chatbot y sus configuraciones';

    public function handle()
    {
        $this->info('🔍 Verificando componentes de chatbot...');

        $this->info("\n📋 FloatingChatbotComponent:");
        $chatbotPath = resource_path('js/components/app/ui/FloatingChatbotComponent.vue');
        if (File::exists($chatbotPath)) {
            $content = File::get($chatbotPath);

            if (strpos($content, 'localhost:11434') !== false) {
                $this->error("   ❌ Aún usa localhost:11434");
            } else {
                $this->info("   ✅ Usa proxy de Laravel");
            }
            
            if (strpos($content, '/ollama/generate') !== false) {
                $this->info("   ✅ URL correcta: /ollama/generate");
            } else {
                $this->error("   ❌ URL incorrecta");
            }

            if (strpos($content, 'handleKeydown') !== false) {
                $this->info("   ✅ Método handleKeydown presente");
            } else {
                $this->warn("   ⚠️  Método handleKeydown no encontrado");
            }
        } else {
            $this->error("   ❌ Archivo no encontrado");
        }

        $this->info("\n📋 FloatingBubbleComponent:");
        $bubblePath = resource_path('js/components/app/ui/FloatingBubbleComponent.vue');
        if (File::exists($bubblePath)) {
            $content = File::get($bubblePath);

            if (strpos($content, 'localhost:11434') !== false) {
                $this->error("   ❌ Aún usa localhost:11434");
            } else {
                $this->info("   ✅ Usa proxy de Laravel");
            }
            
            if (strpos($content, '/ollama/generate') !== false) {
                $this->info("   ✅ URL correcta: /ollama/generate");
            } else {
                $this->error("   ❌ URL incorrecta");
            }

            if (strpos($content, 'handleAIKeydown') !== false) {
                $this->info("   ✅ Método handleAIKeydown presente");
            } else {
                $this->warn("   ⚠️  Método handleAIKeydown no encontrado");
            }
        } else {
            $this->error("   ❌ Archivo no encontrado");
        }

        $this->info("\n🌐 Rutas de Ollama:");
        $routes = \Route::getRoutes();
        $ollamaRoutes = [];
        foreach ($routes as $route) {
            if (strpos($route->uri(), 'ollama') !== false) {
                $ollamaRoutes[] = [
                    'method' => $route->methods()[0],
                    'uri' => $route->uri(),
                    'name' => $route->getName()
                ];
            }
        }
        
        if (!empty($ollamaRoutes)) {
            foreach ($ollamaRoutes as $route) {
                $this->info("   ✅ {$route['method']} {$route['uri']} ({$route['name']})");
            }
        } else {
            $this->error("   ❌ No se encontraron rutas de Ollama");
        }

        $this->info("\n🎮 Controlador OllamaProxyController:");
        $controllerPath = app_path('Http/Controllers/OllamaProxyController.php');
        if (File::exists($controllerPath)) {
            $this->info("   ✅ Controlador existe");
            
            $content = File::get($controllerPath);
            if (strpos($content, 'generate') !== false) {
                $this->info("   ✅ Método generate presente");
            } else {
                $this->error("   ❌ Método generate no encontrado");
            }
        } else {
            $this->error("   ❌ Controlador no encontrado");
        }

        $this->info("\n🎯 Recomendaciones:");
        $this->info("   1. Limpia la caché del navegador (Ctrl+F5)");
        $this->info("   2. Verifica que no haya errores en la consola del navegador");
        $this->info("   3. Asegúrate de que Ollama esté ejecutándose");

        return 0;
    }
}