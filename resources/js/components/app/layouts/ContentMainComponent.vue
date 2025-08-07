<template>
    <div class="application">
        <div class="action-main">
            <title-section />
            <section class="section-content">
                <transition-group
                    enter-active-class="animate__animated animate__fadeInLeft animated_delay"
                    leave-active-class="animate__animated animate__fadeOutRight">
                    
                    <main-section v-if="contentActive.main"
                        v-bind:key="1"
                    />
                    
                    <variables v-if="contentActive.variables"
                        v-bind:key="4"
                    />
                    
                    <matriz v-if="contentActive.matrix"
                        v-bind:key="5"
                    />
                    
                    <graphics v-if="contentActive.graphics"
                        v-bind:key="6"
                    />
                    
                    <analysis v-if="contentActive.analysis"
                        v-bind:key="7"
                    />
                    <hypothesis v-if="contentActive.hypothesis"
                        v-bind:key="8"
                    />
                    <schwartz v-if="contentActive.schwartz"
                        v-bind:key="9"
                    />
                    <initialConditions v-if="contentActive.initialconditions"
                        v-bind:key="10"
                    />
                    <scenarios v-if="contentActive.scenarios"
                        v-bind:key="11"
                    /> 
                    <conclusions v-if="contentActive.conclusions"   
                        v-bind:key="12"
                    />
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