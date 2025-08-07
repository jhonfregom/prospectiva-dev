<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestOpenAIKey extends Command
{
    protected $signature = 'test:openai-key';
    protected $description = 'Verificar la API key de OpenAI';

    public function handle()
    {
        $this->info('🔑 Verificando API key de OpenAI...');

        $envKey = env('OPENAI_API_KEY');
        $configKey = config('services.openai.api_key');
        
        $this->line("   ENV OPENAI_API_KEY: " . ($envKey ? '✅ Configurada' : '❌ No configurada'));
        $this->line("   CONFIG services.openai.api_key: " . ($configKey ? '✅ Configurada' : '❌ No configurada'));
        
        if ($envKey) {
            $this->line("   Longitud de la key: " . strlen($envKey) . " caracteres");
            $this->line("   Inicio de la key: " . substr($envKey, 0, 10) . "...");
        }

        if (empty($envKey)) {
            $this->error("   ❌ La API key está vacía o no se está leyendo correctamente");
            return 1;
        }
        
        if (empty($configKey)) {
            $this->error("   ❌ La configuración de servicios no está leyendo la key");
            return 1;
        }
        
        $this->info("   ✅ API key configurada correctamente");

        $this->info("\n🧪 Probando llamada a la API...");
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $envKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post('https:
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => 'Hola']
                ],
                'max_tokens' => 10
            ]);
            
            if ($response->successful()) {
                $this->info("   ✅ API funcionando correctamente");
                $data = $response->json();
                $this->line("   Respuesta: " . ($data['choices'][0]['message']['content'] ?? 'Sin respuesta'));
            } else {
                $this->error("   ❌ Error en la API: " . $response->status());
                $this->line("   Body: " . $response->body());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error conectando a la API: " . $e->getMessage());
        }
        
        return 0;
    }
}