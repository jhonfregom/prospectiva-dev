<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckOpenRouter extends Command
{
    protected $signature = 'openrouter:check';
    protected $description = 'Verificar la configuración y conectividad de OpenRouter';

    public function handle()
    {
        $this->info('🔍 Verificando configuración de OpenRouter...');

        // Verificar API key
        $apiKey = env('OPENROUTER_API_KEY');
        $configKey = config('services.openrouter.api_key');
        
        $this->line("   ENV OPENROUTER_API_KEY: " . ($apiKey ? '✅ Configurada' : '❌ No configurada'));
        $this->line("   CONFIG services.openrouter.api_key: " . ($configKey ? '✅ Configurada' : '❌ No configurada'));
        
        if ($apiKey) {
            $this->line("   Longitud de la key: " . strlen($apiKey) . " caracteres");
            $this->line("   Inicio de la key: " . substr($apiKey, 0, 10) . "...");
        }

        if (empty($apiKey)) {
            $this->error("   ❌ La API key está vacía o no se está leyendo correctamente");
            $this->info("   💡 Para configurar OpenRouter:");
            $this->line("      1. Ve a https://openrouter.ai/");
            $this->line("      2. Crea una cuenta y obtén tu API key");
            $this->line("      3. Agrega OPENROUTER_API_KEY=tu_api_key en tu archivo .env");
            return 1;
        }

        $this->info("\n🧪 Probando conectividad con OpenRouter...");
        
        try {
            // Probar endpoint de salud
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->withoutVerifying()->get('https://openrouter.ai/api/v1/auth/key');

            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ Conexión exitosa con OpenRouter");
                $this->line("   Usuario: " . ($data['user'] ?? 'No especificado'));
                $this->line("   Créditos disponibles: " . ($data['credits'] ?? 'No especificado'));
            } else {
                $this->error("❌ Error en la autenticación: " . $response->status());
                $this->line("   Body: " . $response->body());
                
                if ($response->status() === 401) {
                    $this->error("   🔑 La API key no es válida o ha expirado");
                    $this->info("   💡 Soluciones:");
                    $this->line("      - Verifica que la API key sea correcta");
                    $this->line("      - Regenera la API key en https://openrouter.ai/");
                    $this->line("      - Asegúrate de que la cuenta esté activa");
                }
                return 1;
            }

            $this->info("\n🧪 Probando generación de texto...");
            
            $testResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->withoutVerifying()->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'meta-llama/llama-3-8b-instruct',
                'messages' => [
                    ['role' => 'user', 'content' => 'Hola, ¿cómo estás?']
                ],
                'max_tokens' => 50,
                'temperature' => 0.7
            ]);

            if ($testResponse->successful()) {
                $data = $testResponse->json();
                $this->info("✅ Generación exitosa");
                $this->line("   Respuesta: " . ($data['choices'][0]['message']['content'] ?? 'Sin respuesta'));
                $this->line("   Modelo usado: " . ($data['model'] ?? 'No especificado'));
                $this->line("   Tokens usados: " . ($data['usage']['total_tokens'] ?? 'No especificado'));
            } else {
                $this->error("❌ Error en la generación: " . $testResponse->status());
                $this->line("   Body: " . $testResponse->body());
            }

        } catch (\Exception $e) {
            $this->error("❌ Error de conexión: " . $e->getMessage());
            $this->info("   💡 Posibles soluciones:");
            $this->line("      - Verifica tu conexión a internet");
            $this->line("      - Asegúrate de que OpenRouter esté disponible");
            $this->line("      - Revisa la configuración de firewall/proxy");
        }

        return 0;
    }
}
