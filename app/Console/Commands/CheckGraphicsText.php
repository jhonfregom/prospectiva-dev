<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckGraphicsText extends Command
{
    protected $signature = 'check:graphics-text';
    protected $description = 'Check that the graphics informative text has been added correctly';

    public function handle()
    {
        $this->info("🔍 Verificando texto informativo de gráfica...");

        // Verificar archivo de configuración
        $configFile = resource_path('config/shared-variables/main.php');
        if (file_exists($configFile)) {
            $this->info("✅ Archivo de configuración encontrado: {$configFile}");
            
            $content = file_get_contents($configFile);
            if (strpos($content, "'description' => 'En esta sección visualizarás el resultado de la matriz de relaciones") !== false) {
                $this->info("✅ Texto descriptivo encontrado en configuración");
            } else {
                $this->error("❌ Texto descriptivo NO encontrado en configuración");
            }
        } else {
            $this->error("❌ Archivo de configuración no encontrado");
        }

        // Verificar store de textos
        $storeFile = resource_path('js/stores/texts.js');
        if (file_exists($storeFile)) {
            $this->info("✅ Archivo de store encontrado: {$storeFile}");
            
            $content = file_get_contents($storeFile);
            if (strpos($content, "description: 'En esta sección visualizarás el resultado de la matriz de relaciones") !== false) {
                $this->info("✅ Texto descriptivo encontrado en store");
            } else {
                $this->error("❌ Texto descriptivo NO encontrado en store");
            }
        } else {
            $this->error("❌ Archivo de store no encontrado");
        }

        // Verificar componente Vue
        $componentFile = resource_path('js/components/app/sections/graphics/GraphicsMainComponent.vue');
        if (file_exists($componentFile)) {
            $this->info("✅ Archivo de componente encontrado: {$componentFile}");
            
            $content = file_get_contents($componentFile);
            if (strpos($content, 'info-banner-component') !== false) {
                $this->info("✅ Componente InfoBanner encontrado en template");
            } else {
                $this->error("❌ Componente InfoBanner NO encontrado en template");
            }
            
            if (strpos($content, 'InfoBannerComponent') !== false) {
                $this->info("✅ Importación de InfoBannerComponent encontrada");
            } else {
                $this->error("❌ Importación de InfoBannerComponent NO encontrada");
            }
            
            if (strpos($content, 'graphics.description') !== false) {
                $this->info("✅ Referencia al texto descriptivo encontrada");
            } else {
                $this->error("❌ Referencia al texto descriptivo NO encontrada");
            }
        } else {
            $this->error("❌ Archivo de componente no encontrado");
        }

        $this->info("\n🎉 Verificación completada");
        return 0;
    }
}
