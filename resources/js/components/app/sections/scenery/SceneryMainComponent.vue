<template>
    <div class="main-content">
        <StrategicScenarioTable />
    </div>
</template>
<script>

import { useSectionStore } from '../../../../stores/section';
import StrategicScenarioTable from '../scenario/StrategicScenarioTable.vue';


export default {
    components: { StrategicScenarioTable },
    setup() {
        const sectionStore = useSectionStore();
        return { 
            sectionStore,

        };
    },
    mounted() {
        this.sectionStore.setTitleSection('Escenarios');
        // this.sectionStore.addDynamicButton('Nuevo', () => {
        //     this.showModal = true;
        // });
    },

}

</script>
