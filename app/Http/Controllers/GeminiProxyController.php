<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProxyController extends Controller
{
    protected $baseUrl = 'https:
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
    }

    public function generate(Request $request)
    {
        try {
            $request->validate([
                'prompt' => 'required|string|max:8000',
                'model' => 'string|in:gemini-pro,gemini-pro-vision',
                'temperature' => 'numeric|min:0|max:2'
            ]);

            $prompt = $request->input('prompt');
            $model = $request->input('model', 'gemini-pro');
            $temperature = $request->input('temperature', 0.7);

            if (empty($this->apiKey)) {
                return $this->useFallback($prompt, $temperature);
            }

            $url = $this->baseUrl . '?key=' . $this->apiKey;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)->withoutVerifying()->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                                                 'text' => 'Eres ProspecIA, un asistente especializado en prospectiva y análisis estratégico. Responde en español de manera natural y concisa. ' . $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192, 
                    'candidateCount' => 1
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return response()->json([
                        'success' => true,
                        'response' => $data['candidates'][0]['content']['parts'][0]['text'],
                        'model' => $model,
                        'provider' => 'gemini'
                    ]);
                } else {
                    Log::error('Gemini response structure unexpected', ['data' => $data]);
                    return $this->useFallback($prompt, $temperature);
                }
            } else {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                $errorData = $response->json();
                Log::info('Gemini error data', ['errorData' => $errorData]);
                
                if ($response->status() === 429) {
                    Log::info('Gemini quota exceeded detected');
                    $errorMessage = 'Tu cuenta de Gemini ha agotado los límites gratuitos (cuota diaria/minuto). Para usar Gemini, necesitas esperar o usar Ollama (local) que es completamente gratuito sin límites.';
                    
                    return response()->json([
                        'success' => true,
                        'response' => $errorMessage,
                        'model' => $model,
                        'provider' => 'gemini-quota-exceeded'
                    ]);
                }
                
                Log::info('Gemini falling back to generic error');
                
                return $this->useFallback($prompt, $temperature);
            }

        } catch (\Exception $e) {
            Log::error('Gemini Proxy error', ['error' => $e->getMessage()]);

            return $this->useFallback($request->input('prompt'), 0.7);
        }
    }

    protected function useFallback($prompt, $temperature)
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
            'provider' => 'gemini',
            'available' => !empty($this->apiKey)
        ]);
    }
}