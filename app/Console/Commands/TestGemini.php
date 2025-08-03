<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestGemini extends Command
{
    protected $signature = 'test:gemini';
    protected $description = 'Probar Gemini API';

    public function handle()
    {
        $this->info('🧪 Probando Gemini...');
        
        $apiKey = env('GEMINI_API_KEY');
        if (empty($apiKey)) {
            $this->error('❌ No hay API key configurada');
            return 1;
        }
        
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=' . $apiKey;
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->withoutVerifying()->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => 'Eres un asistente IA amigable y útil. Responde en español de manera natural y concisa. Hola, ¿cómo estás?'
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192,
                    'candidateCount' => 1
                ]
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $this->info('✅ Gemini funcionando correctamente');
                    $this->line("   Respuesta: " . $data['candidates'][0]['content']['parts'][0]['text']);
                    $this->line("   Modelo: " . ($data['model'] ?? 'gemini-pro'));
                } else {
                    $this->error('❌ Estructura de respuesta inesperada');
                    $this->line("   Data: " . json_encode($data));
                }
            } else {
                $this->error('❌ Error en Gemini');
                $this->line("   Status: {$response->status()}");
                $this->line("   Body: {$response->body()}");
            }
        } catch (\Exception $e) {
            $this->error('❌ Error conectando a Gemini');
            $this->line("   Error: {$e->getMessage()}");
        }
        
        return 0;
    }
} 