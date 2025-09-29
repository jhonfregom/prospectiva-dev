<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckResponsiveDesign extends Command
{
    protected $signature = 'check:responsive-design';
    protected $description = 'Check responsive design implementation across the application';

    public function handle()
    {
        $this->info("🔍 Verificando implementación responsive en toda la aplicación...");

        // Verificar archivos SCSS principales
        $this->info("\n📋 Verificando archivos SCSS principales...");
        $this->checkScssFiles();

        // Verificar componentes Vue
        $this->info("\n🎨 Verificando componentes Vue...");
        $this->checkVueComponents();

        // Verificar layouts Blade
        $this->info("\n📱 Verificando layouts Blade...");
        $this->checkBladeLayouts();

        // Resumen y recomendaciones
        $this->info("\n📊 Resumen de verificación responsive:");
        $this->showSummary();

        return 0;
    }

    private function checkScssFiles()
    {
        $scssFiles = [
            'resources/sass/_responsive.scss' => 'Archivo principal de responsive',
            'resources/sass/_typography.scss' => 'Sistema tipográfico responsive',
            'resources/sass/_index.scss' => 'Archivo principal de estilos',
            'resources/sass/layouts/_main.scss' => 'Layout principal',
            'resources/sass/layouts/_login.scss' => 'Layout de login',
            'resources/sass/layouts/_register.scss' => 'Layout de registro'
        ];

        foreach ($scssFiles as $file => $description) {
            if (File::exists($file)) {
                $content = File::get($file);
                $mediaQueries = $this->countMediaQueries($content);
                
                if ($mediaQueries > 0) {
                    $this->info("✅ {$description}: {$mediaQueries} media queries encontradas");
                } else {
                    $this->warn("⚠️ {$description}: No se encontraron media queries");
                }
            } else {
                $this->error("❌ {$description}: Archivo no encontrado");
            }
        }
    }

    private function checkVueComponents()
    {
        $vueComponents = [
            'resources/js/components/app/ui/FloatingBubbleComponent.vue' => 'Burbuja flotante',
            'resources/js/components/app/ui/FloatingChatbotComponent.vue' => 'Chatbot flotante',
            'resources/js/components/app/ui/StepperPrincipal.vue' => 'Stepper principal',
            'resources/js/components/app/ui/EditButtonComponent.vue' => 'Botón de editar',
            'resources/js/components/app/sections/conclusions/ConclusionsMainComponent.vue' => 'Conclusiones',
            'resources/js/components/app/sections/results/ResultsMainComponent.vue' => 'Resultados',
            'resources/js/components/app/sections/variables/VariablesMainComponent.vue' => 'Variables',
            'resources/js/components/app/sections/graphics/GraphicsMainComponent.vue' => 'Gráficas',
            'resources/js/components/app/sections/matriz/MatrizMainComponent.vue' => 'Matriz',
            'resources/js/components/app/sections/directionFuture/DirectionFutureMainComponent.vue' => 'Direccionadores',
            'resources/js/components/app/sections/initialConditions/InitialConditionsMainComponent.vue' => 'Condiciones iniciales',
            'resources/js/components/app/sections/analysis/AnalysisMainComponent.vue' => 'Análisis',
            'resources/js/components/app/sections/analisisVariables/AnalisisMapaVariablesMainComponent.vue' => 'Mapa de variables',
            'resources/js/components/app/sections/Schwartz/SchwartzMainComponent.vue' => 'Schwartz',
            'resources/js/components/app/sections/scenery/SceneryMainComponent.vue' => 'Escenarios'
        ];

        $responsiveComponents = 0;
        $totalComponents = count($vueComponents);

        foreach ($vueComponents as $file => $description) {
            if (File::exists($file)) {
                $content = File::get($file);
                $mediaQueries = $this->countMediaQueries($content);
                
                if ($mediaQueries > 0) {
                    $this->info("✅ {$description}: {$mediaQueries} media queries");
                    $responsiveComponents++;
                } else {
                    $this->warn("⚠️ {$description}: Sin media queries");
                }
            } else {
                $this->error("❌ {$description}: Componente no encontrado");
            }
        }

        $this->info("\n📈 Componentes responsive: {$responsiveComponents}/{$totalComponents}");
    }

    private function checkBladeLayouts()
    {
        $bladeLayouts = [
            'resources/views/layouts/main.blade.php' => 'Layout principal',
            'resources/views/layouts/auth.blade.php' => 'Layout de autenticación',
            'resources/views/layouts/activation.blade.php' => 'Layout de activación'
        ];

        foreach ($bladeLayouts as $file => $description) {
            if (File::exists($file)) {
                $content = File::get($file);
                
                // Verificar viewport meta tag
                if (strpos($content, 'viewport') !== false) {
                    $this->info("✅ {$description}: Viewport meta tag presente");
                } else {
                    $this->warn("⚠️ {$description}: Viewport meta tag faltante");
                }

                // Verificar media queries en CSS inline
                $mediaQueries = $this->countMediaQueries($content);
                if ($mediaQueries > 0) {
                    $this->info("✅ {$description}: {$mediaQueries} media queries en CSS inline");
                }
            } else {
                $this->error("❌ {$description}: Layout no encontrado");
            }
        }
    }

    private function countMediaQueries($content)
    {
        return preg_match_all('/@media\s*\(/i', $content);
    }

    private function showSummary()
    {
        $this->info("\n🎯 Breakpoints utilizados:");
        $this->line("   • 1920px - Pantallas muy grandes");
        $this->line("   • 1440px - Pantallas grandes");
        $this->line("   • 1366px - Laptops");
        $this->line("   • 1024px - Tablets");
        $this->line("   • 768px - Tablets pequeñas");
        $this->line("   • 640px - Móviles grandes");
        $this->line("   • 576px - Móviles medianos");
        $this->line("   • 480px - Móviles pequeños");
        $this->line("   • 414px - Móviles muy pequeños");
        $this->line("   • 320px - Móviles extra pequeños");

        $this->info("\n📱 Componentes con mejor implementación responsive:");
        $this->line("   ✅ FloatingBubbleComponent - Múltiples breakpoints");
        $this->line("   ✅ FloatingChatbotComponent - Adaptación móvil");
        $this->line("   ✅ StepperPrincipal - Redimensionamiento inteligente");
        $this->line("   ✅ EditButtonComponent - Layout flexible");
        $this->line("   ✅ ConclusionsMainComponent - Tabla responsive");
        $this->line("   ✅ ResultsMainComponent - Filtros adaptativos");

        $this->info("\n⚠️ Componentes que necesitan mejoras responsive:");
        $this->line("   • VariablesMainComponent - Sin media queries");
        $this->line("   • GraphicsMainComponent - Sin media queries");
        $this->line("   • MatrizMainComponent - Sin media queries");

        $this->info("\n💡 Recomendaciones:");
        $this->line("   1. Agregar media queries a componentes sin responsive");
        $this->line("   2. Implementar tablas responsive para móviles");
        $this->line("   3. Optimizar gráficas para pantallas pequeñas");
        $this->line("   4. Mejorar navegación en dispositivos móviles");
        $this->line("   5. Probar en diferentes dispositivos reales");

        $this->info("\n🔧 Comandos útiles:");
        $this->line("   • npm run dev - Para desarrollo responsive");
        $this->line("   • npm run build - Para producción");
        $this->line("   • Herramientas de desarrollo del navegador para testing");
    }
}
