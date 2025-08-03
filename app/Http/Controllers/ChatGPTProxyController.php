<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatGPTProxyController extends Controller
{
    protected $baseUrl = 'https://api.openai.com/v1/chat/completions';
    protected $apiKey;

    public function __construct()
    {
        // Usar una API key gratuita o configurada
        $this->apiKey = config('services.openai.api_key', env('OPENAI_API_KEY'));
    }

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'prompt' => 'required|string|max:4000',
                'model' => 'string|in:gpt-3.5-turbo,gpt-4',
                'max_tokens' => 'integer|min:1|max:2000',
                'temperature' => 'numeric|min:0|max:2'
            ]);

            $prompt = $request->input('prompt');
            $model = $request->input('model', 'gpt-3.5-turbo');
            $maxTokens = $request->input('max_tokens', 1000);
            $temperature = $request->input('temperature', 0.7);

            // Si no hay API key configurada, usar un servicio gratuito alternativo
            if (empty($this->apiKey)) {
                return $this->useFreeAlternative($prompt, $maxTokens, $temperature);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->withoutVerifying()->post($this->baseUrl, [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Eres un asistente IA amigable y útil. Responde en español de manera natural y concisa.'
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
                    'provider' => 'openai'
                ]);
            } else {
                Log::error('ChatGPT API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                // Verificar si es error de cuota agotada
                $errorData = $response->json();
                if ($response->status() === 429 && isset($errorData['error']['type']) && $errorData['error']['type'] === 'insufficient_quota') {
                    return response()->json([
                        'success' => true,
                        'response' => 'Tu cuenta de OpenAI ha agotado los créditos gratuitos. Para usar ChatGPT, necesitas agregar una tarjeta de crédito en https://platform.openai.com/account/billing o usar Ollama (local) que es completamente gratuito.',
                        'model' => 'gpt-3.5-turbo',
                        'provider' => 'openai-quota-exceeded'
                    ]);
                }
                
                // Fallback a servicio gratuito para otros errores
                return $this->useFreeAlternative($prompt, $maxTokens, $temperature);
            }

        } catch (\Exception $e) {
            Log::error('ChatGPT Proxy error', ['error' => $e->getMessage()]);
            
            // Fallback a servicio gratuito
            return $this->useFreeAlternative($request->input('prompt'), 1000, 0.7);
        }
    }

    protected function useFreeAlternative($prompt, $maxTokens, $temperature)
    {
        try {
            // Usar un servicio gratuito alternativo (ejemplo con Hugging Face)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.huggingface.api_key', 'hf_xxx'),
                'Content-Type' => 'application/json',
            ])->timeout(30)->withoutVerifying()->post('https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium', [
                'inputs' => $prompt,
                'parameters' => [
                    'max_length' => $maxTokens,
                    'temperature' => $temperature,
                    'return_full_text' => false
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'response' => $data[0]['generated_text'] ?? 'Respuesta del servicio gratuito.',
                    'model' => 'gpt-free',
                    'provider' => 'huggingface'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Free alternative error', ['error' => $e->getMessage()]);
        }

        // Respuesta de fallback
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
            'provider' => 'chatgpt',
            'available' => !empty($this->apiKey)
        ]);
    }
} 