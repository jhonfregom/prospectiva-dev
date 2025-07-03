<script>
import { ref, onMounted, computed } from 'vue';
import { useSectionStore } from '../../../../stores/section';
import { useScenariosStore } from '../../../../stores/scenarios';
import { useSchwartzStore } from '../../../../stores/schwartz';
import { useFutureDriversStore } from '../../../../stores/futureDrivers';
import { useTextsStore } from '../../../../stores/texts';
// Aquí deberías importar el store de hipótesis si necesitas mostrar las hipótesis cercanas

export default {
    name: 'SceneryMainComponent',
    setup() {
        const sectionStore = useSectionStore();
        const scenariosStore = useScenariosStore();
        const schwartzStore = useSchwartzStore();
        const futureDriversStore = useFutureDriversStore();
        const textsStore = useTextsStore();
        const editingRow = ref({}); // { [id]: true/false }
        const localYears = ref({}); // { [id]: { year_1, year_2, year_3 } }

        // Mapeo de hipótesis por escenario
        const hypothesisMap = [
            // Escenario 1: H1+ y H2+
            { h1: 'H1+', h2: 'H2+' },
            // Escenario 2: H1- y H2+
            { h1: 'H1-', h2: 'H2+' },
            // Escenario 3: H1- y H2-
            { h1: 'H1-', h2: 'H2-' },
            // Escenario 4: H1+ y H2-
            { h1: 'H1+', h2: 'H2-' },
        ];

        // Computed para obtener los textos de hipótesis
        const h1H0 = computed(() => futureDriversStore.drivers[0]?.descriptionH0 || '');
        const h1H1 = computed(() => futureDriversStore.drivers[0]?.descriptionH1 || '');
        const h2H0 = computed(() => futureDriversStore.drivers[1]?.descriptionH0 || '');
        const h2H1 = computed(() => futureDriversStore.drivers[1]?.descriptionH1 || '');

        // Computed para obtener los textos de los escenarios desde Schwartz
        const escenariosSchwartz = computed(() => schwartzStore.escenarios);

        // Sincronizar años con escenarios existentes
        onMounted(async () => {
            sectionStore.setTitleSection(textsStore.getText('scenarios.title'));
            await scenariosStore.fetchScenarios();
            await futureDriversStore.fetchDrivers();
            scenariosStore.scenarios.forEach(s => {
                localYears.value[s.id] = {
                    year1: s.year1 || '',
                    year2: s.year2 || '',
                    year3: s.year3 || ''
                };
            });
        });

        const handleEditSave = async (scenario) => {
            if (editingRow.value[scenario.id]) {
                await scenariosStore.updateScenario(scenario.id, localYears.value[scenario.id]);
                editingRow.value[scenario.id] = false;
            } else {
                editingRow.value[scenario.id] = true;
            }
        };

        // Devuelve el texto del escenario desde Schwartz según el índice
        const getScenarioTitle = (index) => {
            return escenariosSchwartz.value[index]?.texto || '';
        };

        // Devuelve las hipótesis correctas para cada escenario
        const getHypothesisText = (index) => {
            const map = hypothesisMap[index];
            let h1 = '', h2 = '';
            if (map.h1 === 'H1+') h1 = h1H0.value;
            if (map.h1 === 'H1-') h1 = h1H1.value;
            if (map.h2 === 'H2+') h2 = h2H0.value;
            if (map.h2 === 'H2-') h2 = h2H1.value;
            return [h1, h2];
        };

        // Relacionar escenarios Schwartz con escenarios BD por orden
        const escenariosConDatos = computed(() => {
            return scenariosStore.scenarios.map((s, idx) => ({
                ...s,
                titulo: getScenarioTitle(idx),
                hipotesis: getHypothesisText(idx)
            }));
        });

        return {
            sectionStore,
            scenariosStore,
            textsStore,
            editingRow,
            localYears,
            handleEditSave,
            escenariosConDatos
        };
    }
};
</script>
<template>
    <div class="main-content">
        <div v-for="(scenario, idx) in escenariosConDatos" :key="scenario.id" class="scenario-table-wrapper">
            <h3 class="scenario-title">{{ scenario.titulo }}</h3>
            <b-table :data="[scenario]" :striped="true" :hoverable="true" :narrowed="true" :bordered="false">
                <b-table-column :label="textsStore.getText('scenarios.table.description')" v-slot="props">
                    <span>{{ scenario.texto }}</span>
                </b-table-column>
                <b-table-column :label="textsStore.getText('scenarios.table.hypothesis')" v-slot="props">
                    <span>
                        <div v-for="(h, i) in scenario.hipotesis" :key="i">{{ h }}</div>
                    </span>
                </b-table-column>
                <b-table-column :label="textsStore.getText('scenarios.table.year1')" v-slot="props">
                    <b-input type="textarea"
                        v-model="localYears[scenario.id].year1"
                        :disabled="!editingRow[scenario.id]"
                        :placeholder="textsStore.getText('scenarios.table.year1')"
                        rows="2"
                    />
                </b-table-column>
                <b-table-column :label="textsStore.getText('scenarios.table.year2')" v-slot="props">
                    <b-input type="textarea"
                        v-model="localYears[scenario.id].year2"
                        :disabled="!editingRow[scenario.id]"
                        :placeholder="textsStore.getText('scenarios.table.year2')"
                        rows="2"
                    />
                </b-table-column>
                <b-table-column :label="textsStore.getText('scenarios.table.year3')" v-slot="props">
                    <b-input type="textarea"
                        v-model="localYears[scenario.id].year3"
                        :disabled="!editingRow[scenario.id]"
                        :placeholder="textsStore.getText('scenarios.table.year3')"
                        rows="2"
                    />
                </b-table-column>
                <b-table-column :label="textsStore.getText('scenarios.table.actions')" v-slot="props">
                    <b-button type="is-info" size="is-small" icon-left="edit" @click="handleEditSave(scenario)" outlined>
                        {{ editingRow[scenario.id] ? textsStore.getText('scenarios.table.save') : textsStore.getText('scenarios.table.edit') }}
                    </b-button>
                </b-table-column>
            </b-table>
        </div>
    </div>
</template>
<style scoped>
.main-content {
    padding: 20px;
}
.scenario-table-wrapper {
    margin-bottom: 40px;
}
.scenario-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #7c5cbf;
    margin-bottom: 10px;
    text-align: left;
}
</style>