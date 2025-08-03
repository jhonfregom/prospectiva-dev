<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestOllamaLongText extends Command
{
    protected $signature = 'test:ollama-long-text';
    protected $description = 'Probar Ollama con textos largos';

    public function handle()
    {
        $this->info('🧪 Probando Ollama con texto largo...');
        
        $longText = "El ensayo académico es un tipo de texto en el que se reflexiona sobre un tema y se lo analiza e interpreta. Para esto, su autor recurre a argumentos que se sustentan en base a la bibliografía seleccionada para llevar adelante este escrito. Su escritura se inserta en una estructura determinada, con una introducción, un desarrollo y una conclusión.";
        
        $this->line("   Longitud del texto: " . strlen($longText) . " caracteres");
        
        try {
            $response = Http::timeout(60)->post('http://localhost:11434/api/generate', [
                'model' => 'gemma3:4b',
                'prompt' => "Analiza este texto y dime qué corregir: " . $longText,
                'stream' => false,
                'options' => [
                    'temperature' => 0.3,
                    'top_p' => 0.7,
                    'max_tokens' => 8000,
                    'num_predict' => 200,
                    'top_k' => 15,
                    'repeat_penalty' => 1.05
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ Ollama funcionando con texto largo');
                $this->line("   Respuesta: " . ($data['response'] ?? 'Sin respuesta'));
                $this->line("   Tiempo de respuesta: " . ($data['total_duration'] ?? 'No especificado') . " ms");
            } else {
                $this->error('❌ Error en Ollama');
                $this->line("   Status: {$response->status()}");
                $this->line("   Body: {$response->body()}");
            }
        } catch (\Exception $e) {
            $this->error('❌ Error conectando a Ollama');
            $this->line("   Error: {$e->getMessage()}");
        }
        
        return 0;
    }
} 