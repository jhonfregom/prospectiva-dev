<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestOllamaProxy extends Command
{
    protected $signature = 'test:ollama-proxy';
    protected $description = 'Probar el proxy de Ollama';

    public function handle()
    {
        $this->info('🧪 Probando proxy de Ollama...');

        try {
            // Simular una petición al proxy
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('http://localhost/ollama/generate', [
                'model' => 'gemma3:4b',
                'prompt' => 'Hola, ¿cómo estás?',
                'stream' => false,
                'options' => [
                    'temperature' => 0.3,
                    'top_p' => 0.7,
                    'max_tokens' => 100,
                    'num_predict' => 50,
                    'top_k' => 15,
                    'repeat_penalty' => 1.05
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ Proxy funcionando correctamente');
                $this->line("   Respuesta: " . ($data['response'] ?? 'Sin respuesta'));
            } else {
                $this->error('❌ Error en el proxy');
                $this->line("   Status: {$response->status()}");
                $this->line("   Body: {$response->body()}");
            }

        } catch (\Exception $e) {
            $this->error('❌ Error conectando al proxy');
            $this->line("   Error: {$e->getMessage()}");
        }

        return 0;
    }
} 