<template>
    <div class="scenery-container">
        <!-- Letrero informativo -->
        <info-banner-component
            :description="textsStore.getText('scenarios.description')"
        />
        
        <!-- MiniStepper eliminado -->
        <div class="main-content">
            <StrategicScenarioTable />
        </div>
    </div>
</template>
<script>

import { useSectionStore } from '../../../../stores/section';
import { useTextsStore } from '../../../../stores/texts';
import StrategicScenarioTable from '../scenario/StrategicScenarioTable.vue';
import InfoBannerComponent from '../../ui/InfoBannerComponent.vue';

export default {
    components: {
        StrategicScenarioTable,
        InfoBannerComponent
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
        const textsStore = useTextsStore();
        return { 
            sectionStore,
            textsStore
        };
    },
    mounted() {
        this.sectionStore.setTitleSection('Escenarios');

    },

}

</script>