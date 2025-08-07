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
                    <!-- Muestra el componente conclusions si contentActive.conclusions es true -->
                    <conclusions v-if="contentActive.conclusions"   
                        v-bind:key="12"
                    />
                    <!-- Muestra el componente results si contentActive.results es true -->
                    <results v-if="contentActive.results"
                        v-bind:key="13"
                    />
                </transition-group>
            </section>
        </div>
    </div>
</template>

<script>
    
    import { useSessionStore } from '../../../stores/session';
    
    import { useSectionStore } from '../../../stores/section';
    
    import titleSection from '../ui/TitleSectionComponent.vue';
    
    import mainSection from '../sections/MainComponent.vue';
    
    import variables from '../sections/variables/VariablesMainComponent.vue';
    
    import matriz from '../sections/matriz/MatrizMainComponent.vue';
    
    import graphics from '../sections/graphics/GraphicsMainComponent.vue';
    
    import analysis from '../sections/analisisVariables/AnalisisMapaVariablesMainComponent.vue';
    
    import hypothesis  from '../sections/directionFuture/DirectionFutureMainComponent.vue';
    
    import schwartz from '../sections/schwartz/SchwartzMainComponent.vue';
    
    import initialConditions from '../sections/initialConditions/InitialConditionsMainComponent.vue';
    
    import scenarios from '../sections/scenery/SceneryMainComponent.vue';
    
    import conclusions from '../sections/conclusions/ConclusionsMainComponent.vue';
    
    import results from '../sections/results/ResultsMainComponent.vue';

    export default {
        
        setup() {
            const storeSession = useSessionStore();
            const storeSection = useSectionStore();
            return { storeSession, storeSection };
        },

        components: {
            titleSection,    
            mainSection,     
            variables,       
            matriz,          
            graphics,        
            analysis,        
            hypothesis, 
            schwartz,         
            initialConditions, 
            scenarios,         
            conclusions,       
            results,
            
        },

        data() {
            return {

                contentActive: {}
            }
        },

        created() {
            
            this.contentActive = this.storeSession.contentActive;
        },

        watch: {
            'storeSession.contentActive': {
                handler(newVal) {
                    this.contentActive = newVal;
                },
                deep: true
            }
        },

        methods: {
        }
    }
</script>