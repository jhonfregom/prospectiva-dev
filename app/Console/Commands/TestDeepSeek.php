<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestDeepSeek extends Command
{
    protected $signature = 'test:deepseek';
    protected $description = 'Probar DeepSeek API';

    public function handle()
    {
        $this->info('🧪 Probando DeepSeek...');
        
        $apiKey = env('DEEPSEEK_API_KEY');
        if (empty($apiKey)) {
            $this->error('❌ No hay API key configurada');
            return 1;
        }
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->withoutVerifying()->post('https:
                'model' => 'deepseek-chat',
                'messages' => [
                    [
                        'role' => 'system',
                                                 'content' => 'Eres ProspecIA, un asistente especializado en prospectiva y análisis estratégico. Responde en español de manera natural y concisa.'
                    ],
                    [
                        'role' => 'user',
                        'content' => 'Hola, ¿cómo estás?'
                    ]
                ],
                'max_tokens' => 100,
                'temperature' => 0.7,
                'stream' => false
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ DeepSeek funcionando correctamente');
                $this->line("   Respuesta: " . ($data['choices'][0]['message']['content'] ?? 'Sin respuesta'));
                $this->line("   Modelo: " . ($data['model'] ?? 'No especificado'));
                $this->line("   Tokens usados: " . ($data['usage']['total_tokens'] ?? 'No especificado'));
            } else {
                $this->error('❌ Error en DeepSeek');
                $this->line("   Status: {$response->status()}");
                $this->line("   Body: {$response->body()}");
            }
        } catch (\Exception $e) {
            $this->error('❌ Error conectando a DeepSeek');
            $this->line("   Error: {$e->getMessage()}");
        }
        
        return 0;
    }
}