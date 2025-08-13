<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckInstitutionalColors extends Command
{
    protected $signature = 'check:institutional-colors';
    protected $description = 'Verificar que los colores institucionales se han aplicado correctamente';

    public function handle()
    {
        $this->info('🎨 Verificando aplicación de colores institucionales...');
        
        // Verificar archivo de colores principal
        $colorsFile = resource_path('sass/_colors.scss');
        if (file_exists($colorsFile)) {
            $colorsContent = file_get_contents($colorsFile);
            
            $this->info('✅ Archivo de colores institucionales encontrado');
            
            // Verificar colores principales
            $colorChecks = [
                'color-primary' => '#005883',
                'color-secondary' => '#F0B429', 
                'color-accent' => '#F47920'
            ];
            
            foreach ($colorChecks as $variable => $hexValue) {
                if (strpos($colorsContent, $variable) !== false && strpos($colorsContent, $hexValue) !== false) {
                    $this->info("   ✅ {$variable}: {$hexValue}");
                } else {
                    $this->error("   ❌ {$variable}: NO encontrado o valor incorrecto");
                }
            }
            
            // Verificar gradientes
            $gradientChecks = [
                'gradient-primary' => 'linear-gradient(135deg, #005883 0%, #0077B6 100%)',
                'gradient-secondary' => 'linear-gradient(135deg, #F0B429 0%, #FFD700 100%)',
                'gradient-accent' => 'linear-gradient(135deg, #F47920 0%, #FF6B35 100%)'
            ];
            
            foreach ($gradientChecks as $variable => $gradient) {
                if (strpos($colorsContent, $variable) !== false) {
                    $this->info("   ✅ {$variable}: Gradiente definido");
                } else {
                    $this->error("   ❌ {$variable}: NO encontrado");
                }
            }
        } else {
            $this->error('❌ Archivo de colores institucionales NO encontrado');
        }
        
        // Verificar archivos que usan los nuevos colores
        $filesToCheck = [
            'resources/sass/_default_controls.scss' => ['colors.$color-accent', 'colors.$color-secondary'],
            'resources/sass/layouts/_login.scss' => ['colors.$color-accent', 'colors.$color-primary'],
            'resources/sass/layouts/_register.scss' => ['colors.$color-accent', 'colors.$color-primary'],
            'resources/sass/_footer.scss' => ['colors.$color-accent'],
            'resources/js/components/app/ui/TitleSectionComponent.vue' => ['colors.$color-accent'],
            'resources/sass/sections/_login.scss' => ['colors.$color-accent', 'colors.$color-primary'],
            'resources/sass/layouts/_main.scss' => ['colors'],
            'resources/js/components/app/ui/navbar-top/NavbarNotificationComponent.vue' => ['colors.$color-secondary'],
            'resources/js/components/app/ui/MenuSectionComponent.vue' => ['colors.$color-accent'],
            'resources/js/components/app/ui/navbar-top/NavbarCompanyComponent.vue' => ['colors.$color-primary', 'colors.$color-secondary'],
            'resources/js/components/app/ui/FloatingBubbleComponent.vue' => ['#005883'],
            'resources/js/components/app/sections/CustomStepper.vue' => ['#005883']
        ];
        
        $this->info("\n📁 Verificando archivos actualizados:");
        
        foreach ($filesToCheck as $file => $checks) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $allFound = true;
                
                foreach ($checks as $check) {
                    if (strpos($content, $check) === false) {
                        $allFound = false;
                        break;
                    }
                }
                
                if ($allFound) {
                    $this->info("   ✅ {$file}");
                } else {
                    $this->error("   ❌ {$file}: Colores institucionales NO aplicados");
                }
            } else {
                $this->error("   ❌ {$file}: Archivo NO encontrado");
            }
        }
        
        // Verificar que no queden referencias al archivo variables.scss
        $this->info("\n🔍 Verificando eliminación de referencias antiguas:");
        
        $oldVariableFiles = [
            'resources/sass/_variables.scss' => 'Archivo de variables antiguo'
        ];
        
        foreach ($oldVariableFiles as $file => $description) {
            if (file_exists($file)) {
                $this->warn("   ⚠️ {$file}: {$description} - Considerar eliminación");
            } else {
                $this->info("   ✅ {$file}: Eliminado correctamente");
            }
        }
        
        // Verificar que no queden referencias a @use '../variables'
        $this->info("\n🔍 Verificando referencias a variables antiguas:");
        
        $grepCommand = "grep -r \"@use.*variables.*as\" resources/sass/ resources/js/ 2>/dev/null || echo 'No se encontraron referencias'";
        $result = shell_exec($grepCommand);
        
        if (strpos($result, 'No se encontraron referencias') !== false || empty(trim($result))) {
            $this->info("   ✅ No se encontraron referencias a variables antiguas");
        } else {
            $this->error("   ❌ Se encontraron referencias a variables antiguas:");
            $this->line($result);
        }
        
        $this->info("\n🎨 Verificación de colores institucionales completada!");
        $this->info("💡 Los colores principales aplicados son:");
        $this->info("   • Azul institucional: #005883");
        $this->info("   • Amarillo dorado: #F0B429");
        $this->info("   • Naranja vibrante: #F47920");
        $this->info("   • Barras de navegación: Naranja sólido (#F47920)");
        $this->info("   • Botones principales: Naranja sólido con texto blanco");
        $this->info("   • Botones de acción (Guardar, Cerrar, Regresar): Azul institucional (#005883)");
        $this->info("   • Burbuja de herramientas: Azul institucional (#005883)");
        $this->info("   • Stepper de navegación: Azul institucional (#005883)");
        
        return 0;
    }
}
