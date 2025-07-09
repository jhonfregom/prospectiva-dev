<script>
import { ref, onMounted } from 'vue';
import { useSectionStore } from '../../../../stores/section';
import { useInitialConditionsStore } from '../../../../stores/initialConditions';
import { useTextsStore } from '../../../../stores/texts';

export default {
    name: 'InitialConditionsMainComponent',
    setup() {
        const sectionStore = useSectionStore();
        const initialConditionsStore = useInitialConditionsStore();
        const textsStore = useTextsStore();
        const editingRow = ref(null);
        const localNowConditions = ref([]);

        onMounted(async () => {
            sectionStore.setTitleSection(textsStore.getText('initialConditions.title'));
            await initialConditionsStore.fetchConditions();
            // Sincronizar valores locales
            initialConditionsStore.conditions.forEach((c) => {
                localNowConditions.value[c.id] = c.now_condition || '';
            });
        });

        const handleEditSave = async (row, index) => {
            if (row.state === '1') return;
            if (editingRow.value === row.id) {
                await initialConditionsStore.updateCondition(row.id, localNowConditions.value[row.id] || '');
                await initialConditionsStore.fetchConditions();
                const updated = initialConditionsStore.conditions.find(c => c.id === row.id);
                if (updated) {
                    localNowConditions.value[row.id] = updated.now_condition || '';
                }
                editingRow.value = null;
            } else {
                editingRow.value = row.id;
            }
        };

        return { 
            sectionStore,
            initialConditionsStore,
            textsStore,
            editingRow,
            localNowConditions,
            handleEditSave
        };
    }
};
</script>
<template>
    <div class="main-content">
        <b-message type="is-info" has-icon>
            {{ textsStore.getText('initialConditions.subtitle') }}
        </b-message>
        <b-table :data="initialConditionsStore.conditions" :striped="true" :hoverable="true" :bordered="true" :narrowed="true" :loading="initialConditionsStore.isLoading" icon-pack="fas">
            <b-table-column field="id_variable" :label="textsStore.getText('initialConditions.table.variable')" v-slot="props" centered>
                <span>{{ props.row.id_variable }}</span>
            </b-table-column>
            <b-table-column field="name_variable" :label="textsStore.getText('initialConditions.table.name')" v-slot="props" centered>
                <span>{{ props.row.name_variable }}</span>
            </b-table-column>
            <b-table-column field="now_condition" :label="textsStore.getText('initialConditions.table.nowCondition')" v-slot="props" centered>
                <div style="display: flex; justify-content: center; align-items: center; width: 100%;">
                    <b-input
                        v-model="localNowConditions[props.row.id]"
                        type="textarea"
                        :disabled="props.row.state === '1' || editingRow !== props.row.id"
                        :placeholder="textsStore.getText('initialConditions.table.nowCondition')"
                        :rows="3"
                        style="min-width:700px; max-width:1200px; resize:vertical; text-align:center;"
                    />
                </div>
            </b-table-column>
            <b-table-column field="actions" :label="textsStore.getText('initialConditions.table.actions')" v-slot="props" centered>
                <b-button
                    type="is-info"
                    size="is-small"
                    icon-left="edit"
                    @click="handleEditSave(props.row, props.index)"
                    outlined
                    :disabled="props.row.state === '1'"
                >
                    {{ editingRow === props.row.id ? textsStore.getText('initialConditions.table.save') : textsStore.getText('initialConditions.table.edit') }}
                </b-button>
                <span v-if="props.row.state === '1'" class="tag is-warning ml-2">{{ textsStore.getText('initialConditions.table.locked') }}</span>
            </b-table-column>
        </b-table>
    </div>
</template>
<style scoped>
.main-content {
    padding: 20px;
}

/* Centrado vertical EXACTO como en VariablesMainComponent */
::v-deep .b-table .table tbody td {
    vertical-align: middle !important;
    height: 80px !important;
}

/* El resto de estilos de borders y backgrounds se mantienen */
::v-deep .b-table .table,
::v-deep .b-table .table th,
::v-deep .b-table .table td,
::v-deep .b-table .table tr,
::v-deep .b-table .table thead,
::v-deep .b-table .table tbody {
    border: none !important;
    border-bottom: none !important;
    border-right: none !important;
    border-left: none !important;
    border-top: none !important;
    box-shadow: none !important;
}
.b-table th, .b-table td {
    border: none !important;
    border-bottom: none !important;
    border-right: none !important;
}
.b-table tr {
    border: none !important;
}
.b-table tr:nth-child(even) td {
    background-color: #f8f7fa !important;
}
.b-table tr:nth-child(odd) td {
    background-color: #fff !important;
}
.b-table th,
.b-table td {
    vertical-align: middle !important;
    text-align: center !important;
    font-family: 'Montserrat', Arial, sans-serif;
}
.b-table th {
    background-color: #f8f7fa !important;
    color: #7c5cbf !important;
    font-family: 'Montserrat', Arial, sans-serif;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 13px;
    min-height: 60px;
    text-align: center !important;
    vertical-align: middle !important;
}
.b-table td {
    color: #1F2937;
    font-weight: 500;
    text-align: center !important;
    font-family: 'Montserrat', Arial, sans-serif;
    padding: 0 8px;
    vertical-align: middle !important;
}
.b-table .textarea-container,
.b-table .edit-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.b-table-column span {
    display: inline-block;
    vertical-align: middle;
    text-align: center;
}
.b-input[type="textarea"] {
    background: #f8f7fa !important;
    border: none !important;
    border-radius: 0 !important;
    font-size: 15px;
    color: #7c5cbf;
    padding: 12px 16px !important;
    min-width: 700px;
    max-width: 1200px;
    resize: vertical;
    text-align: left;
    font-family: 'Montserrat', Arial, sans-serif;
    box-shadow: none !important;
    width: 100%;
    height: 100%;
    /* Centrado vertical del texto dentro del textarea */
    display: flex;
    align-items: center;
}
.b-input[type="textarea"]:disabled {
    background: #f3f3f3 !important;
    color: #b0b3c6 !important;
}
.b-table .actions-column {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 100%;
}
.b-table .b-button.is-info {
    background: #fff !important;
    color: #3b82f6 !important;
    border: 1px solid #3b82f6 !important;
    font-weight: 600;
    border-radius: 4px;
    padding: 4px 16px;
    transition: background 0.2s, color 0.2s;
    font-family: 'Montserrat', Arial, sans-serif;
}
.b-table .b-button.is-info:hover {
    background: #3b82f6 !important;
    color: #fff !important;
}
.b-table .tag.is-warning {
    background: #fff !important;
    color: #e74c3c !important;
    border: 1px solid #e74c3c !important;
    border-radius: 4px;
    font-weight: 600;
    padding: 4px 12px;
    margin-left: 8px;
    font-family: 'Montserrat', Arial, sans-serif;
}
</style>