<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckOllama extends Command
{
    
    protected $signature = 'ollama:check';

    protected $description = 'Verificar si Ollama está funcionando correctamente';

    public function handle()
    {
        $this->info('Verificando estado de Ollama...');
        
        $ollamaUrl = 'http:
        
        try {
            
            $this->info('1. Verificando conectividad...');
            $response = Http::timeout(5)->get($ollamaUrl . '/api/tags');
            
            if ($response->successful()) {
                $this->info('✅ Ollama está respondiendo correctamente');
                
                $models = $response->json();
                if (isset($models['models'])) {
                    $this->info('2. Modelos disponibles:');
                    foreach ($models['models'] as $model) {
                        $this->line("   - {$model['name']} (tamaño: {$model['size']})");
                    }
                }

                $this->info('3. Verificando modelo gemma3:4b...');
                $modelExists = false;
                if (isset($models['models'])) {
                    foreach ($models['models'] as $model) {
                        if ($model['name'] === 'gemma3:4b') {
                            $modelExists = true;
                            break;
                        }
                    }
                }
                
                if ($modelExists) {
                    $this->info('✅ Modelo gemma3:4b está disponible');
                } else {
                    $this->warn('⚠️  Modelo gemma3:4b no está disponible');
                    $this->info('Para instalar el modelo, ejecuta: ollama pull gemma3:4b');
                }

                $this->info('4. Probando generación...');
                $testResponse = Http::timeout(30)->post($ollamaUrl . '/api/generate', [
                    'model' => 'gemma3:4b',
                    'prompt' => 'Hola, ¿cómo estás?',
                    'stream' => false,
                    'options' => [
                        'temperature' => 0.7,
                        'top_p' => 0.9,
                        'max_tokens' => 50
                    ]
                ]);
                
                if ($testResponse->successful()) {
                    $data = $testResponse->json();
                    $this->info('✅ Generación exitosa');
                    $this->line("   Respuesta: {$data['response']}");
                } else {
                    $this->error('❌ Error en generación');
                    $this->line("   Status: {$testResponse->status()}");
                    $this->line("   Body: {$testResponse->body()}");
                }
                
            } else {
                $this->error('❌ Ollama no está respondiendo correctamente');
                $this->line("   Status: {$response->status()}");
                $this->line("   Body: {$response->body()}");
            }
            
        } catch (\Exception $e) {
            $this->error('❌ No se puede conectar a Ollama');
            $this->line("   Error: {$e->getMessage()}");
            $this->info('');
            $this->info('Para solucionar esto:');
            $this->line('1. Instala Ollama desde https:
            $this->line('2. Ejecuta: ollama serve');
            $this->line('3. En otra terminal, ejecuta: ollama pull gemma3:4b');
        }
        
        return 0;
    }
}