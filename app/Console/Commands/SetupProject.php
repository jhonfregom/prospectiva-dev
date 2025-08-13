<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupProject extends Command
{
    protected $signature = 'setup:project';
    protected $description = 'Setup the complete project from scratch';

    public function handle()
    {
        $this->info('🚀 Configurando proyecto Prospectiva desde cero...');

        // 1. Verificar si .env existe
        if (!file_exists(base_path('.env'))) {
            $this->error('❌ Archivo .env no encontrado. Por favor, copia .env.example a .env y configura tu base de datos.');
            return 1;
        }

        // 2. Generar clave de aplicación
        $this->info('🔑 Generando clave de aplicación...');
        Artisan::call('key:generate');

        // 3. Limpiar cache
        $this->info('🧹 Limpiando cache...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        // 4. Ejecutar migraciones
        $this->info('📊 Ejecutando migraciones...');
        try {
            Artisan::call('migrate:fresh');
            $this->info('✅ Migraciones ejecutadas correctamente');
        } catch (\Exception $e) {
            $this->error('❌ Error ejecutando migraciones: ' . $e->getMessage());
            return 1;
        }

        // 5. Ejecutar seeders
        $this->info('🌱 Ejecutando seeders...');
        try {
            Artisan::call('db:seed');
            $this->info('✅ Seeders ejecutados correctamente');
        } catch (\Exception $e) {
            $this->error('❌ Error ejecutando seeders: ' . $e->getMessage());
            return 1;
        }

        // 6. Verificar instalación
        $this->info('🔍 Verificando instalación...');
        Artisan::call('check:migrations');
        Artisan::call('check:seeders');

        // 7. Verificar permisos
        $this->info('🔐 Verificando permisos...');
        $storagePath = storage_path();
        $bootstrapPath = base_path('bootstrap/cache');
        
        if (!is_writable($storagePath)) {
            $this->warn("⚠️ El directorio storage no es escribible. Ejecuta: chmod -R 775 {$storagePath}");
        } else {
            $this->info('✅ Permisos de storage correctos');
        }

        if (!is_writable($bootstrapPath)) {
            $this->warn("⚠️ El directorio bootstrap/cache no es escribible. Ejecuta: chmod -R 775 {$bootstrapPath}");
        } else {
            $this->info('✅ Permisos de bootstrap/cache correctos');
        }

        $this->info("\n🎉 ¡Proyecto configurado exitosamente!");
        $this->info("\n📋 Próximos pasos:");
        $this->info("   1. Instala dependencias frontend: npm install");
        $this->info("   2. Inicia el servidor de desarrollo: php artisan serve");
        $this->info("   3. En otra terminal: npm run dev");
        $this->info("   4. Abre http://localhost:8000 en tu navegador");

        return 0;
    }
}
