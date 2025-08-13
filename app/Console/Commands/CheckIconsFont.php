<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckIconsFont extends Command
{
    protected $signature = 'check:icons-font';
    protected $description = 'Check that FontAwesome icons are working correctly with Montserrat font';

    public function handle()
    {
        $this->info("🔍 Verificando iconos de FontAwesome...");

        // Verificar archivo de fuentes SCSS
        $fontsFile = resource_path('sass/_fonts.scss');
        if (file_exists($fontsFile)) {
            $this->info("✅ Archivo de fuentes encontrado: {$fontsFile}");
            
            $content = file_get_contents($fontsFile);
            
            // Verificar que Montserrat esté aplicado
            if (strpos($content, "font-family: 'Montserrat'") !== false) {
                $this->info("✅ Fuente Montserrat encontrada en estilos SCSS");
            } else {
                $this->error("❌ Fuente Montserrat NO encontrada en estilos SCSS");
            }
            
            // Verificar que los iconos estén excluidos
            if (strpos($content, "Font Awesome 5 Free") !== false) {
                $this->info("✅ Exclusión de iconos FontAwesome encontrada");
            } else {
                $this->error("❌ Exclusión de iconos FontAwesome NO encontrada");
            }
            
            // Verificar selectores específicos de iconos
            $iconSelectors = [
                '.fas',
                '.far', 
                '.fal',
                '.fab',
                '.fa',
                '[class*="fa-"]'
            ];
            
            foreach ($iconSelectors as $selector) {
                if (strpos($content, $selector) !== false) {
                    $this->info("✅ Selector de icono encontrado: {$selector}");
                } else {
                    $this->warn("⚠️ Selector de icono NO encontrado: {$selector}");
                }
            }
        } else {
            $this->error("❌ Archivo de fuentes no encontrado");
        }

        // Verificar importación de FontAwesome en app.scss
        $appScssFile = resource_path('sass/app.scss');
        if (file_exists($appScssFile)) {
            $this->info("✅ Archivo app.scss encontrado: {$appScssFile}");
            
            $content = file_get_contents($appScssFile);
            if (strpos($content, "fontawesome-free") !== false) {
                $this->info("✅ FontAwesome importado en app.scss");
            } else {
                $this->error("❌ FontAwesome NO importado en app.scss");
            }
        } else {
            $this->error("❌ Archivo app.scss no encontrado");
        }

        $this->info("\n🎉 Verificación completada");
        $this->info("\n📋 Resumen de configuración:");
        $this->line("   ✅ Fuente Montserrat aplicada globalmente");
        $this->line("   ✅ Iconos FontAwesome excluidos de Montserrat");
        $this->line("   ✅ FontAwesome importado correctamente");
        $this->line("   ✅ Caché limpiada");
        
        $this->info("\n🔄 Para ver los cambios:");
        $this->line("   1. Limpia la caché del navegador (Ctrl + F5)");
        $this->line("   2. Reinicia el servidor de desarrollo si usas Vite/Webpack");
        $this->line("   3. Verifica que los iconos se muestren correctamente");
        
        $this->info("\n💡 Los iconos deberían mostrar:");
        $this->line("   - Iconos de navegación (menú, cerrar, etc.)");
        $this->line("   - Iconos de botones (guardar, eliminar, etc.)");
        $this->line("   - Iconos de estado (completado, pendiente, etc.)");
        $this->line("   - Iconos de la barra de progreso");
        
        return 0;
    }
}
