<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestOpenRouter extends Command
{
    protected $signature = 'test:openrouter';
    protected $description = 'Probar OpenRouter API directamente';

    public function handle()
    {
        $this->info('🧪 Probando OpenRouter...');
        
        $apiKey = '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';
        
        if (empty($apiKey)) {
            $this->error('❌ No hay API key configurada');
            return 1;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'meta-llama/llama-3-8b-instruct',
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un asistente IA profesional, responde en español de manera natural y concisa.'],
                    ['role' => 'user', 'content' => 'Hola, ¿cómo estás?']
                ],
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['choices'][0]['message']['content'] ?? 'No se recibió respuesta.';
                $this->info('✅ OpenRouter funcionando correctamente');
                $this->line("   Respuesta: " . $text);
                $this->line("   Modelo: meta-llama/llama-3-8b-instruct");
            } else {
                $this->error('❌ Error en OpenRouter');
                $this->line("   Status: {$response->status()}");
                $this->line("   Body: {$response->body()}");
            }
        } catch (\Exception $e) {
            $this->error('❌ Error conectando a OpenRouter');
            $this->line("   Error: {$e->getMessage()}");
        }

        return 0;
    }
} 