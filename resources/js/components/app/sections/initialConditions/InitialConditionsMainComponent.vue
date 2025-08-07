<template>
    <div class="initial-conditions-container">
        <!-- Letrero informativo -->
        <info-banner-component
            :description="textsStore.getText('initialConditions.description')"
        />
        
        <!-- MiniStepper eliminado -->
        <div class="main-content">
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
                        :disabled="(props.row.edits_now_condition || 0) >= 3"
                    >
                        {{ editingRow === props.row.id ? textsStore.getText('initialConditions.table.save') : textsStore.getText('initialConditions.table.edit') }}
                    </b-button>
                    <span v-if="(props.row.edits_now_condition || 0) >= 3" class="tag is-warning ml-2">{{ textsStore.getText('initialConditions.table.locked') }}</span>
                </b-table-column>
            </b-table>
        </div>
            <!-- Botón cerrar/regresar en la esquina inferior derecha -->
    <div class="cerrar-container">
      <button
        class="cerrar-btn"
        v-if="!cerrado"
        @click="mostrarModal = true"
        :disabled="cerrado"
      >{{ textsStore.getText('initial_conditions_section.close_button') }}</button>
      <button
        class="cerrar-btn"
        v-else-if="state !== null && state === '0'"
        @click="mostrarModalRegresar = true"
      >{{ textsStore.getText('initial_conditions_section.return_button') }}</button>
    </div>
        <!-- Modal de confirmación -->
        <div v-if="mostrarModal" class="modal-confirm">
          <div class="modal-content">
            <p>{{ textsStore.getText('initial_conditions_section.close_confirm_message') }}</p>
            <button @click="cerrarModulo">{{ textsStore.getText('initial_conditions_section.confirm_yes') }}</button>
            <button @click="mostrarModal = false">{{ textsStore.getText('initial_conditions_section.confirm_no') }}</button>
          </div>
        </div>
            <!-- Modal de confirmación para regresar -->
    <div v-if="mostrarModalRegresar" class="modal-confirm">
      <div class="modal-content">
        <p>{{ textsStore.getText('initial_conditions_section.return_confirm_message') }}</p>
        <button @click="regresarModulo">{{ textsStore.getText('initial_conditions_section.confirm_yes_return') }}</button>
        <button @click="mostrarModalRegresar = false">{{ textsStore.getText('initial_conditions_section.confirm_no') }}</button>
      </div>
    </div>
    </div>
</template>
<script>
import { ref, onMounted, onBeforeUnmount, getCurrentInstance, inject } from 'vue';
import { useSectionStore } from '../../../../stores/section';
import { useInitialConditionsStore } from '../../../../stores/initialConditions';
import { useTextsStore } from '../../../../stores/texts';
import { useSessionStore } from '../../../../stores/session';
import axios from 'axios';
import { useTraceabilityStore } from '../../../../stores/traceability';
import InfoBannerComponent from '../../ui/InfoBannerComponent.vue';

export default {
    name: 'InitialConditionsMainComponent',
    components: {
        InfoBannerComponent,
    },
    data() {
        return {
            
            isComponentPracticeEditing: false,
            isActualityEditing: false,
            isAplicationEditing: false,
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
            ],
            mostrarModal: false,
            mostrarModalRegresar: false,
            cerrado: false,
            forceRerender: 0,
            tried: null, 
        };
    },

    setup() {
        const sectionStore = useSectionStore();
        const initialConditionsStore = useInitialConditionsStore();
        const textsStore = useTextsStore();
        const sessionStore = useSessionStore();
        const editingRow = ref(null);
        const localNowConditions = ref([]);
        const traceabilityStore = useTraceabilityStore();
        
        let initialCerrado = false;
        if (typeof window !== 'undefined') {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            const cerradoKey = 'initialconditions_cerrado_' + (user.id || 'anon');
            initialCerrado = localStorage.getItem(cerradoKey) === 'true';
        }
        const cerrado = ref(initialCerrado);
        const mostrarModal = ref(false);
        const mostrarModalRegresar = ref(false);
        const state = ref(null); 

        onMounted(async () => {
            sectionStore.setTitleSection(textsStore.getText('initialConditions.title'));
            await initialConditionsStore.fetchConditions();
            
            initialConditionsStore.conditions.forEach((c) => {
                localNowConditions.value[c.id] = c.now_condition || '';
            });
            
            if (typeof window !== 'undefined') {
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const cerradoKey = 'initialconditions_cerrado_' + (user.id || 'anon');
                cerrado.value = localStorage.getItem(cerradoKey) === 'true';
            }
            
            await loadStateValue();
        });

        onBeforeUnmount(() => {
            
            sectionStore.clearDynamicButtons();
        });

        const loadStateValue = async () => {
            try {
                const response = await axios.get('/traceability/current-route-state');
                if (response.data && response.data.success && response.data.state !== undefined) {
                    state.value = response.data.state;
                }
            } catch (error) {
                console.error('Error al cargar state:', error);
            }
        };

        const incrementState = async () => {
            try {
                await axios.put('/traceability/current-route-state', { state: '1' });
                state.value = '1';
            } catch (error) {
                console.error('Error al actualizar state:', error);
            }
        };

        const handleEditSave = async (row, index) => {
            console.log('handleEditSave called:', { 
                variable_id: row.id_variable, 
                id: row.id, 
                index: index, 
                state: row.state,
                isLocked: row.state === '1'
            });
            
            if (row.state === '1') {
                console.log('Row is locked, cannot edit');
                return;
            }
            
            if (editingRow.value === row.id) {
                
                console.log('Saving initial condition for variable ID:', row.id);
                const result = await initialConditionsStore.updateCondition(row.id, localNowConditions.value[row.id] || '');
                
                if (result && result.success) {
                    
                    await initialConditionsStore.fetchConditions();
                    const updated = initialConditionsStore.conditions.find(c => c.id === row.id);
                    if (updated) {
                        localNowConditions.value[row.id] = updated.now_condition || '';
                    }
                    editingRow.value = null;
                    console.log('After save - Variable ID:', row.id, 'New state:', updated?.state);
                }
            } else {
                
                editingRow.value = row.id;
            }
        };

        async function updateInitialConditionInServer(id) {
            try {
                
                const result = await axios.put(`/initial-conditions/${id}`, {
                    now_condition: localNowConditions.value[id] || '',
                    edits_now_condition: 3, 
                    state: 1
                });
                
                if (result.data && result.data.data) {
                    
                    const updatedCondition = initialConditionsStore.conditions.find(c => c.id === id);
                    if (updatedCondition) {
                        updatedCondition.edits_now_condition = result.data.data.edits_now_condition;
                        updatedCondition.state = result.data.data.state;
                    }
                }
            } catch (e) {
                console.error('Error al actualizar condición inicial:', e);
            }
        }

        const cerrarModulo = async () => {
            mostrarModal.value = false;
            try {
                
                const requests = initialConditionsStore.conditions.map(cond =>
                    axios.put(`/initial-conditions/${cond.id}`, {
                        now_condition: cond.now_condition,
                        state: 1,
                        edits_now_condition: 3
                    })
                );
                await Promise.all(requests);
                await initialConditionsStore.fetchConditions();
                
                initialConditionsStore.conditions.forEach((c) => {
                    localNowConditions.value[c.id] = c.now_condition || '';
                });
                editingRow.value = null;
                
                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'cerrar', modulo: 'initialconditions' }));
                
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const cerradoKey = 'initialconditions_cerrado_' + (user.id || 'anon');
                localStorage.setItem(cerradoKey, 'true');
                
                if (typeof window !== 'undefined' && window.$buefy) {
                    window.$buefy.toast.open({
                        message: 'Módulo de condiciones iniciales cerrado correctamente',
                        type: 'is-success'
                    });
                }
                
                sessionStore.setActiveContent('main');
                
                setTimeout(async () => {
                    await traceabilityStore.markSectionCompleted('initialconditions');
                }, 1000);
                cerrado.value = true;
            } catch (error) {
                cerrado.value = false;
                
                if (typeof window !== 'undefined' && window.$buefy) {
                    window.$buefy.toast.open({
                        message: 'Error al cerrar el módulo de condiciones iniciales',
                        type: 'is-danger'
                    });
                }
            }
        };

        const regresarModulo = async () => {
            mostrarModalRegresar.value = false;
            try {
                
                await incrementState();
                
                await loadStateValue();
                
                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'regresar', modulo: 'initialconditions' }));
                
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const posteriores = [
                  'scenarios', 'conclusions', 'results' 
                ];
                posteriores.forEach(mod => {
                  const cerradoKey = mod + '_cerrado_' + (user.id || 'anon');
                  localStorage.removeItem(cerradoKey);
                });
                
                sessionStore.setActiveContent('main');
                
                const cerradoKey = 'initialconditions_cerrado_' + (user.id || 'anon');
                localStorage.removeItem(cerradoKey);
                cerrado.value = false;
                
                await initialConditionsStore.fetchConditions();
                
                if (typeof window !== 'undefined' && window.$buefy) {
                    window.$buefy.toast.open({
                        message: 'Módulo de condiciones iniciales reabierto correctamente',
                        type: 'is-success'
                    });
                }
            } catch (error) {
                if (typeof window !== 'undefined' && window.$buefy) {
                    window.$buefy.toast.open({
                        message: 'Error al regresar el módulo de condiciones iniciales',
                        type: 'is-danger'
                    });
                }
            }
        };

        return { 
            sectionStore,
            initialConditionsStore,
            textsStore,
            sessionStore,
            editingRow,
            localNowConditions,
            traceabilityStore,
            cerrado,
            mostrarModal,
            mostrarModalRegresar,
            state,
            handleEditSave,
            cerrarModulo,
            regresarModulo,
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
    }
};
</script>

<style scoped>
.main-content {
    padding: 20px;
}

:deep(.b-table .table tbody td) {
    vertical-align: middle !important;
    height: 80px !important;
}

:deep(.b-table .table),
:deep(.b-table .table th),
:deep(.b-table .table td),
:deep(.b-table .table tr),
:deep(.b-table .table thead),
:deep(.b-table .table tbody) {
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
.cerrar-container {
  position: fixed;
  bottom: 32px;
  right: 48px;
  z-index: 100;
}
.cerrar-btn {
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 14px 32px;
  font-size: 1.2rem;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(50,115,220,0.08);
  cursor: pointer;
  transition: background 0.2s;
}
.cerrar-btn:disabled {
  background: #b0b0b0;
  cursor: not-allowed;
}
.modal-confirm {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
}
.modal-content {
  background: white;
  padding: 32px 48px;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.15);
  text-align: center;
}
.modal-content button {
  margin: 0 12px;
  padding: 10px 24px;
  border-radius: 6px;
  border: none;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
}
</style>