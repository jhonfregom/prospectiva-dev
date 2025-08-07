<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenRouterProxyController extends Controller
{
    protected const BASE_URL = 'https://openrouter.ai/api/v1';
    protected const DEFAULT_MODEL = 'meta-llama/llama-3-8b-instruct';
    protected const DEFAULT_TEMPERATURE = 0.7;
    protected const MAX_TOKENS = 1000;
    protected const TIMEOUT = 60;
    
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key', env('OPENROUTER_API_KEY'));
    }

    public function generate(Request $request): JsonResponse
    {
        // Validación mejorada
        $validated = $request->validate([
            'prompt' => 'required|string|max:8000|min:1',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:4000'
        ]);

        if (empty($this->apiKey)) {
            Log::warning('OpenRouter API key no configurada');
            return response()->json([
                'success' => false,
                'response' => 'Servicio de IA no disponible. Contacta al administrador.',
                'provider' => 'openrouter',
                'error_code' => 'API_KEY_MISSING'
            ], 503);
        }

        // Configuración optimizada
        $model = $validated['model'] ?? self::DEFAULT_MODEL;
        $temperature = $validated['temperature'] ?? self::DEFAULT_TEMPERATURE;
        $maxTokens = $validated['max_tokens'] ?? self::MAX_TOKENS;
        $prompt = trim($validated['prompt']);

        // Cache key para evitar peticiones duplicadas
        $cacheKey = 'openrouter_' . md5($prompt . $model . $temperature . $maxTokens);
        
        // Verificar cache para respuestas similares
        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);
            Log::info('Respuesta servida desde cache', ['cache_key' => $cacheKey]);
            return response()->json($cachedResponse);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => 'ProspecIA - Sistema de Prospectiva'
            ])
            ->timeout(self::TIMEOUT)
            ->withoutVerifying()
            ->post(self::BASE_URL . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => 'Eres ProspecIA, un asistente especializado en prospectiva y análisis estratégico. Responde en español de manera natural, concisa y profesional. Proporciona análisis basados en evidencia y metodologías de prospectiva.'
                    ],
                    [
                        'role' => 'user', 
                        'content' => $prompt
                    ]
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'top_p' => 0.9,
                'frequency_penalty' => 0.1,
                'presence_penalty' => 0.1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['choices'][0]['message']['content'] ?? 'No se recibió respuesta.';
                $usage = $data['usage'] ?? [];
                
                $result = [
                    'success' => true,
                    'response' => $text,
                    'model' => $model,
                    'provider' => 'openrouter',
                    'usage' => [
                        'prompt_tokens' => $usage['prompt_tokens'] ?? 0,
                        'completion_tokens' => $usage['completion_tokens'] ?? 0,
                        'total_tokens' => $usage['total_tokens'] ?? 0
                    ],
                    'cached' => false
                ];

                // Cachear respuesta por 1 hora para peticiones similares
                Cache::put($cacheKey, $result, now()->addHour());
                
                Log::info('OpenRouter respuesta exitosa', [
                    'model' => $model,
                    'prompt_length' => strlen($prompt),
                    'response_length' => strlen($text),
                    'total_tokens' => $usage['total_tokens'] ?? 0
                ]);

                return response()->json($result);
            } else {
                $errorData = $response->json();
                $errorMessage = $this->getErrorMessage($response->status(), $errorData);
                
                Log::error('OpenRouter API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'model' => $model,
                    'prompt_length' => strlen($prompt)
                ]);

                return response()->json([
                    'success' => false,
                    'response' => $errorMessage,
                    'provider' => 'openrouter',
                    'error_code' => $this->getErrorCode($response->status()),
                    'status_code' => $response->status()
                ], $this->getHttpStatusCode($response->status()));
            }
        } catch (\Exception $e) {
            Log::error('OpenRouter Proxy error', [
                'error' => $e->getMessage(),
                'model' => $model,
                'prompt_length' => strlen($prompt)
            ]);

            return response()->json([
                'success' => false,
                'response' => 'Error temporal del servicio. Intenta nuevamente en unos momentos.',
                'provider' => 'openrouter',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    public function healthCheck(): JsonResponse
    {
        $isHealthy = !empty($this->apiKey);
        
        if ($isHealthy) {
            try {
                // Verificar conectividad real
                $response = Http::timeout(5)
                    ->withoutVerifying()
                    ->get(self::BASE_URL . '/models');
                
                $isHealthy = $response->successful();
            } catch (\Exception $e) {
                $isHealthy = false;
                Log::warning('OpenRouter health check failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'provider' => 'openrouter',
            'available' => $isHealthy,
            'timestamp' => now()->toISOString()
        ]);
    }

    private function getErrorMessage(int $status, array $errorData = []): string
    {
        return match($status) {
            401 => 'Credenciales de API inválidas. Contacta al administrador.',
            429 => 'Límite de peticiones excedido. Intenta nuevamente en unos minutos.',
            500, 502, 503, 504 => 'Servicio temporalmente no disponible. Intenta nuevamente.',
            default => $errorData['error']['message'] ?? 'Error desconocido en el servicio de IA.'
        };
    }

    private function getErrorCode(int $status): string
    {
        return match($status) {
            401 => 'UNAUTHORIZED',
            429 => 'RATE_LIMITED',
            500, 502, 503, 504 => 'SERVICE_UNAVAILABLE',
            default => 'UNKNOWN_ERROR'
        };
    }

    private function getHttpStatusCode(int $apiStatus): int
    {
        return match($apiStatus) {
            401, 403 => 503, // Servicio no disponible para el cliente
            429 => 429, // Mantener rate limit
            500, 502, 503, 504 => 503, // Servicio no disponible
            default => 500
        };
    }
}