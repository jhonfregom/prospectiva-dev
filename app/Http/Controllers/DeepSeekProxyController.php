<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekProxyController extends Controller
{
    protected $baseUrl = 'https:
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.deepseek.api_key', env('DEEPSEEK_API_KEY'));
    }

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'prompt' => 'required|string|max:4000',
                'model' => 'string|in:deepseek-chat,deepseek-coder',
                'max_tokens' => 'integer|min:1|max:2000',
                'temperature' => 'numeric|min:0|max:2'
            ]);

            $prompt = $request->input('prompt');
            $model = $request->input('model', 'deepseek-chat');
            $maxTokens = $request->input('max_tokens', 1000);
            $temperature = $request->input('temperature', 0.7);

            if (empty($this->apiKey)) {
                return $this->useFallback($prompt, $maxTokens, $temperature);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->withoutVerifying()->post($this->baseUrl, [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                                                 'content' => 'Eres ProspecIA, un asistente especializado en prospectiva y análisis estratégico. Responde en español de manera natural y concisa.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'stream' => false
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'response' => $data['choices'][0]['message']['content'] ?? 'No se recibió respuesta.',
                    'model' => $model,
                    'provider' => 'deepseek'
                ]);
            } else {
                Log::error('DeepSeek API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                $errorData = $response->json();
                if ($response->status() === 429 || $response->status() === 402) {
                    $errorMessage = isset($errorData['error']['message']) && $errorData['error']['message'] === 'Insufficient Balance' 
                        ? 'Tu cuenta de DeepSeek tiene saldo insuficiente. Para usar DeepSeek, necesitas agregar créditos o usar Ollama (local) que es completamente gratuito.'
                        : 'Tu cuenta de DeepSeek ha agotado los créditos gratuitos. Para usar DeepSeek, necesitas agregar créditos o usar Ollama (local) que es completamente gratuito.';
                    
                    return response()->json([
                        'success' => true,
                        'response' => $errorMessage,
                        'model' => $model,
                        'provider' => 'deepseek-quota-exceeded'
                    ]);
                }

                return $this->useFallback($prompt, $maxTokens, $temperature);
            }

        } catch (\Exception $e) {
            Log::error('DeepSeek Proxy error', ['error' => $e->getMessage()]);

            return $this->useFallback($request->input('prompt'), 1000, 0.7);
        }
    }

    protected function useFallback($prompt, $maxTokens, $temperature)
    {
        return response()->json([
            'success' => true,
            'response' => 'Lo siento, los servicios de IA no están disponibles en este momento. Te sugiero usar el chatbot local (Ollama) como alternativa.',
            'model' => 'fallback',
            'provider' => 'local'
        ]);
    }

    public function healthCheck()
    {
        return response()->json([
            'status' => 'healthy',
            'provider' => 'deepseek',
            'available' => !empty($this->apiKey)
        ]);
    }
}