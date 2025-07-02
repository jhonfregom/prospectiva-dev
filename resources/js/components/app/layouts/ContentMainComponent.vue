<script>
    // Importa el store de sesión que maneja el estado global de la sesión
    import { useSessionStore } from '../../../stores/session';
    // Importa el store de sección que maneja el título y botones de la sección actual
    import { useSectionStore } from '../../../stores/section';
    // Importa el componente que muestra el título de la sección actual
    import titleSection from '../ui/TitleSectionComponent.vue';
    // Importa el componente principal que muestra el dashboard
    import mainSection from '../sections/MainComponent.vue';
    // Importa el componente que maneja la tabla de variables
    import variables from '../sections/variables/VariablesMainComponent.vue';
    // Importa el componente que maneja la matriz de influencia
    import matriz from '../sections/matriz/MatrizMainComponent.vue';
    // Importa el componente que maneja la gráfica principal
    import graphics from '../sections/graphics/GraphicsMainComponent.vue';
    // Importa el componente para el análisis mapa de variables
    import analysis from '../sections/analisisVariables/AnalisisMapaVariablesMainComponent.vue';
    // Importa el componente que maneja los direccionadores de futuro
    import hypothesis  from '../sections/directionFuture/DirectionFutureMainComponent.vue';
    // Importa el componente que maneja los ejes de Peter Schwartz
    import schwartz from '../sections/schwartz/SchwartzMainComponent.vue';
    // Importa el componente que maneja las condiciones iniciales
    import initialConditions from '../sections/initialConditions/InitialConditionsMainComponent.vue';
    // Importa el componente que maneja los escenarios
    import scenarios from '../sections/scenery/SceneryMainComponent.vue';

    export default {
        // Setup es un hook de composition API que inicializa los stores
        setup() {
            const storeSession = useSessionStore();
            const storeSection = useSectionStore();
            return { storeSession, storeSection };
        },

        // Registra los componentes que se usarán en el template
        components: {
            titleSection,    // Componente para el título de sección
            mainSection,     // Componente para la vista principal/dashboard
            variables,       // Componente para la tabla de variables
            matriz,          // Componente para la matriz de influencia
            graphics,        // Componente para la gráfica principal
            analysis,        // Componente para el análisis mapa de variables
            hypothesis, // Componente para los direccionadores de futuro
            schwartz,         // Componente para los ejes de Peter Schwartz
            initialConditions, // Componente para las condiciones iniciales
            scenarios         // Componente para los escenarios
        },

        data() {
            return {
                // Objeto que refleja qué contenido está activo
                // Se sincroniza con el store de sesión
                contentActive: {}
            }
        },

        created() {
            // Al crear el componente, obtiene el contenido activo del store de sesión
            this.contentActive = this.storeSession.contentActive;
        },

        watch: {
            'storeSession.contentActive': {
                handler(newVal) {
                    console.log('Contenido activo cambiado:', newVal);
                    this.contentActive = newVal;
                },
                deep: true
            }
        },

        methods: {
        }
    }
</script>
<template>
    <div class="application">
        <div class="action-main">
            <!-- Componente que muestra el título de la sección -->
            <title-section />

            <!-- Sección principal que contiene el contenido -->
            <section class="section-content">
                <!-- Grupo de transiciones para animar los cambios entre componentes -->
                <transition-group
                    enter-active-class="animate__animated animate__fadeInLeft animated_delay"
                    leave-active-class="animate__animated animate__fadeOutRight">
                    
                    <!-- Muestra el componente mainSection si contentActive.main es true -->
                    <main-section v-if="contentActive.main"
                        v-bind:key="1"
                    />
                    
                    <!-- Muestra el componente variables si contentActive.variables es true -->
                    <!-- La key=4 es única para este componente en la transición -->
                    <variables v-if="contentActive.variables"
                        v-bind:key="4"
                    />
                    
                    <!-- Muestra el componente matriz si contentActive.matrix es true -->
                    <!-- La key=5 es única para este componente en la transición -->
                    <matriz v-if="contentActive.matrix"
                        v-bind:key="5"
                    />
                    
                    <!-- Muestra el componente graphics si contentActive.graphics es true -->
                    <!-- La key=6 es única para este componente en la transición -->
                    <graphics v-if="contentActive.graphics"
                        v-bind:key="6"
                    />
                    
                    <!-- Muestra el componente analysis si contentActive.analysis es true -->
                    <!-- La key=7 es única para este componente en la transición -->
                    <analysis v-if="contentActive.analysis"
                        v-bind:key="7"
                    />
                    <!-- Muestra el componente hypothesis si contentActive.hypothesis es true -->
                    <hypothesis v-if="contentActive.hypothesis"
                        v-bind:key="8"
                    />
                    <!-- Muestra el componente schwartz si contentActive.schwartz es true -->
                    <schwartz v-if="contentActive.schwartz"
                        v-bind:key="9"
                    />
                    <!-- Muestra el componente initialConditions si contentActive.initialconditions es true -->
                    <initialConditions v-if="contentActive.initialconditions"
                        v-bind:key="10"
                    />
                    <!-- Muestra el componente scenarios si contentActive.scenarios es true -->
                    <scenarios v-if="contentActive.scenarios"
                        v-bind:key="11"
                    /> 
                </transition-group>
            </section>
        </div>
    </div>
</template>
