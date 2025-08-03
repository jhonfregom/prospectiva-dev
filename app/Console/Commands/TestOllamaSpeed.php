<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestOllamaSpeed extends Command
{
    protected $signature = 'ollama:speed-test';
    protected $description = 'Probar la velocidad de respuesta de Ollama';

    public function handle()
    {
        $this->info('🚀 Probando velocidad de Ollama...');
        
        $ollamaUrl = 'http://localhost:11434/api/generate';
        
        // Configuración optimizada para velocidad
        $fastConfig = [
            'model' => 'gemma3:4b',
            'prompt' => 'Hola',
            'stream' => false,
            'options' => [
                'temperature' => 0.2,
                'top_p' => 0.6,
                'max_tokens' => 50,
                'num_predict' => 50,
                'top_k' => 10,
                'repeat_penalty' => 1.0
            ]
        ];
        
        $this->info('⏱️  Iniciando prueba de velocidad...');
        
        $startTime = microtime(true);
        
        try {
            $response = Http::timeout(10)->post($ollamaUrl, $fastConfig);
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2); // en milisegundos
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ Respuesta exitosa en {$duration}ms");
                $this->line("📝 Respuesta: {$data['response']}");
                
                if ($duration < 2000) {
                    $this->info("🎉 ¡Excelente velocidad! (< 2 segundos)");
                } elseif ($duration < 5000) {
                    $this->info("👍 Buena velocidad (< 5 segundos)");
                } else {
                    $this->warn("⚠️  Velocidad lenta (> 5 segundos)");
                }
                
            } else {
                $this->error("❌ Error en la respuesta");
                $this->line("Status: {$response->status()}");
                $this->line("Body: {$response->body()}");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error de conexión: {$e->getMessage()}");
        }
        
        return 0;
    }
} 