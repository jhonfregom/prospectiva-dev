<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OllamaProxyController extends Controller
{
    private const OLLAMA_URL = 'http://localhost:11434';
    private const HEALTH_CHECK_TIMEOUT = 3;
    private const GENERATE_TIMEOUT = 45;
    private const MODELS_TIMEOUT = 10;
    private const CACHE_TTL = 1800; // 30 minutos

    public function generate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'model' => 'required|string|max:100',
                'prompt' => 'required|string|max:10000|min:1',
                'stream' => 'boolean',
                'options' => 'array',
                'options.temperature' => 'nullable|numeric|min:0|max:2',
                'options.top_p' => 'nullable|numeric|min:0|max:1',
                'options.max_tokens' => 'nullable|integer|min:1|max:8000',
                'options.num_predict' => 'nullable|integer|min:1|max:8000'
            ]);

            $model = $validated['model'];
            $prompt = trim($validated['prompt']);
            $stream = $validated['stream'] ?? false;
            $options = $validated['options'] ?? [];

            // Verificar salud de Ollama con cache
            $healthStatus = $this->checkOllamaHealth();
            if (!$healthStatus['healthy']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ollama no está disponible',
                    'message' => $healthStatus['message'],
                    'provider' => 'ollama'
                ], 503);
            }

            // Cache key para evitar peticiones duplicadas
            $cacheKey = 'ollama_' . md5($prompt . $model . json_encode($options));
            
            if (!$stream && Cache::has($cacheKey)) {
                $cachedResponse = Cache::get($cacheKey);
                Log::info('Respuesta de Ollama servida desde cache', ['cache_key' => $cacheKey]);
                return response()->json($cachedResponse);
            }

            $ollamaData = [
                'model' => $model,
                'prompt' => $prompt,
                'stream' => $stream,
            ];

            if (!empty($options)) {
                $ollamaData['options'] = $options;
            }

            Log::info('Enviando petición a Ollama', [
                'model' => $model,
                'prompt_length' => strlen($prompt),
                'stream' => $stream,
                'options' => $options
            ]);

            $response = Http::timeout(self::GENERATE_TIMEOUT)
                ->post(self::OLLAMA_URL . '/api/generate', $ollamaData);

            if ($response->successful()) {
                $responseData = $response->json();
                
                $result = [
                    'success' => true,
                    'response' => $responseData['response'] ?? '',
                    'model' => $model,
                    'provider' => 'ollama',
                    'usage' => [
                        'prompt_eval_count' => $responseData['prompt_eval_count'] ?? 0,
                        'eval_count' => $responseData['eval_count'] ?? 0,
                        'total_duration' => $responseData['total_duration'] ?? 0
                    ],
                    'cached' => false
                ];

                // Cachear respuesta no-stream por 30 minutos
                if (!$stream) {
                    Cache::put($cacheKey, $result, now()->addSeconds(self::CACHE_TTL));
                }

                Log::info('Ollama respuesta exitosa', [
                    'model' => $model,
                    'response_length' => strlen($result['response']),
                    'total_duration' => $responseData['total_duration'] ?? 0,
                    'cached' => false
                ]);

                return response()->json($result);
            } else {
                $errorMessage = $this->getOllamaErrorMessage($response->status());
                
                Log::error('Error en Ollama API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'model' => $model,
                    'prompt_length' => strlen($prompt)
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Error en Ollama API',
                    'message' => $errorMessage,
                    'provider' => 'ollama',
                    'status_code' => $response->status()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error en Ollama Proxy', [
                'message' => $e->getMessage(),
                'model' => $request->input('model'),
                'prompt_length' => strlen($request->input('prompt', ''))
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor',
                'message' => 'Error temporal. Intenta nuevamente en unos momentos.',
                'provider' => 'ollama'
            ], 500);
        }
    }

    public function models(): JsonResponse
    {
        try {
            // Cache de modelos por 5 minutos
            $cacheKey = 'ollama_models';
            if (Cache::has($cacheKey)) {
                $cachedModels = Cache::get($cacheKey);
                Log::info('Modelos de Ollama servidos desde cache');
                return response()->json($cachedModels);
            }

            $response = Http::timeout(self::MODELS_TIMEOUT)
                ->get(self::OLLAMA_URL . '/api/tags');

            if ($response->successful()) {
                $models = $response->json();
                
                // Cachear modelos por 5 minutos
                Cache::put($cacheKey, $models, now()->addMinutes(5));
                
                Log::info('Modelos de Ollama obtenidos exitosamente', [
                    'count' => count($models['models'] ?? [])
                ]);
                
                return response()->json($models);
            } else {
                Log::error('Error al obtener modelos de Ollama', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Error al obtener modelos',
                    'message' => 'No se pudieron obtener los modelos disponibles',
                    'provider' => 'ollama'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error al obtener modelos de Ollama', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al obtener modelos',
                'message' => 'Error de conexión con Ollama',
                'provider' => 'ollama'
            ], 500);
        }
    }

    private function checkOllamaHealth(): array
    {
        $cacheKey = 'ollama_health';
        
        // Cache de estado de salud por 30 segundos
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(self::HEALTH_CHECK_TIMEOUT)
                ->get(self::OLLAMA_URL . '/api/tags');
            
            $isHealthy = $response->successful();
            $result = [
                'healthy' => $isHealthy,
                'message' => $isHealthy 
                    ? 'Ollama está funcionando correctamente'
                    : 'Ollama no está respondiendo correctamente'
            ];
            
            Cache::put($cacheKey, $result, now()->addSeconds(30));
            return $result;
            
        } catch (\Exception $e) {
            $result = [
                'healthy' => false,
                'message' => 'No se puede conectar a Ollama. Verifica que esté instalado y ejecutándose.'
            ];
            
            Cache::put($cacheKey, $result, now()->addSeconds(30));
            return $result;
        }
    }

    private function getOllamaErrorMessage(int $status): string
    {
        return match($status) {
            400 => 'Petición inválida. Verifica los parámetros enviados.',
            404 => 'Modelo no encontrado. Verifica que el modelo esté instalado.',
            500 => 'Error interno de Ollama. Verifica los logs del servicio.',
            default => 'Error desconocido en Ollama.'
        };
    }
}