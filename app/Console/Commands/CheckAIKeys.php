<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckAIKeys extends Command
{
    protected $signature = 'check:ai-keys';
    protected $description = 'Verificar las claves de las APIs de IA';

    public function handle()
    {
        $this->info('🔍 Verificando claves de APIs de IA...');
        $this->info('');

        $openaiKey = config('services.openai.api_key');
        $this->line('🤖 OpenAI (ChatGPT):');
        $this->line('   Config: ' . ($openaiKey ? '✅ Configurada' : '❌ No configurada'));
        $this->line('   ENV: ' . (env('OPENAI_API_KEY') ? '✅ Configurada' : '❌ No configurada'));
        $this->line('');

        $deepseekKey = config('services.deepseek.api_key');
        $this->line('🔍 DeepSeek:');
        $this->line('   Config: ' . ($deepseekKey ? '✅ Configurada' : '❌ No configurada'));
        $this->line('   ENV: ' . (env('DEEPSEEK_API_KEY') ? '✅ Configurada' : '❌ No configurada'));
        $this->line('');

        $geminiKey = config('services.gemini.api_key');
        $this->line('🌟 Gemini:');
        $this->line('   Config: ' . ($geminiKey ? '✅ Configurada' : '❌ No configurada'));
        $this->line('   ENV: ' . (env('GEMINI_API_KEY') ? '✅ Configurada' : '❌ No configurada'));
        $this->line('');

        $openrouterKey = config('services.openrouter.api_key');
        $this->line('🌐 OpenRouter:');
        $this->line('   Config: ' . ($openrouterKey ? '✅ Configurada' : '❌ No configurada'));
        $this->line('   ENV: ' . (env('OPENROUTER_API_KEY') ? '✅ Configurada' : '❌ No configurada'));
        $this->line('');

        $huggingfaceKey = config('services.huggingface.api_key');
        $this->line('🤗 HuggingFace:');
        $this->line('   Config: ' . ($huggingfaceKey ? '✅ Configurada' : '❌ No configurada'));
        $this->line('   ENV: ' . (env('HUGGINGFACE_API_KEY') ? '✅ Configurada' : '❌ No configurada'));
        $this->line('');

        $this->info('📝 Instrucciones:');
        $this->line('1. Crea un archivo .env en la raíz del proyecto');
        $this->line('2. Agrega las claves de las IAs que quieras usar:');
        $this->line('   OPENAI_API_KEY=tu_clave_aqui');
        $this->line('   DEEPSEEK_API_KEY=tu_clave_aqui');
        $this->line('   GEMINI_API_KEY=tu_clave_aqui');
        $this->line('   OPENROUTER_API_KEY=tu_clave_aqui');
        $this->line('   HUGGINGFACE_API_KEY=tu_clave_aqui');
        $this->line('3. Ejecuta: php artisan config:cache');
        $this->line('4. Vuelve a ejecutar este comando para verificar');
        $this->line('');
        $this->info('💡 Nota: Si no configuras ninguna clave, el sistema usará Ollama (local) como fallback.');
    }
}




