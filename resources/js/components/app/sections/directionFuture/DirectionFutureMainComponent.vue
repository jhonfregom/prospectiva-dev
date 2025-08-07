<template>
    <div class="direction-future-container">
        <!-- Letrero informativo -->
        <info-banner-component
            :description="textsStore.getText('hypothesis.description')"
        />
        
        <!-- MiniStepper eliminado -->
        <div class="variables-container">
            <b-table :data="drivers" :striped="true" :hoverable="true" :bordered="false" :narrowed="true" :loading="futureDriversStore.isLoading" icon-pack="fas">
                <b-table-column field="h" :label="textsStore.getText('hypothesis.table.h')" width="80" v-slot="props" centered>
                    <span>H{{ props.index + 1 }}</span>
                </b-table-column>
                <b-table-column field="variable" :label="textsStore.getText('hypothesis.table.variable')" v-slot="props" centered>
                    <span>{{ props.row.variable_name }}</span>
                </b-table-column>
                <b-table-column field="descriptionH0" :label="textsStore.getText('hypothesis.table.descriptionH0')" v-slot="props" centered header-class="hypothesis-header">
                    <div class="edit-area">
                        <div class="textarea-container">
                            <b-input
                                v-model="localDescriptionsH0[props.row.variable_id]"
                                type="textarea"
                                :disabled="isLocked(props.row) || editingRow !== props.row.variable_id"
                                :placeholder="textsStore.getText('hypothesis.table.descriptionH0')"
                                :rows="3"
                                style="min-width:180px; max-width:350px; resize:vertical;"
                                @input="handleInput($event, props.row, 'H0')"
                                @keydown="handleKeyDown($event, props.row, 'H0')"
                                @paste="handlePaste($event, props.row, 'H0')"
                            />
                            <div class="word-counter" :class="getWordCountClass(countWords(localDescriptionsH0[props.row.variable_id]))">
                                {{ countWords(localDescriptionsH0[props.row.variable_id]) }}/40 palabras
                            </div>
                        </div>
                    </div>
                </b-table-column>
                <b-table-column field="descriptionH1" :label="textsStore.getText('hypothesis.table.descriptionH1')" v-slot="props" centered header-class="hypothesis-header">
                    <div class="edit-area">
                        <div class="textarea-container">
                            <b-input
                                v-model="localDescriptionsH1[props.row.variable_id]"
                                type="textarea"
                                :disabled="isLocked(props.row) || editingRow !== props.row.variable_id"
                                :placeholder="textsStore.getText('hypothesis.table.descriptionH1')"
                                :rows="3"
                                style="min-width:180px; max-width:350px; resize:vertical;"
                                @input="handleInput($event, props.row, 'H1')"
                                @keydown="handleKeyDown($event, props.row, 'H1')"
                                @paste="handlePaste($event, props.row, 'H1')"
                            />
                            <div class="word-counter" :class="getWordCountClass(countWords(localDescriptionsH1[props.row.variable_id]))">
                                {{ countWords(localDescriptionsH1[props.row.variable_id]) }}/40 palabras
                            </div>
                        </div>
                    </div>
                </b-table-column>
                <b-table-column field="actions" :label="textsStore.getText('hypothesis.table.actions')" v-slot="props" centered>
                    <div class="actions-column">
                        <b-button
                            type="is-info"
                            size="is-small"
                            icon-left="edit"
                            @click="handleEditSave(props.row, props.index)"
                            outlined
                            :disabled="isLocked(props.row)"
                        >
                            {{ editingRow === props.row.variable_id ? textsStore.getText('hypothesis.table.save') : textsStore.getText('hypothesis.table.edit') }}
                        </b-button>
                        <span v-if="isLocked(props.row)" class="tag is-warning ml-2">{{ textsStore.getText('hypothesis.table.locked') }}</span>
                    </div>
                </b-table-column>
            </b-table>
            <div class="note-box mt-4">
                <b-message type="is-warning" has-icon>
                    <strong>Nota:</strong> {{ textsStore.getText('hypothesis.note') }}
                </b-message>
            </div>
        </div>
    </div>
    <!-- Botón cerrar/regresar en la esquina inferior derecha -->
    <div class="cerrar-container">
      <button
        class="cerrar-btn"
        v-if="!cerrado"
        @click="confirmarCerrar"
        :disabled="cerrado"
      >{{ textsStore.getText('hypothesis_section.close_button') }}</button>
      <button
        class="cerrar-btn"
        v-else-if="state !== null && state === '0'"
        @click="confirmarRegresar"
      >{{ textsStore.getText('hypothesis_section.return_button') }}</button>
    </div>
    <!-- Modal de confirmación -->
    <div v-if="mostrarModal" class="modal-confirm">
      <div class="modal-content">
        <p>{{ textsStore.getText('hypothesis_section.close_confirm_message') }}</p>
        <button @click="cerrarModulo">{{ textsStore.getText('hypothesis_section.confirm_yes') }}</button>
        <button @click="mostrarModal = false">{{ textsStore.getText('hypothesis_section.confirm_no') }}</button>
      </div>
    </div>
    <!-- Modal de confirmación para regresar -->
    <div v-if="mostrarModalRegresar" class="modal-confirm">
      <div class="modal-content">
        <p>¿Está seguro que desea regresar? Solo podrá hacer esto una vez.</p>
        <button @click="regresarModulo">Sí, regresar</button>
        <button @click="mostrarModalRegresar = false">Cancelar</button>
      </div>
    </div>

</template>
<script>
import { useSectionStore } from '../../../../stores/section';
import { useFutureDriversStore } from '../../../../stores/futureDrivers';
import { useTextsStore } from '../../../../stores/texts';
import { useTraceabilityStore } from '../../../../stores/traceability';
import { ref, onMounted, onBeforeUnmount, watch, computed, getCurrentInstance, inject } from 'vue';
import { storeToRefs } from 'pinia';
import { useSessionStore } from '../../../../stores/session';
import axios from 'axios';
import InfoBannerComponent from '../../ui/InfoBannerComponent.vue';

export default {
    name: 'DirectionFutureMainComponent',
    components: {
        InfoBannerComponent,
    },
    setup() {
        
        const sectionStore = useSectionStore();
        const futureDriversStore = useFutureDriversStore();
        const textsStore = useTextsStore();
        const traceabilityStore = useTraceabilityStore();
        const { drivers } = storeToRefs(futureDriversStore);
        const localDescriptionsH0 = ref([]);
        const localDescriptionsH1 = ref([]);
        const editingRow = ref(null);
        const sessionStore = useSessionStore();
        const cerrado = ref(false);
        const mostrarModal = ref(false);
        const mostrarModalRegresar = ref(false);
        const state = ref(null); 

        const countWords = (text) => {
            if (!text) return 0;
            return text.trim().split(/\s+/).filter(word => word.length > 0).length;
        };

        const limitWords = (text, maxWords = 40) => {
            if (!text) return text;
            const words = text.trim().split(/\s+/).filter(word => word.length > 0);
            if (words.length <= maxWords) return text;
            return words.slice(0, maxWords).join(' ');
        };

        const getWordCountClass = (wordCount) => {
            if (wordCount > 40) return 'has-text-danger';
            if (wordCount > 35) return 'has-text-warning';
            return 'has-text-grey';
        };

        watch(
            () => futureDriversStore.drivers,
            (newVal) => {
                if (newVal && newVal.length > 0) {
                    newVal.forEach(d => {
                        localDescriptionsH0.value[d.variable_id] = d.descriptionH0 || '';
                        localDescriptionsH1.value[d.variable_id] = d.descriptionH1 || '';
                    });
                }
            },
            { immediate: true, deep: true }
        );

        watch(drivers, (newDrivers) => {
            if (newDrivers && newDrivers.length > 0) {
                newDrivers.forEach((driver) => {
                    localDescriptionsH0.value[driver.variable_id] = driver.descriptionH0 || '';
                    localDescriptionsH1.value[driver.variable_id] = driver.descriptionH1 || '';
                });
            }
        }, { immediate: true, deep: true });

        const isLocked = (row) => {
            
            const locked = (row.stateH0 === '1' || row.stateH1 === '1');
            return locked;
        };

        const handleEditSave = async (row, index) => {
            
            if (isLocked(row)) {
                return;
            }

            if (editingRow.value === row.variable_id) {
                
                const h0Text = localDescriptionsH0.value[row.variable_id] || '';
                const h1Text = localDescriptionsH1.value[row.variable_id] || '';

                const result = await futureDriversStore.saveBothHypotheses(
                    row.variable_id,  
                    'H' + (index + 1), 
                    h0Text,           
                    h1Text,           
                    row.zone_id || 1, 
                    undefined         
                );
                
                if (result && result.success) {
                    editingRow.value = null;
                    
                    await futureDriversStore.fetchDrivers();
                }
            } else {
                editingRow.value = row.variable_id;
            }
        };

        const handleInput = (event, row, type) => {
            const text = event.target.value;
            const wordCount = countWords(text);

            if (wordCount > 40) {
                const limitedText = limitWords(text, 40);
                if (type === 'H0') {
                    localDescriptionsH0.value[row.variable_id] = limitedText;
                } else {
                    localDescriptionsH1.value[row.variable_id] = limitedText;
                }
                return;
            }

            if (type === 'H0') {
                localDescriptionsH0.value[row.variable_id] = text;
            } else {
                localDescriptionsH1.value[row.variable_id] = text;
            }
        };

        const handleKeyDown = (event, row, type) => {
            const text = event.target.value;
            const wordCount = countWords(text);

            if (wordCount > 40 && 
                !['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Enter'].includes(event.key)) {
                event.preventDefault();
            }
        };

        const handlePaste = (event, row, type) => {
            event.preventDefault();
            const pastedText = event.clipboardData.getData('text');
            const currentText = event.target.value;
            const combinedText = currentText + pastedText;
            const limitedText = limitWords(combinedText, 40);
            
            if (type === 'H0') {
                localDescriptionsH0.value[row.variable_id] = limitedText;
            } else {
                localDescriptionsH1.value[row.variable_id] = limitedText;
            }
        };

        const loadTriedValue = async () => {
            try {
                const response = await axios.get('/traceability/current-route-state');
                if (response.data && response.data.success && response.data.state !== undefined) {
                    state.value = response.data.state;
                }
            } catch (error) {
                console.error('Error al cargar state:', error);
            }
        };

        const incrementTried = async () => {
            try {
                await axios.put('/traceability/current-route-state', { state: '1' });
                state.value = '1';
            } catch (error) {
                console.error('Error al actualizar state:', error);
            }
        };

        onMounted(async () => {
            
            sectionStore.setTitleSection('Direccionadores de futuro');

            const user = JSON.parse(localStorage.getItem('user')) || {};
            const cerradoKey = 'hypothesis_cerrado_' + (user.id || 'anon');
            const cerradoValue = localStorage.getItem(cerradoKey);
            cerrado.value = cerradoValue === 'true';
            
            await futureDriversStore.fetchDrivers();
            
            await loadTriedValue();
            
        });

        onBeforeUnmount(() => {
            
            sectionStore.clearDynamicButtons();
        });

        return { 
            sectionStore,
            futureDriversStore,
            textsStore,
            traceabilityStore,
            drivers,
            localDescriptionsH0,
            localDescriptionsH1,
            editingRow,
            sessionStore,
            cerrado,
            mostrarModal,
            mostrarModalRegresar,
            state,
            countWords,
            limitWords,
            getWordCountClass,
            isLocked,
            handleEditSave,
            handleInput,
            handleKeyDown,
            handlePaste,
            loadTriedValue,
            incrementTried,
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
            confirmarRegresar: () => { mostrarModalRegresar.value = true; },
            async regresarModulo() {
                mostrarModalRegresar.value = false;
                try {
                    
                    const result = await futureDriversStore.reopenAllHypotheses();
                    if (result.success) {
                    
                        await incrementTried();
                    
                        await loadTriedValue();
                    
                    localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'regresar', modulo: 'hypothesis' }));
                    
                    sessionStore.setActiveContent('main');
                    
                    const user = JSON.parse(localStorage.getItem('user')) || {};
                    const cerradoKey = 'hypothesis_cerrado_' + (user.id || 'anon');
                    localStorage.removeItem(cerradoKey);
                    cerrado.value = false;
                    } else {
                        if (typeof window !== 'undefined' && window.$buefy) {
                            window.$buefy.toast.open({
                                message: 'Error al reabrir el módulo de direccionadores de futuro',
                                type: 'is-danger'
                            });
                        }
                    }
                } catch (error) {
                    if (typeof window !== 'undefined' && window.$buefy) {
                        window.$buefy.toast.open({
                            message: 'Error al regresar el módulo de direccionadores de futuro',
                            type: 'is-danger'
                        });
                    }
                }
            }
        };
    },

    methods: {
        async cerrarModulo() {
            this.mostrarModal = false;
            try {
                
                const result = await this.futureDriversStore.closeAllHypotheses();
                if (result.success) {
                    
                    await this.traceabilityStore.markSectionCompleted('hypothesis');

                    localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'cerrar', modulo: 'hypothesis' }));
                this.$buefy.toast.open({
                    message: 'Módulo de direccionadores de futuro cerrado correctamente',
                    type: 'is-success'
                });
                this.sessionStore.setActiveContent('main');
                
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const cerradoKey = 'hypothesis_cerrado_' + (user.id || 'anon');
                localStorage.setItem(cerradoKey, 'true');
                this.cerrado = true;
                } else {
                    this.$buefy.toast.open({
                        message: 'Error al cerrar el módulo de direccionadores de futuro',
                        type: 'is-danger'
                    });
                }
            } catch (error) {
                this.$buefy.toast.open({
                    message: 'Error al cerrar el módulo de direccionadores de futuro',
                    type: 'is-danger'
                });
            }
        },

        confirmarCerrar() {
            this.mostrarModal = true;
        },

        async loadTriedValue() {
            try {
                const response = await axios.get('/traceability/current-route-state');
                if (response.data && response.data.success && response.data.state !== undefined) {
                    this.state = response.data.state;
                }
            } catch (error) {
                console.error('Error al cargar state:', error);
            }
        },

        async incrementTried() {
            try {
                await axios.put('/traceability/current-route-state', { state: '1' });
                this.state = '1';
            } catch (error) {
                console.error('Error al actualizar state:', error);
            }
        }
    }
};
</script>

<style scoped>
.variables-container {
    padding: 20px;
}
.b-table {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    font-size: 14px;
}

:deep(.b-table .table thead th) {
    height: 40px !important;
    min-height: 0 !important;
    padding-top: 6px !important;
    padding-bottom: 6px !important;
    padding-left: 8px !important;
    padding-right: 8px !important;
}

:deep(.b-table .table th),
:deep(.b-table .table td) {
    vertical-align: middle !important;
    height: 100px !important;
}
.b-table th {
    background-color: #EEF2FF;
    color: #4F46E5;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 13px;
    border: none;
    border-bottom: 2px solid #E0E7FF;
    min-height: 60px;
}
.b-table td {
    background-color: #fff;
    color: #1F2937;
    font-weight: 500;
    border: none;
    border-bottom: 1px solid #E0E7FF;
}
.b-table tr:nth-child(even) td {
    background-color: #FAFAFA;
}
.b-table tr:nth-child(odd) td {
    background-color: #FFFFFF;
}

.edit-area,
.textarea-container,
.actions-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.textarea-container {
    position: relative;
}
.word-counter {
    font-size: 11px;
    margin-top: 4px;
    text-align: right;
    font-weight: 500;
    width: 100%;
}
.b-table-column span {
    display: inline-block;
    vertical-align: middle;
}
.note-box {
    max-width: 600px;
    margin: 0 auto;
}
th.hypothesis-header {
    min-width: 180px;
    max-width: 350px;
    background: #f5f6fa;
    color: #b0b3c6;
    font-weight: 500;
    text-align: center !important;
    vertical-align: middle !important;
    padding: 12px;
}
.b-table td:first-child, .b-table td:nth-child(2) {
    height: 60px;
}
.b-table .textarea-container .input {
    margin: 0;
}
.b-table td > * {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
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