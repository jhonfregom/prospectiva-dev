<template>
    <div v-if="traceabilityStore.isLoading || !seccionesValidas" style="min-height:160px;"></div>
    <div v-else class="analisis-mapa-variables-container">
        <b-message type="is-info" has-icon class="description-message">
            {{ descriptionWithCount }}
        </b-message>
        <b-table
            :data="rows"
            :striped="true"
            :hoverable="true"
            :bordered="false"
            :narrowed="true"
            :loading="analysisStore.isLoading"
            icon-pack="fas"
        >
            <b-table-column field="zone" label="Ubicación" width="180" v-slot="props" centered>
                <span>{{ getZoneName(props.row.key) }}</span>
            </b-table-column>
            <b-table-column field="variables" label="Variables en la zona" v-slot="props" centered>
                <div>
                    <div v-for="(fila, idx) in groupByRows(props.row.variables, 3)" :key="'fila-' + idx" style="margin-bottom:2px;">
                        <span v-for="v in fila" :key="v.codigo" style="display:inline-block;text-align:center;">
                            <b-tag
                                type="is-info"
                                class="mr-1"
                                :style="v.frontera ? 'border: 2px solid #ff8c00; box-shadow: 0 0 4px #ff8c00;' : ''"
                                :title="v.frontera ? 'En frontera: asignada a zona crítica' : ''"
                            >
                                {{ v.codigo }}
                            </b-tag>
                        </span>
                    </div>
                </div>
            </b-table-column>
            <b-table-column field="comment" label="ANÁLISIS" v-slot="props" centered>
                <div class="textarea-container">
                    <b-input
                        v-model="props.row.comment"
                        type="textarea"
                        :disabled="props.row.state === '1' || editingRow !== props.row.key"
                        placeholder="Escribe tu análisis..."
                        :rows="4"
                        style="min-width:495px; max-width:900px; resize:vertical; text-align:center;"
                        @input="handleCommentChange(props.row)"
                        @keyup="handleCommentChange(props.row)"
                        @paste="handleCommentChange(props.row)"
                        @cut="handleCommentChange(props.row)"
                    />
                </div>
            </b-table-column>
            <b-table-column field="actions" label="Acciones" v-slot="props" centered>
                <edit-button-component
                    :is-editing="editingRow === props.row.key"
                    :is-locked="props.row.state === '1'"
                    edit-text="Editar"
                    save-text="Guardar"
                    locked-text="Bloqueado"
                    @click="handleEditSave(props.row)"
                />
            </b-table-column>
            <b-table-column field="score" label="PUNTAJE" v-slot="props" centered>
                <span>{{ props.row.score }}</span>
            </b-table-column>
            <b-table-column field="diagnosis" label="DIAGNÓSTICO" v-slot="props" centered>
                <span :class="getDiagnosisClass(props.row.score)" style="font-weight:bold;">
                    {{ getDiagnosis(props.row.score).text }}
                </span>
            </b-table-column>
        </b-table>
    </div>
    <!-- Botón cerrar en la esquina inferior derecha -->
    <div class="cerrar-container">
      <button
        class="cerrar-btn"
        v-if="!cerrado"
        @click="confirmarCerrar"
        :disabled="cerrado"
      >{{ textsStore.getText('analysis_section.close_button') }}</button>
      <button
        class="cerrar-btn"
        v-else-if="state !== null && state === '0'"
        @click="confirmarRegresar"
      >{{ textsStore.getText('analysis_section.return_button') }}</button>
    </div>
    <!-- Modal de confirmación -->
    <div v-if="mostrarModal" class="modal-confirm">
      <div class="modal-content">
        <p class="modal-text">{{ textsStore.getText('analysis_section.close_confirm_message') }}</p>
        <button @click="cerrarModulo">{{ textsStore.getText('analysis_section.confirm_yes') }}</button>
        <button @click="mostrarModal = false">{{ textsStore.getText('analysis_section.confirm_no') }}</button>
      </div>
    </div>
    <!-- Modal de confirmación para regresar -->
    <div v-if="mostrarModalRegresar" class="modal-confirm">
      <div class="modal-content">
        <p class="modal-text">{{ textsStore.getText('analysis_section.return_confirm_message') }}</p>
        <button @click="regresarModulo">{{ textsStore.getText('analysis_section.confirm_yes_return') }}</button>
        <button @click="mostrarModalRegresar = false">{{ textsStore.getText('analysis_section.confirm_no') }}</button>
      </div>
    </div>
</template>

<script>
import { useAnalysisStore } from '../../../../stores/analysis';
import { useTextsStore } from '../../../../stores/texts';
import { useGraphicsStore } from '../../../../stores/graphics';
import { useSectionStore } from '../../../../stores/section';
import { useSessionStore } from '../../../../stores/session';
import { onMounted, computed, ref, nextTick } from 'vue';
import { storeToRefs } from 'pinia';
import { debounce } from 'lodash';
import { useTraceabilityStore } from '../../../../stores/traceability';
import axios from 'axios';
import EditButtonComponent from '../../ui/EditButtonComponent.vue';

const CERRADO_KEY_PREFIX = 'analisis_cerrado_';

export default {
    name: 'AnalisisMapaVariablesMainComponent',
    
    components: {
        EditButtonComponent
    },

    data() {
        return {
            variables: [],
            isLoading: false,
            mostrarModal: false,
            mostrarModalRegresar: false,
            cerrado: false,
            state: null, 
            debouncedSaveAnalysis: null, 
        };
    },
    created() {
        
        const user = JSON.parse(localStorage.getItem('user')) || {};
        const cerradoKey = CERRADO_KEY_PREFIX + (user.id || 'anon');
        const cerradoValue = localStorage.getItem(cerradoKey);
        this.cerrado = cerradoValue === 'true';
    },
    setup() {
        const analysisStore = useAnalysisStore();
        const textsStore = useTextsStore();
        const graphicsStore = useGraphicsStore();
        const sectionStore = useSectionStore();
        const sessionStore = useSessionStore();
        const traceabilityStore = useTraceabilityStore();
        const { rows } = storeToRefs(analysisStore);
        const { data } = storeToRefs(graphicsStore);
        const editingRow = ref(null); 
        
        const descriptionWithCount = computed(() => {
            const count = data.value ? data.value.length : 0;
            return textsStore.getText('analysis.description').replace(/de \d+ variables/i, `de ${count} variables`);
        });
        
        const seccionesValidas = computed(() => {
            const secciones = traceabilityStore.availableSections;
            return secciones && secciones.analysis === true;
        });

        return {
            analysisStore,
            textsStore,
            graphicsStore,
            sectionStore,
            sessionStore,
            rows,
            data,
            descriptionWithCount,
            traceabilityStore,
            seccionesValidas,
            editingRow,
        };
    },
    mounted() {
        this.sectionStore.setTitleSection(this.textsStore.getText('analysis.title'));
        this.loadVariables();
        this.loadSavedAnalysis();
        
        this.loadTriedValue();
        
        this.debouncedSaveAnalysis = debounce(async (row) => {
            if (row.state !== '1') {
                await this.saveOrCreateAnalysis(row, false);
            }
        }, 1000);
    },

    beforeUnmount() {
        
        this.sectionStore.clearDynamicButtons();
        
        if (this.debouncedSaveAnalysis) {
            this.debouncedSaveAnalysis.cancel();
        }
    },
    methods: {
        async loadSavedAnalysis() {
            try {
                const result = await this.analysisStore.fetchAnalyses();
                if (result && result.data) {
                    result.data.forEach(analysis => {
                        const row = this.rows.find(r => r.key === analysis.zone_id);
                        if (row) {
                            row.comment = analysis.description || '';
                            row.score = analysis.score || 0;
                            row.state = analysis.state || '0';
                        }
                    });
                }
            } catch (e) {
                console.error('Error al cargar análisis:', e);
            }
        },
        async loadVariables() {
            try {
                
                await this.graphicsStore.fetchGraphicsData();

                if (!this.data || this.data.length === 0) {
                    console.warn('No hay datos de variables para la ruta actual');
                    return;
                }

                this.rows.forEach(row => {
                    const zoneVariables = this.data.filter(variable => {
                        
                        const zoneMapping = {
                            'poder': 'ZONA DE PODER',
                            'conflicto': 'ZONA DE CONFLICTO',
                            'salida': 'ZONA DE SALIDA',
                            'indiferencia': 'ZONA DE INDIFERENCIA'
                        };
                        return variable.zone === zoneMapping[row.key];
                    });
                    
                    row.variables = zoneVariables;
                });

                console.log('Variables por zona:', this.rows.map(row => ({
                    zona: row.key,
                    variables: row.variables.map(v => v.codigo)
                })));
            } catch (e) {
                console.error('Error al cargar variables:', e);
            }
        },
        groupByRows(variables, itemsPerRow) {
            const groups = [];
            for (let i = 0; i < variables.length; i += itemsPerRow) {
                groups.push(variables.slice(i, i + itemsPerRow));
            }
            return groups;
        },
        getZoneName(key) {
            const zone = this.textsStore.getText('analysis.zones').find(z => z.key === key);
            return zone ? zone.name : key;
        },
        getDiagnosis(score) {
            const diagnosis = this.textsStore.getText('analysis.diagnosis');
            for (const diag of diagnosis) {
                if (score >= diag.min && score <= diag.max) {
                    return diag;
                }
            }
            return diagnosis[0];
        },
        getDiagnosisClass(score) {
            const diag = this.getDiagnosis(score);
            if (diag.text === 'DEBES MEJORAR') return 'has-text-danger';
            if (diag.text === 'FALTA ALGO MAS') return 'has-text-warning';
            if (diag.text === 'UN ESFUERZO MAS') return 'has-text-info';
            if (diag.text === 'LO LOGRASTE') return 'has-text-success';
            return '';
        },
        handleCommentChange(row) {
            const words = row.comment ? row.comment.split(/\s+/).filter(word => word.length > 0) : [];
            row.score = words.length;
            
            this.debouncedSaveAnalysis(row);
        },
        async saveOrCreateAnalysis(row, isManualSave = false) {
            try {
                
                const zoneMapping = {
                    'poder': 'ZONA DE PODER',
                    'conflicto': 'ZONA DE CONFLICTO',
                    'salida': 'ZONA DE SALIDA',
                    'indiferencia': 'ZONA DE INDIFERENCIA'
                };
                
                const analysisData = {
                    zone_id: zoneMapping[row.key] || row.key,
                    description: row.comment || '',
                    score: Number(row.score) || 0,
                    state: String(row.state || 0),
                    is_manual_save: isManualSave
                };
                const result = await this.analysisStore.saveAnalysis(analysisData);
                if (result.success && result.data) {
                    row.state = result.data.state;
                    
                    if (isManualSave) {
                        await this.loadSavedAnalysis();
                    }
                }
            } catch (e) {
                console.error('Error al guardar análisis:', e);
            }
        },
        async updateAnalysisInServer(row) {
            try {
                const analyses = await this.analysisStore.fetchAnalyses();
                if (analyses && analyses.data) {
                    const existingAnalysis = analyses.data.find(analysis => analysis.zone_id === row.key);
                    if (existingAnalysis) {
                        const result = await this.analysisStore.updateAnalysis(existingAnalysis.id, {
                            description: row.comment || '',
                            score: Number(row.score) || 0,
                            state: '1' 
                        });
                        if (result.success && result.data) {
                            row.state = result.data.state;
                        }
                    } else {
                        await this.saveOrCreateAnalysis(row, true);
                    }
                }
            } catch (e) {
                console.error('Error al actualizar análisis:', e);
            }
        },
        async handleEditSave(row) {
            if (row.state === '1') return;
            if (this.editingRow === row.key) {
                
                if (this.debouncedSaveAnalysis) {
                this.debouncedSaveAnalysis.cancel();
                }
                try {
                await this.saveOrCreateAnalysis(row, true);
                } catch (error) {
                    console.error('Error al guardar análisis:', error);
                }
                this.editingRow = null;
                await nextTick();
            } else {
                this.editingRow = row.key;
                await nextTick();
            }
        },
        confirmarCerrar() {
            this.mostrarModal = true;
        },
        confirmarRegresar() {
            this.mostrarModalRegresar = true;
        },
        async cerrarModulo() {
            this.mostrarModal = false;
            
            const result = await this.analysisStore.closeAllAnalyses();
            if (result.success) {
                
                await this.traceabilityStore.markSectionCompleted('analysis');

                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'cerrar', modulo: 'analysis' }));
                this.$buefy.toast.open({
                    message: 'Módulo de análisis de variables cerrado correctamente',
                    type: 'is-success'
                });
                this.sessionStore.setActiveContent('main');
                
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const cerradoKey = CERRADO_KEY_PREFIX + (user.id || 'anon');
                localStorage.setItem(cerradoKey, 'true');
                this.cerrado = true;
            } else {
                this.$buefy.toast.open({
                    message: 'Error al cerrar el módulo de análisis de variables',
                    type: 'is-danger'
                });
            }
        },
        async regresarModulo() {
            this.mostrarModalRegresar = false;
            try {
                
                const result = await this.analysisStore.reopenAllAnalyses();
                if (result.success) {
                
                await this.incrementTried();
                
                await this.loadTriedValue();
                
                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'regresar', modulo: 'analysis' }));
                this.$buefy.toast.open({
                    message: 'Módulo de análisis de variables reabierto correctamente',
                    type: 'is-success'
                });
                this.sessionStore.setActiveContent('main');
                
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const cerradoKey = CERRADO_KEY_PREFIX + (user.id || 'anon');
                localStorage.removeItem(cerradoKey);
                this.cerrado = false;
                } else {
                    this.$buefy.toast.open({
                        message: 'Error al reabrir el módulo de análisis de variables',
                        type: 'is-danger'
                    });
                }
            } catch (error) {
                this.$buefy.toast.open({
                    message: 'Error al regresar el módulo de análisis de variables',
                    type: 'is-danger'
                });
            }
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
.analisis-mapa-variables-container {
    position: relative; 
}

.variables-container {
    padding: 20px;
}

.variables-container .b-table {
    margin-top: 20px;
}

.variables-container .b-table .table {
    background-color: white;
}

.variables-container .b-table .table th {
    background-color: #f5f5f5;
    font-weight: bold;
    text-align: center;
}

.variables-container .b-table .table td {
    vertical-align: middle;
    text-align: center;
}

.variables-container .b-message {
    margin-bottom: 20px;
}

.description-message {
    text-align: justify !important;
    line-height: 1.6;
}

.modal-text {
    text-align: justify !important;
    line-height: 1.5;
    margin-bottom: 20px;
}

.has-text-danger {
    color: #ff3860 !important;
    font-weight: 600;
}
.has-text-warning {
    color: #ffdd57 !important;
    font-weight: 600;
}
.has-text-info {
    color: #3298dc !important;
    font-weight: 600;
}
.has-text-success {
    color: #48c774 !important;
    font-weight: 600;
}

:deep(.b-table .table tbody td) {
    vertical-align: middle !important;
    height: 80px !important;
}

.textarea-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
}

:deep(.textarea-container .b-input[type="textarea"]) {
    text-align: center !important;
    display: flex;
    align-items: center;
    justify-content: center;
}

:deep(.textarea-container .b-input[type="textarea"] textarea) {
    text-align: center !important;
    resize: vertical;
    min-height: 80px;
}

.cerrar-container {
  position: fixed;
  bottom: 32px;
  right: 48px;
  z-index: 100;
}
.cerrar-btn {
  background: #005883;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 14px 32px;
  font-size: 1.2rem;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(0,88,131,0.2);
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
  background: #005883;
  color: white;
  transition: background 0.2s;
}

.modal-content button:hover {
  background: #004466;
}
</style>