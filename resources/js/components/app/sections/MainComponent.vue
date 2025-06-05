<script>
    //Import texts from store
    import { useTextsStore } from '../../../stores/texts';
    //Import session from store
    import { useSessionStore } from '../../../stores/session';

    export default {
        setup(){
            const storeTexts = useTextsStore();
            const storeSession = useSessionStore();
            return { storeTexts, storeSession };
        },
        methods: {
            setActiveSection(item){
                this.storeSession.setActiveContent( item );
            },
        }
    }
</script>
<template>
    <div class="main-content">
        <section class="dashboard columns">
            <section class="modules column columns is-multiline">
                <div v-for="( module, index ) in storeTexts.main_section.modules"
                    :key="index"
                    class="column is-6">
                    <div class="card"
                        v-on:click.prevent="setActiveSection(index)">
                        <header class="card-header">
                            <p class="card-header-title">
                                {{ module.title }}
                            </p>
                        </header>
                        <div class="card-content">
                            <p v-for="(descp, index) in module.description"
                                :key="index">
                                {{ descp }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <section class="separator column is-1" />
            <section class="queries column columns is-multiline">
                <div v-for="( query, index ) in storeTexts.main_section.queries"
                    :key="index"
                    class="column is-6">
                    <div class="card"
                        v-on:click.prevent="setActiveSection(index)">
                        <header class="card-header">
                            <p class="card-header-title">
                                {{ query.title }}
                            </p>
                        </header>
                        <div class="card-content">
                            <p v-for="(descp, index) in query.description"
                                :key="index">
                                {{ descp }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </div>
</template>
