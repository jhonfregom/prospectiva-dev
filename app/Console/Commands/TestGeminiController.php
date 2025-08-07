<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GeminiProxyController;
use Illuminate\Http\Request;

class TestGeminiController extends Command
{
    protected $signature = 'test:gemini-controller';
    protected $description = 'Probar el controlador de Gemini directamente';

    public function handle()
    {
        $this->info('🧪 Probando controlador de Gemini directamente...');
        
        try {
            $controller = new GeminiProxyController();
            $request = new Request();
            $request->merge([
                'prompt' => 'Hola, ¿cómo estás?',
                'model' => 'gemini-1.5-pro',
                'temperature' => 0.7
            ]);
            
            $response = $controller->generate($request);
            $data = $response->getData();
            
            $this->info('✅ Controlador funcionando correctamente');
            $this->line("   Respuesta: " . ($data->response ?? 'Sin respuesta'));
            $this->line("   Modelo: " . ($data->model ?? 'No especificado'));
            $this->line("   Proveedor: " . ($data->provider ?? 'No especificado'));
            
        } catch (\Exception $e) {
            $this->error('❌ Error en el controlador');
            $this->line("   Error: {$e->getMessage()}");
        }
        
        return 0;
    }
}