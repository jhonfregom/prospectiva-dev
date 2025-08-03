<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterProxyController extends Controller
{
    protected $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = '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';
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
            ])->timeout(60)->post($this->baseUrl, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un asistente IA profesional, responde en español de manera natural y concisa.'],
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