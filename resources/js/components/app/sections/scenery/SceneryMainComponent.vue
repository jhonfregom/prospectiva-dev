<template>
    <div class="scenery-container">
        <MiniStepper :steps="steps" :currentIndex="7" />
        <div class="main-content">
            <StrategicScenarioTable />
        </div>
    </div>
</template>
<script>

import { useSectionStore } from '../../../../stores/section';
import StrategicScenarioTable from '../scenario/StrategicScenarioTable.vue';
import MiniStepper from '../../ui/MiniStepper.vue';


export default {
    components: {
        MiniStepper,
        StrategicScenarioTable
    },
    data() {
        return {
            steps: [
                { key: 'variables', label: 'Variables', icon: 'fas fa-list' },
                { key: 'matrix', label: 'Matriz', icon: 'fas fa-th' },
                { key: 'graphics', label: 'Gráfica', icon: 'fas fa-chart-bar' },
                { key: 'analysis', label: 'Mapa', icon: 'fas fa-map' },
                { key: 'hypothesis', label: 'Direccionador', icon: 'fas fa-bolt' },
                { key: 'schwartz', label: 'Schwartz', icon: 'fas fa-project-diagram' },
                { key: 'initialconditions', label: 'Condiciones', icon: 'fas fa-flag' },
                { key: 'scenarios', label: 'Escenarios', icon: 'fas fa-cubes' },
                { key: 'conclusions', label: 'Conclusiones', icon: 'fas fa-lightbulb' },
                { key: 'results', label: 'Resultados', icon: 'fas fa-trophy' },
                { key: 'nueva', label: 'Nueva', icon: 'fas fa-star' },
            ]
        };
    },
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
