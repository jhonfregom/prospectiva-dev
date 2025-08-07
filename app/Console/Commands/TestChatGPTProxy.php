<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestChatGPTProxy extends Command
{
    protected $signature = 'test:chatgpt-proxy';
    protected $description = 'Probar el proxy de ChatGPT';

    public function handle()
    {
        $this->info('🧪 Probando proxy de ChatGPT...');

        try {
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post('http:
                'prompt' => 'Hola, ¿cómo estás?',
                'model' => 'gpt-3.5-turbo',
                'max_tokens' => 100,
                'temperature' => 0.7
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ Proxy funcionando correctamente');
                $this->line("   Respuesta: " . ($data['response'] ?? 'Sin respuesta'));
                $this->line("   Modelo: " . ($data['model'] ?? 'No especificado'));
                $this->line("   Proveedor: " . ($data['provider'] ?? 'No especificado'));
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