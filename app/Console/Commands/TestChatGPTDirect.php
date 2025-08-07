<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ChatGPTProxyController;
use Illuminate\Http\Request;

class TestChatGPTDirect extends Command
{
    protected $signature = 'test:chatgpt-direct';
    protected $description = 'Probar el controlador de ChatGPT directamente';

    public function handle()
    {
        $this->info('🧪 Probando controlador de ChatGPT directamente...');

        try {
            $controller = new ChatGPTProxyController();

            $request = new Request();
            $request->merge([
                'prompt' => 'Hola, ¿cómo estás?',
                'model' => 'gpt-3.5-turbo',
                'max_tokens' => 100,
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