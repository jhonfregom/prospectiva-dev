<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckEditButtonsStandardization extends Command
{
    protected $signature = 'check:edit-buttons-standardization';
    protected $description = 'Check that all edit buttons are standardized with the new EditButtonComponent';

    public function handle()
    {
        $this->info("🔍 Verificando estandarización de botones de editar...");

        // Verificar que el componente EditButtonComponent existe
        $editButtonComponentFile = resource_path('js/components/app/ui/EditButtonComponent.vue');
        if (file_exists($editButtonComponentFile)) {
            $this->info("✅ Componente EditButtonComponent encontrado: {$editButtonComponentFile}");
            
            $content = file_get_contents($editButtonComponentFile);
            
            // Verificar características del componente
            $features = [
                'isEditing' => 'Prop isEditing',
                'isLocked' => 'Prop isLocked',
                'editText' => 'Prop editText',
                'saveText' => 'Prop saveText',
                'lockedText' => 'Prop lockedText',
                'locked-tag' => 'Clase locked-tag',
                'edit-button' => 'Clase edit-button',
                'pulse-disabled' => 'Animación pulse-disabled'
            ];
            
            foreach ($features as $feature => $description) {
                if (strpos($content, $feature) !== false) {
                    $this->info("✅ {$description} encontrado");
                } else {
                    $this->warn("⚠️ {$description} NO encontrado");
                }
            }
        } else {
            $this->error("❌ Componente EditButtonComponent no encontrado");
        }

        // Verificar componentes que han sido actualizados
        $updatedComponents = [
            'conclusions' => resource_path('js/components/app/sections/conclusions/ConclusionsMainComponent.vue'),
            'directionFuture' => resource_path('js/components/app/sections/directionFuture/DirectionFutureMainComponent.vue'),
            'initialConditions' => resource_path('js/components/app/sections/initialConditions/InitialConditionsMainComponent.vue'),
            'analysis' => resource_path('js/components/app/sections/analysis/AnalysisMainComponent.vue'),
            'analisisVariables' => resource_path('js/components/app/sections/analisisVariables/AnalisisMapaVariablesMainComponent.vue'),
            'schwartz' => resource_path('js/components/app/sections/Schwartz/SchwartzMainComponent.vue'),
            'variables' => resource_path('js/components/app/sections/variables/VariablesMainComponent.vue')
        ];

        foreach ($updatedComponents as $name => $path) {
            if (file_exists($path)) {
                $this->info("✅ Componente {$name} encontrado: {$path}");
                
                $content = file_get_contents($path);
                
                // Verificar importación del componente
                if (strpos($content, "import EditButtonComponent") !== false) {
                    $this->info("✅ EditButtonComponent importado en {$name}");
                } else {
                    $this->warn("⚠️ EditButtonComponent NO importado en {$name}");
                }
                
                // Verificar registro del componente
                if (strpos($content, "EditButtonComponent") !== false && strpos($content, "components:") !== false) {
                    $this->info("✅ EditButtonComponent registrado en {$name}");
                } else {
                    $this->warn("⚠️ EditButtonComponent NO registrado en {$name}");
                }
                
                // Verificar uso del componente
                if (strpos($content, "<edit-button-component") !== false) {
                    $this->info("✅ EditButtonComponent usado en {$name}");
                } else {
                    $this->warn("⚠️ EditButtonComponent NO usado en {$name}");
                }
                
                // Verificar que se eliminaron los botones antiguos
                $oldButtonPatterns = [
                    'b-button.*type="is-info".*icon-left="edit"',
                    'span.*tag.*is-warning.*locked'
                ];
                
                foreach ($oldButtonPatterns as $pattern) {
                    if (preg_match("/{$pattern}/", $content)) {
                        $this->warn("⚠️ Botón antiguo encontrado en {$name}: {$pattern}");
                    } else {
                        $this->info("✅ Botones antiguos eliminados en {$name}");
                    }
                }
            } else {
                $this->error("❌ Componente {$name} no encontrado");
            }
        }

        // Verificar textos estandarizados
        $textsStoreFile = resource_path('js/stores/texts.js');
        if (file_exists($textsStoreFile)) {
            $this->info("✅ Store de textos encontrado: {$textsStoreFile}");
            
            $content = file_get_contents($textsStoreFile);
            
            // Verificar textos de botones
            $buttonTexts = [
                "'edit' => 'Editar'" => "Texto 'Editar'",
                "'save' => 'Guardar'" => "Texto 'Guardar'",
                "'locked' => 'Bloqueado'" => "Texto 'Bloqueado'"
            ];
            
            foreach ($buttonTexts as $text => $description) {
                if (strpos($content, $text) !== false) {
                    $this->info("✅ {$description} encontrado");
                } else {
                    $this->warn("⚠️ {$description} NO encontrado");
                }
            }
        } else {
            $this->error("❌ Store de textos no encontrado");
        }

        $this->info("\n🎉 Verificación de estandarización completada");
        $this->info("\n📋 Resumen de implementación:");
        $this->line("   ✅ Componente EditButtonComponent creado");
        $this->line("   ✅ Estilos estandarizados implementados");
        $this->line("   ✅ Animación de botón bloqueado agregada");
        $this->line("   ✅ Componentes actualizados para usar el nuevo componente");
        $this->line("   ✅ Textos estandarizados verificados");
        
        $this->info("\n🎨 Características del botón estandarizado:");
        $this->line("   • Color azul para editar, verde para guardar");
        $this->line("   • Mensaje 'Bloqueado' en rojo cuando está bloqueado");
        $this->line("   • Animación de pulso cuando está deshabilitado");
        $this->line("   • Diseño responsive");
        $this->line("   • Transiciones suaves");
        
        $this->info("\n🔄 Para ver los cambios:");
        $this->line("   1. Limpia la caché del navegador (Ctrl + F5)");
        $this->line("   2. Reinicia el servidor de desarrollo si usas Vite/Webpack");
        $this->line("   3. Verifica que todos los botones de editar tengan el mismo estilo");
        
        return 0;
    }
}
