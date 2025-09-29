<template>
    <div class="main-content">
    <section class="intro-section">
    <h2>{{ storeTexts.variables_section.title_introduction }}</h2>
        <div v-html="storeTexts.variables_section.content_introduction"></div>
</section>
        <!-- Nuevo Stepper visual custom -->
        <CustomStepper
            :steps="steps"
            @update:modelValue="onStepperInput"
        />
        
        <!-- Botón Nueva Ruta - solo aparece cuando tiene 1 ruta y está completada -->
        <div v-if="showNewRouteButton" class="new-route-row">
            <button @click="createNewRoute" class="button is-primary is-large">
                <span class="icon">
                    <i class="fas fa-plus"></i>
                </span>
                <span>Crear Segunda Ruta</span>
            </button>
        </div>
    </div>
    

</template>

<script>
import { onMounted, computed } from 'vue';
import { useTextsStore } from '../../../stores/texts';
import { useSessionStore } from '../../../stores/session';
import { useTraceabilityStore } from '../../../stores/traceability';
import { ref, watch } from 'vue';
import CustomStepper from './CustomStepper.vue';

export default {
    components: {
        CustomStepper
    },
    setup() {
        const storeTexts = useTextsStore();
        const storeSession = useSessionStore();
        const traceabilityStore = useTraceabilityStore();
        
        const steps = [
            { label: 'Variables', icon: 'fa-list' },
            { label: 'Matriz', icon: 'fa-th' },
            { label: 'Gráfica', icon: 'fa-chart-bar' },
            { label: 'Mapa', icon: 'fa-map' },
            { label: 'Direccionador', icon: 'fa-bolt' },
            { label: 'Schwartz', icon: 'fa-project-diagram' },
            { label: 'Condiciones', icon: 'fa-flag' },
            { label: 'Escenarios', icon: 'fa-cubes' },
            { label: 'Conclusiones', icon: 'fa-lightbulb' },
            { label: 'Resultados', icon: 'fa-trophy' }
        ];
        const sectionKeys = [
            'variables',
            'matrix',
            'graphics',
            'analysis',
            'hypothesis',
            'schwartz',
            'initialconditions',
            'scenarios',
            'conclusions',
            'results'
        ];
        


        // Computed para mostrar el botón de nueva ruta
        const showNewRouteButton = computed(() => {
            // Solo mostrar si tiene exactamente 1 ruta y la ruta actual está completada
            const hasOneRoute = traceabilityStore.userRoutes && traceabilityStore.userRoutes.length === 1;
            const currentRouteCompleted = traceabilityStore.availableSections && 
                                         traceabilityStore.availableSections.results === true;
            
            return hasOneRoute && currentRouteCompleted;
        });
        
        // Función para crear nueva ruta
        const createNewRoute = async () => {
            try {
                const response = await fetch('/traceability/create-new-route', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Recargar las secciones disponibles y rutas del usuario
                    await traceabilityStore.loadAvailableSections();
                    await traceabilityStore.loadUserRoutes();
                    
                    // Mostrar mensaje de éxito
                    alert('Segunda ruta creada exitosamente. Ahora tienes 2 rutas disponibles.');
                    
                    // Recargar la página para aplicar los cambios
                    window.location.reload();
                } else {
                    alert('Error al crear nueva ruta: ' + data.message);
                }
            } catch (error) {
                console.error('Error creating new route:', error);
                alert('Error al crear nueva ruta');
            }
        };
        
        // Elimina activeStep y los watchers relacionados
        // Función para el stepper custom: cambia el módulo cuando el usuario hace clic
        function onStepperInput(idx) {
            const section = sectionKeys[idx];
            if (section) {
                storeSession.setActiveContent(section);
            }
        }
        // --- NUEVO: Forzar recarga de secciones al montar el main ---
        onMounted(async () => {
            // Cargar las rutas del usuario para el botón de nueva ruta
            await traceabilityStore.loadUserRoutes();
            
            const accion = JSON.parse(localStorage.getItem('accion_pendiente'));
            const sectionOrder = [
                'variables', 'matrix', 'graphics', 'analysis', 'hypothesis', 'schwartz', 'initialconditions', 'scenarios', 'conclusions', 'results'
            ];
            const cerradoPrefixes = {
                variables: 'variables_cerrado_',
                matrix: 'matriz_cerrado_',
                graphics: 'graphics_cerrado_',
                analysis: 'analisis_cerrado_',
                hypothesis: 'hypothesis_cerrado_',
                schwartz: 'schwartz_cerrado_',
                initialconditions: 'initialconditions_cerrado_',
                scenarios: 'scenarios_cerrado_',
                conclusions: 'conclusions_cerrado_',
                results: 'results_cerrado_'
            };
            const user = JSON.parse(localStorage.getItem('user')) || {};
            if (accion) {
                setTimeout(async () => {
                    if (accion.tipo === 'cerrar') {
                        await traceabilityStore.markSectionCompleted(accion.modulo);
                    } else if (accion.tipo === 'regresar') {
                        if (traceabilityStore.reverseSectionCompleted) {
                            await traceabilityStore.reverseSectionCompleted(accion.modulo);
                        } else {
                            await traceabilityStore.forceReloadSections();
                        }
                        // Eliminar claves de cerrado de este módulo y todos los posteriores
                        const idx = sectionOrder.indexOf(accion.modulo);
                        for (let i = idx; i < sectionOrder.length; i++) {
                            const key = cerradoPrefixes[sectionOrder[i]] + (user.id || 'anon');
                            localStorage.removeItem(key);
                        }
                    }
                    localStorage.removeItem('accion_pendiente');
                    setTimeout(() => {
                        traceabilityStore.forceReloadSections();
                    }, 100);
                }, 1000); // Espera 1 segundo antes de ejecutar la acción pendiente
            } else {
                setTimeout(() => {
                    traceabilityStore.forceReloadSections();
                }, 100);
            }
        });
        // --- FIN NUEVO ---
        return { 
            storeTexts, 
            storeSession, 
            traceabilityStore,
            steps, 
            onStepperInput, 
            showNewRouteButton, 
            createNewRoute 
        };
    }
}
</script>

<style lang="scss" scoped>
.intro-section {
    background-color: #5090c0;
    color: white;
    border-radius: 8px;
    padding: 20px 30px;
    margin: 20px 0;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    line-height: 1.6;
}

.intro-section h2 {
    font-size: 1.5rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 15px;
    color: white !important;
}

/* Asegurar que el título sea blanco con selector más específico */
.intro-section h2,
.intro-section .title,
.intro-section h2.title {
    color: white !important;
}

.intro-section div {
    margin-bottom: 10px;
    font-size: 1rem;
    line-height: 1.6;
}

.new-route-section {
    /* Eliminado: ya no se usa el contenedor */
    display: none;
}

.new-route-row {
    display: flex;
    justify-content: flex-end;
    margin: 80px 0 0 0;
}

.new-route-row .button {
    font-size: 1.2rem;
    padding: 15px 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    background: #7c3aed;
    color: #fff;
    border: none;
    transition: background 0.2s, box-shadow 0.2s;
}
.new-route-row .button:hover {
    background: #5b21b6;
    box-shadow: 0 6px 16px rgba(0,0,0,0.18);
}
</style>
