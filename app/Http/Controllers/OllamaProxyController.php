<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProxyController extends Controller
{
    private $ollamaUrl = 'http:

    public function generate(Request $request): JsonResponse
    {
        try {
            
            $request->validate([
                'model' => 'required|string',
                'prompt' => 'required|string',
                'stream' => 'boolean',
                'options' => 'array'
            ]);

            try {
                $healthCheck = Http::timeout(3)->get($this->ollamaUrl . '/api/tags');
                if (!$healthCheck->successful()) {
                    Log::error('Ollama no está disponible', [
                        'status' => $healthCheck->status(),
                        'body' => $healthCheck->body()
                    ]);
                    
                    return response()->json([
                        'error' => 'Ollama no está disponible',
                        'message' => 'Asegúrate de que Ollama esté ejecutándose en localhost:11434'
                    ], 503);
                }
            } catch (\Exception $e) {
                Log::error('No se puede conectar a Ollama', [
                    'message' => $e->getMessage()
                ]);
                
                return response()->json([
                    'error' => 'No se puede conectar a Ollama',
                    'message' => 'Verifica que Ollama esté instalado y ejecutándose'
                ], 503);
            }

            $ollamaData = [
                'model' => $request->input('model'),
                'prompt' => $request->input('prompt'),
                'stream' => $request->input('stream', false),
            ];

            if ($request->has('options')) {
                $ollamaData['options'] = $request->input('options');
            }

            Log::info('Enviando petición a Ollama', [
                'model' => $ollamaData['model'],
                'prompt_length' => strlen($ollamaData['prompt'])
            ]);

            $response = Http::timeout(45)->post($this->ollamaUrl . '/api/generate', $ollamaData);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Respuesta exitosa de Ollama', [
                    'response_length' => strlen($responseData['response'] ?? '')
                ]);
                return response()->json($responseData);
            } else {
                Log::error('Error en Ollama API', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return response()->json([
                    'error' => 'Error en Ollama API',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error en Ollama Proxy', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function models(): JsonResponse
    {
        try {
            $response = Http::timeout(10)->get($this->ollamaUrl . '/api/tags');

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json([
                    'error' => 'Error al obtener modelos',
                    'status' => $response->status()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error al obtener modelos de Ollama', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al obtener modelos',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}