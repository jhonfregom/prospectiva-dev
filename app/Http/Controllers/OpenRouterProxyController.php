<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterProxyController extends Controller
{
    protected $baseUrl = 'https://openrouter.ai/api/v1';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key', env('OPENROUTER_API_KEY'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:8000',
            'model' => 'string|nullable',
            'temperature' => 'numeric|min:0|max:2'
        ]);

        $prompt = $request->input('prompt');
        $model = $request->input('model', 'meta-llama/llama-3-8b-instruct');
        $temperature = $request->input('temperature', 0.7);

        if (empty($this->apiKey)) {
            return response()->json([
                'success' => false,
                'response' => 'No hay API key de OpenRouter configurada.',
                'provider' => 'openrouter'
            ]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->withoutVerifying()->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                                         ['role' => 'system', 'content' => 'Eres ProspecIA, un asistente especializado en prospectiva y análisis estratégico. Responde en español de manera natural y concisa.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $temperature,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['choices'][0]['message']['content'] ?? 'No se recibió respuesta.';
                return response()->json([
                    'success' => true,
                    'response' => $text,
                    'model' => $model,
                    'provider' => 'openrouter'
                ]);
            } else {
                Log::error('OpenRouter API error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json([
                    'success' => false,
                    'response' => 'Error al conectar con OpenRouter: ' . $response->body(),
                    'provider' => 'openrouter'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('OpenRouter Proxy error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'response' => 'Error inesperado: ' . $e->getMessage(),
                'provider' => 'openrouter'
            ]);
        }
    }

    public function healthCheck()
    {
        return response()->json([
            'status' => 'healthy',
            'provider' => 'openrouter',
            'available' => !empty($this->apiKey)
        ]);
    }
}