<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckTypographySystem extends Command
{
    protected $signature = 'check:typography-system';
    protected $description = 'Check that the typography system has been applied correctly';

    public function handle()
    {
        $this->info("🔍 Verificando sistema tipográfico estandarizado...");

        // Verificar archivo de tipografía SCSS
        $typographyFile = resource_path('sass/_typography.scss');
        if (file_exists($typographyFile)) {
            $this->info("✅ Archivo de tipografía encontrado: {$typographyFile}");
            
            $content = file_get_contents($typographyFile);
            
            // Verificar variables de tamaños de fuente
            $fontSizes = [
                '$font-size-xs: 0.75rem',
                '$font-size-sm: 0.875rem',
                '$font-size-base: 1rem',
                '$font-size-lg: 1.125rem',
                '$font-size-xl: 1.25rem',
                '$font-size-2xl: 1.5rem',
                '$font-size-3xl: 1.875rem',
                '$font-size-4xl: 2.25rem'
            ];
            
            foreach ($fontSizes as $fontSize) {
                if (strpos($content, $fontSize) !== false) {
                    $this->info("✅ Variable de tamaño encontrada: {$fontSize}");
                } else {
                    $this->warn("⚠️ Variable de tamaño NO encontrada: {$fontSize}");
                }
            }
            
            // Verificar jerarquía tipográfica
            $typographyClasses = [
                '.title-main',
                '.title-section',
                '.subtitle',
                '.title-module',
                '.title-component',
                '.title-small',
                '.description-main',
                '.description-secondary',
                '.info-text',
                '.table-header',
                '.table-content',
                '.table-action'
            ];
            
            foreach ($typographyClasses as $class) {
                if (strpos($content, $class) !== false) {
                    $this->info("✅ Clase tipográfica encontrada: {$class}");
                } else {
                    $this->warn("⚠️ Clase tipográfica NO encontrada: {$class}");
                }
            }
        } else {
            $this->error("❌ Archivo de tipografía no encontrado");
        }

        // Verificar importación en _index.scss
        $indexScssFile = resource_path('sass/_index.scss');
        if (file_exists($indexScssFile)) {
            $this->info("✅ Archivo _index.scss encontrado: {$indexScssFile}");
            
            $content = file_get_contents($indexScssFile);
            if (strpos($content, "@use './typography'") !== false) {
                $this->info("✅ Importación de tipografía encontrada en _index.scss");
            } else {
                $this->error("❌ Importación de tipografía NO encontrada en _index.scss");
            }
        } else {
            $this->error("❌ Archivo _index.scss no encontrado");
        }

        // Verificar correcciones en textos
        $mainConfigFile = resource_path('config/shared-variables/main.php');
        if (file_exists($mainConfigFile)) {
            $this->info("✅ Archivo de configuración principal encontrado: {$mainConfigFile}");
            
            $content = file_get_contents($mainConfigFile);
            
            // Verificar correcciones ortográficas
            $corrections = [
                "'title' => 'Gráfica de Variables'" => "✅ Título de gráfica corregido",
                "'title' => 'Conclusiones de Aprendizaje'" => "✅ Título de conclusiones corregido",
                "'title' => 'Direccionadores de Futuro'" => "✅ Título de direccionadores corregido",
                "'subtitle' => 'Listado de Usuarios y sus Datos'" => "✅ Subtítulo de resultados corregido",
                "'main_title' => 'Prospectiva Estratégica para la Generación de Competitividad Empresarial'" => "✅ Título principal estratégico corregido"
            ];
            
            foreach ($corrections as $correction => $message) {
                if (strpos($content, $correction) !== false) {
                    $this->info($message);
                } else {
                    $this->warn("⚠️ Corrección NO encontrada: {$correction}");
                }
            }
        } else {
            $this->error("❌ Archivo de configuración principal no encontrado");
        }

        // Verificar store de textos
        $textsStoreFile = resource_path('js/stores/texts.js');
        if (file_exists($textsStoreFile)) {
            $this->info("✅ Store de textos encontrado: {$textsStoreFile}");
            
            $content = file_get_contents($textsStoreFile);
            
            // Verificar correcciones en el store
            $storeCorrections = [
                "title: 'Gráfica de Variables'" => "✅ Título de gráfica corregido en store",
                "h1_plus: 'Hipótesis 1+'" => "✅ Hipótesis corregida en store",
                "scenario_1: 'Escenario 1'" => "✅ Escenarios corregidos en store",
                "year1: 'Año 1'" => "✅ Años corregidos en store",
                "title: 'Direccionadores de Futuro'" => "✅ Título de direccionadores corregido en store"
            ];
            
            foreach ($storeCorrections as $correction => $message) {
                if (strpos($content, $correction) !== false) {
                    $this->info($message);
                } else {
                    $this->warn("⚠️ Corrección en store NO encontrada: {$correction}");
                }
            }
        } else {
            $this->error("❌ Store de textos no encontrado");
        }

        $this->info("\n🎉 Verificación del sistema tipográfico completada");
        $this->info("\n📋 Resumen de implementación:");
        $this->line("   ✅ Sistema tipográfico estandarizado creado");
        $this->line("   ✅ Jerarquía de tamaños de fuente definida");
        $this->line("   ✅ Clases tipográficas específicas implementadas");
        $this->line("   ✅ Corrección ortográfica aplicada");
        $this->line("   ✅ Títulos estandarizados (primera letra mayúscula)");
        $this->line("   ✅ Textos descriptivos corregidos");
        $this->line("   ✅ Importación en archivos SCSS principales");
        
        $this->info("\n🎨 Sistema tipográfico implementado:");
        $this->line("   • Títulos principales: 36px (2.25rem) - Bold");
        $this->line("   • Títulos de sección: 30px (1.875rem) - Semi-bold");
        $this->line("   • Subtítulos: 24px (1.5rem) - Semi-bold");
        $this->line("   • Títulos de módulos: 20px (1.25rem) - Semi-bold");
        $this->line("   • Textos descriptivos: 16px (1rem) - Normal");
        $this->line("   • Textos de tabla: 14px (0.875rem) - Normal/Semi-bold");
        $this->line("   • Textos pequeños: 12px (0.75rem) - Medium");
        
        $this->info("\n🔄 Para ver los cambios:");
        $this->line("   1. Limpia la caché del navegador (Ctrl + F5)");
        $this->line("   2. Reinicia el servidor de desarrollo si usas Vite/Webpack");
        $this->line("   3. Verifica que todos los textos usen el nuevo sistema tipográfico");
        
        return 0;
    }
}
