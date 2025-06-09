<script>
    //Import session from store
    import { useSessionStore } from '../../../stores/session';
    //Import section from store
    import { useSectionStore } from '../../../stores/section';
    //Import title section
    import titleSection from '../ui/TitleSectionComponent.vue';
    //Import ContentMain
    import mainSection from '../sections/MainComponent.vue';
    //Import Sections
    //import parameters from '../sections/parameters/ParametersMainComponent.vue';
    //import roles from '../sections/roles/RolesMainComponent.vue';
    import variables from '../sections/variables/VariablesMainComponent.vue';

    export default{
        setup(){
            const storeSession = useSessionStore();
            const storeSection = useSectionStore();
            return { storeSession, storeSection };
        },
        components: {
            titleSection,
            mainSection,
         //   parameters,
         //   roles,
            variables
        },
        props: {
        },
        data() {
            return {
                contentActive: {}
            }
        },
        created(){
            //Set current component as active
            this.contentActive = this.storeSession.contentActive;
        },
        methods: {
        }
    }
</script>
<template>
    <div class="application">
        <div class="action-main">
            <!-- Title section -->
            <title-section />
            <!-- /Title section -->

            <!-- Content section -->
            <section class="section-content">
                <transition-group
                    enter-active-class="animate__animated animate__fadeInLeft animated_delay"
                    leave-active-class="animate__animated animate__fadeOutRight">
                    <main-section v-if="contentActive.main"
                        v-bind:key="1"
                    />
                   <!-- <parameters v-if="contentActive.parameters"
                        v-bind:key="2"
                    />
                    <roles v-if="contentActive.roles"
                        v-bind:key="3"
                    />-->
                    <variables v-if="contentActive.variables"
                        v-bind:key="4"
                    />
                </transition-group>
            </section>
            <!-- /Content section -->
        </div>
    </div>
</template>
