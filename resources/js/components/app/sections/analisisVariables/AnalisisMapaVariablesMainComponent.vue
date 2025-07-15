<template>
    <div class="analisis-mapa-variables-container">
        <b-message type="is-info" has-icon>
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
                                :style="v.frontera ? 'border: 2px solid #f9d423; box-shadow: 0 0 4px #f9d423;' : ''"
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
                        @input="onCommentInput(props.row, $event)"
                        @keyup="onCommentInput(props.row, $event)"
                        @paste="onCommentInput(props.row, $event)"
                        @cut="onCommentInput(props.row, $event)"
                    />
                </div>
            </b-table-column>
            <b-table-column field="actions" label="Acciones" v-slot="props" centered>
                <b-button 
                    type="is-info"
                    size="is-small"
                    icon-left="edit"
                    @click="handleEditSave(props.row)"
                    outlined
                    :disabled="props.row.state === '1'"
                >
                    {{ editingRow === props.row.key ? 'Guardar' : 'Editar' }}
                </b-button>
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
</template>

<script>
import { useAnalysisStore } from '../../../../stores/analysis';
import { useTextsStore } from '../../../../stores/texts';
import { useGraphicsStore } from '../../../../stores/graphics';
import { useSectionStore } from '../../../../stores/section';
import { useSessionStore } from '../../../../stores/session';
import { onMounted, watch, computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { debounce } from 'lodash';

export default {
    name: 'AnalisisMapaVariablesMainComponent',
    
    components: {
    },

    setup() {
        const analysisStore = useAnalysisStore();
        const textsStore = useTextsStore();
        const graphicsStore = useGraphicsStore();
        const sectionStore = useSectionStore();
        const sessionStore = useSessionStore();
        const { rows } = storeToRefs(analysisStore);
        const { data } = storeToRefs(graphicsStore);
        const editingRow = ref(null);

        const descriptionWithCount = computed(() => {
            const count = data.value ? data.value.length : 0;
            return textsStore.getText('analysis.description').replace(/de \d+ variables/i, `de ${count} variables`);
        });

        // Cargar análisis guardados al montar
        async function loadSavedAnalysis() {
            try {
                console.log('Cargando análisis para usuario autenticado');
                
                const result = await analysisStore.fetchAnalyses();
                if (result && result.data) {
                    result.data.forEach(analysis => {
                        console.log('Procesando análisis:', analysis);
                        const row = rows.value.find(r => r.key === analysis.zone_id);
                        console.log('Fila encontrada:', row ? row.key : 'NO ENCONTRADA');
                        
                        if (row) {
                            row.comment = analysis.description || '';
                            row.score = analysis.score || 0;
                            row.state = analysis.state || '0';
                            console.log(`Asignado a zona ${row.key}:`, {
                                comment: row.comment,
                                score: row.score,
                                state: row.state,
                                isBlocked: row.state === '1'
                            });
                        }
                    });
                }
            } catch (e) {
                console.error('Error al cargar análisis:', e);
            }
        }

        // Guardar o crear análisis en backend
        async function saveOrCreateAnalysis(row, isManualSave = false) {
            try {
                const analysisData = {
                    zone_id: row.key,
                    description: row.comment || '',
                    score: Number(row.score) || 0,
                    state: String(row.state || 0),
                    is_manual_save: isManualSave
                };
                
                console.log('Datos enviados al backend:', analysisData);
                const result = await analysisStore.saveAnalysis(analysisData);
                
                if (result.success && result.data) {
                    row.state = result.data.state;
                }
            } catch (e) {
                console.error('Error al guardar análisis:', e);
            }
        }

        // Al hacer click en el botón
        async function handleEditSave(row) {
            console.log('handleEditSave - Zone:', row.key, 'Current state:', row.state);
            
            if (row.state === '1') {
                console.log('Análisis bloqueado, no se puede editar:', row.key);
                return; // Verificar si está bloqueado
            }
            
            if (editingRow.value === row.key) {
                // Guardar manualmente
                console.log('Guardando manualmente análisis:', row.key);
                debouncedSaveAnalysis.cancel(); // Cancelar guardado automático pendiente
                const result = await saveOrCreateAnalysis(row, true);
                console.log('After save - Zone:', row.key, 'New state:', row.state);
                editingRow.value = null;
            } else {
                // Entrar en modo edición
                editingRow.value = row.key;
            }
        }

        // Función para agrupar variables en filas
        function groupByRows(variables, itemsPerRow) {
            const groups = [];
            for (let i = 0; i < variables.length; i += itemsPerRow) {
                groups.push(variables.slice(i, i + itemsPerRow));
            }
            return groups;
        }

        // Función para obtener el nombre de la zona
        function getZoneName(key) {
            const zone = textsStore.getText('analysis.zones').find(z => z.key === key);
            return zone ? zone.name : key;
        }

        // Función para obtener el diagnóstico
        function getDiagnosis(score) {
            const diagnosis = textsStore.getText('analysis.diagnosis');
            for (const diag of diagnosis) {
                if (score >= diag.min && score <= diag.max) {
                    return diag;
                }
            }
            return diagnosis[0]; // Por defecto
        }

        // Función para obtener la clase CSS del diagnóstico
        function getDiagnosisClass(score) {
            const diag = getDiagnosis(score);
            if (diag.text === 'DEBES MEJORAR') return 'has-text-danger';
            if (diag.text === 'FALTA ALGO MAS') return 'has-text-warning';
            if (diag.text === 'UN ESFUERZO MAS') return 'has-text-info';
            if (diag.text === 'LO LOGRASTE') return 'has-text-success';
            return '';
        }

        // Función para actualizar variables por zona
        function updateVariablesByZone() {
            if (!data.value || data.value.length === 0) return;
            
            const maxX = Math.max(...data.value.map(v => v.influencia), 10);
            const maxY = Math.max(...data.value.map(v => v.dependencia), 12);
            const centroX = maxX / 2;
            const centroY = maxY / 2;
            
            rows.value.forEach(r => r.variables = []);
            
            data.value.forEach(v => {
                let zona = '';
                let esFrontera = false;
                
                if (v.influencia === centroX || v.dependencia === centroY) {
                    esFrontera = true;
                    if (v.influencia > centroX && v.dependencia >= centroY) zona = 'conflicto';
                    else if (v.influencia <= centroX && v.dependencia > centroY) zona = 'poder';
                    else if (v.influencia > centroX && v.dependencia < centroY) zona = 'salida';
                    else zona = 'indiferencia';
                } else {
                    if (v.influencia <= centroX && v.dependencia > centroY) zona = 'poder';
                    else if (v.influencia > centroX && v.dependencia >= centroY) zona = 'conflicto';
                    else if (v.influencia <= centroX && v.dependencia <= centroY) zona = 'indiferencia';
                    else if (v.influencia > centroX && v.dependencia < centroY) zona = 'salida';
                }
                
                const row = rows.value.find(r => r.key === zona);
                if (row) {
                    row.variables.push({ codigo: v.codigo, frontera: esFrontera });
                }
            });
        }

        // Guardado automático con debounce
        const debouncedSaveAnalysis = debounce((row) => {
            saveOrCreateAnalysis(row, false);
        }, 1000);

        // Constante para el límite de caracteres
        const MAX_CHARACTERS = 255;

        // Función para manejar input del comentario
        function onCommentInput(row, event = null) {
            if (row.state === '1') {
                console.log('Análisis bloqueado, no se permiten cambios:', row.key);
                return; // No permitir cambios si está bloqueado
            }

            // Si es un evento de pegado, manejar de forma especial
            if (event && event.type === 'paste') {
                const pastedText = (event.clipboardData || window.clipboardData).getData('text');
                const currentText = row.comment || '';
                const combinedText = currentText + pastedText;
                
                if (combinedText.length <= MAX_CHARACTERS) {
                    // Permitir el pegado normal
                    return;
                } else {
                    // Prevenir el pegado por defecto y manejar manualmente
                    event.preventDefault();
                    // Calcular cuántos caracteres se pueden agregar
                    const availableSpace = MAX_CHARACTERS - currentText.length;
                    if (availableSpace > 0) {
                        const truncatedPastedText = pastedText.substring(0, availableSpace);
                        row.comment = currentText + truncatedPastedText;
                        console.log(`Texto pegado truncado. Límite de ${MAX_CHARACTERS} caracteres alcanzado`);
                    } else {
                        console.log(`No se puede pegar más texto. Límite de ${MAX_CHARACTERS} caracteres alcanzado`);
                    }
                }
            } else {
                // Para input normal y keyup
                const text = row.comment || '';
                if (text.length > MAX_CHARACTERS) {
                    // Truncar el texto al límite
                    row.comment = text.substring(0, MAX_CHARACTERS);
                    console.log(`Límite de ${MAX_CHARACTERS} caracteres alcanzado`);
                }
            }
            
            // Guardado automático
            debouncedSaveAnalysis(row);
        }

        onMounted(async () => {
            console.log('onMounted iniciado');
            sectionStore.setTitleSection(textsStore.getText('analysis.title'));
            analysisStore.initZones();
            
            console.log('Filas inicializadas:', rows.value.map(row => ({
                key: row.key,
                state: row.state,
                comment: row.comment,
                score: row.score
            })));
            
            await graphicsStore.fetchGraphicsData();
            updateVariablesByZone();
            
            // Cargar análisis después de que todo esté inicializado
            await loadSavedAnalysis();
            
            // Verificar el estado después de cargar
            console.log('Estado final de las filas:', rows.value.map(row => ({
                key: row.key,
                state: row.state,
                isBlocked: row.state === '1',
                comment: row.comment,
                score: row.score
            })));
        });

        // Actualiza las variables por zona en tiempo real
        watch(data, () => {
            updateVariablesByZone();
        }, { immediate: true });

        // Watcher para actualizar el puntaje cuando cambie el comentario
        watch(rows, () => {
            rows.value.forEach(row => {
                if (row.comment !== undefined) {
                    const words = (row.comment || '').trim().split(/\s+/).filter(w => w.length > 0);
                    row.score = words.length;
                }
            });
        }, { deep: true });

        return {
            analysisStore,
            textsStore,
            graphicsStore,
            sectionStore,
            sessionStore,
            rows,
            data,
            editingRow,
            descriptionWithCount,
            loadSavedAnalysis,
            saveOrCreateAnalysis,
            handleEditSave,
            groupByRows,
            getZoneName,
            getDiagnosis,
            getDiagnosisClass,
            onCommentInput,
            updateVariablesByZone,
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

/* Centrado vertical SOLO en filas de datos (tbody td) de la tabla de análisis mapa de variables */
:deep(.b-table .table tbody td) {
    vertical-align: middle !important;
    height: 80px !important;
}

/* Centrar textareas en la columna de análisis */
.textarea-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
}

/* Centrar el contenido del textarea */
:deep(.textarea-container .b-input[type="textarea"]) {
    text-align: center !important;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Asegurar que el textarea esté centrado dentro de su contenedor */
:deep(.textarea-container .b-input[type="textarea"] textarea) {
    text-align: center !important;
    resize: vertical;
    min-height: 80px;
}
</style>