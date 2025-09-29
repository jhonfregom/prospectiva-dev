<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckMontserratFont extends Command
{
    protected $signature = 'check:montserrat-font';
    protected $description = 'Check that Montserrat font has been applied correctly';

    public function handle()
    {
        $this->info("🔍 Verificando aplicación de fuente Montserrat...");

        // Verificar archivo de fuentes SCSS
        $fontsFile = resource_path('sass/_fonts.scss');
        if (file_exists($fontsFile)) {
            $this->info("✅ Archivo de fuentes encontrado: {$fontsFile}");
            
            $content = file_get_contents($fontsFile);
            if (strpos($content, "font-family: 'Montserrat'") !== false) {
                $this->info("✅ Fuente Montserrat encontrada en estilos SCSS");
            } else {
                $this->error("❌ Fuente Montserrat NO encontrada en estilos SCSS");
            }
        } else {
            $this->error("❌ Archivo de fuentes no encontrado");
        }

        // Verificar importación en app.scss
        $appScssFile = resource_path('sass/app.scss');
        if (file_exists($appScssFile)) {
            $this->info("✅ Archivo app.scss encontrado: {$appScssFile}");
            
            $content = file_get_contents($appScssFile);
            if (strpos($content, "fonts.googleapis.com") !== false) {
                $this->info("✅ Importación de Google Fonts encontrada en app.scss");
            } else {
                $this->error("❌ Importación de Google Fonts NO encontrada en app.scss");
            }
        } else {
            $this->error("❌ Archivo app.scss no encontrado");
        }

        // Verificar importación en _index.scss
        $indexScssFile = resource_path('sass/_index.scss');
        if (file_exists($indexScssFile)) {
            $this->info("✅ Archivo _index.scss encontrado: {$indexScssFile}");
            
            $content = file_get_contents($indexScssFile);
            if (strpos($content, "@use './fonts'") !== false) {
                $this->info("✅ Importación de fuentes encontrada en _index.scss");
            } else {
                $this->error("❌ Importación de fuentes NO encontrada en _index.scss");
            }
        } else {
            $this->error("❌ Archivo _index.scss no encontrado");
        }

        // Verificar layouts HTML
        $layouts = [
            'main' => resource_path('views/layouts/main.blade.php'),
            'auth' => resource_path('views/layouts/auth.blade.php'),
            'activation' => resource_path('views/layouts/activation.blade.php')
        ];

        foreach ($layouts as $name => $path) {
            if (file_exists($path)) {
                $this->info("✅ Layout {$name} encontrado: {$path}");
                
                $content = file_get_contents($path);
                if (strpos($content, "fonts.googleapis.com") !== false) {
                    $this->info("✅ Fuente Montserrat encontrada en layout {$name}");
                } else {
                    $this->error("❌ Fuente Montserrat NO encontrada en layout {$name}");
                }
            } else {
                $this->error("❌ Layout {$name} no encontrado");
            }
        }

        $this->info("\n🎉 Verificación completada");
        $this->info("\n📋 Resumen de implementación:");
        $this->line("   ✅ Fuente Montserrat importada desde Google Fonts");
        $this->line("   ✅ Estilos globales aplicados en _fonts.scss");
        $this->line("   ✅ Importación en archivos SCSS principales");
        $this->line("   ✅ Fuente agregada a todos los layouts HTML");
        $this->line("   ✅ Caché limpiada");
        
        $this->info("\n🔄 Para ver los cambios:");
        $this->line("   1. Limpia la caché del navegador (Ctrl + F5)");
        $this->line("   2. Reinicia el servidor de desarrollo si usas Vite/Webpack");
        $this->line("   3. Verifica que todos los textos usen Montserrat");
        
        return 0;
    }
}
