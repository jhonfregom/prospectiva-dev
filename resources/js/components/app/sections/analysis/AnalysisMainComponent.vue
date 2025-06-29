<template>
  <div class="variables-container">
    <b-message type="is-info" has-icon>
      {{ descriptionWithCount }}
    </b-message>
    <b-table
      :data="rows"
      :striped="true"
      :hoverable="true"
      :bordered="false"
      :narrowed="true"
      :loading="false"
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
        <b-input
          v-model="props.row.comment"
          type="textarea"
          :disabled="props.row.state === '1' || editingRow !== props.row.key"
          placeholder="Escribe tu análisis..."
          :rows="4"
          style="min-width:220px; max-width:400px; resize:vertical;"
          @input="onCommentInput(props.row)"
          @keyup="onCommentInput(props.row)"
          @paste="onCommentInput(props.row)"
          @cut="onCommentInput(props.row)"
        />
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
import axios from 'axios';
import { debounce } from 'lodash';

export default {
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
      return textsStore.analysis.description.replace(/de \d+ variables/i, `de ${count} variables`);
    });

    // Cargar análisis guardados al montar
    async function loadSavedAnalysis() {
      try {
        console.log('Cargando análisis para usuario autenticado');
        console.log('Session store participant:', sessionStore.participant);
        
        console.log('Filas antes de cargar:', rows.value.map(row => ({
          key: row.key,
          state: row.state,
          comment: row.comment,
          score: row.score
        })));
        
        const res = await axios.get('/variables-map-analysis/user');
        console.log('Análisis recibidos del backend:', res.data);
        
        if (res.data && res.data.data) {
          res.data.data.forEach(analysis => {
            console.log('Procesando análisis:', analysis);
            // El backend ya convierte el ID a nombre, así que usamos directamente
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
        
        console.log('Filas después de cargar:', rows.value.map(row => ({
          key: row.key,
          state: row.state,
          comment: row.comment,
          score: row.score,
          isBlocked: row.state === '1'
        })));
      } catch (e) {
        console.error('Error al cargar análisis:', e);
      }
    }

    // Guardar o crear análisis en backend
    async function saveOrCreateAnalysis(row, isManualSave = false) {
      try {
        const payload = {
          description: row.comment || '',
          score: Number(row.score) || 0,
          zone_id: row.key, // Enviar el nombre de la zona directamente
          state: String(row.state || 0), // Convertir a string para el enum
          is_manual_save: isManualSave // Indicar si es guardado manual
        };
        console.log('Datos enviados al backend:', payload);
        const res = await axios.post('/variables-map-analysis/save', payload);
        if (res.data && res.data.data) {
          row.state = res.data.data.state;
        }
      } catch (e) {
        console.error('Error al guardar análisis:', e);
      }
    }

    // Al hacer click en el botón
    async function handleEditSave(row) {
      if (row.state === '1') return; // Verificar si está bloqueado
      
      if (editingRow.value === row.key) {
        // Guardar manualmente
        console.log('Guardando manualmente análisis:', row.key);
        debouncedSaveAnalysis.cancel(); // Cancelar guardado automático pendiente
        await saveOrCreateAnalysis(row, true);
        editingRow.value = null;
      } else {
        // Entrar en modo edición
        // Solo crear análisis vacío si no existe ninguno
        if (!row.comment && !row.score && row.state === '0') {
          console.log('Creando análisis vacío para:', row.key);
          await saveOrCreateAnalysis(row, true);
        }
        editingRow.value = row.key;
      }
    }

    onMounted(async () => {
      console.log('onMounted iniciado');
      sectionStore.setTitleSection(textsStore.analysis.title);
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

    function getZoneName(key) {
      const zone = textsStore.analysis.zones.find(z => z.key === key);
      return zone ? zone.name : key;
    }
    function getDiagnosis(score) {
      const diag = textsStore.analysis.diagnosis.find(d => score >= d.min && score <= d.max);
      return diag || { text: '', color: '' };
    }
    function getDiagnosisClass(score) {
      const diag = getDiagnosis(score);
      if (diag.text === 'DEBES MEJORAR') return 'has-text-danger';
      if (diag.text === 'FALTA ALGO MAS') return 'has-text-warning';
      if (diag.text === 'UN ESFUERZO MAS') return 'has-text-info';
      if (diag.text === 'LO LOGRASTE') return 'has-text-success';
      return '';
    }

    function onCommentInput(row) {
      // El watcher se encarga de actualizar el puntaje automáticamente
      // Solo guardar automáticamente si hay contenido y estamos en modo edición
      if (row.comment && row.comment.trim().length > 0 && editingRow.value === row.key) {
        debouncedSaveAnalysis(row);
      }
    }

    // Función debounced para guardar automáticamente
    const debouncedSaveAnalysis = debounce(async (row) => {
      // Solo guardar si estamos en modo edición y hay contenido
      if (editingRow.value === row.key && row.comment && row.comment.trim().length > 0) {
        console.log('Guardando automáticamente análisis:', row.key);
        await saveOrCreateAnalysis(row, false);
      }
    }, 1000); // Guardar después de 1 segundo de inactividad

    // Función para cancelar el guardado automático
    const cancelAutoSave = debounce(() => {
      // Esta función cancela el guardado automático
    }, 0);

    function updateVariablesByZone() {
      if (!data.value || data.value.length === 0) return;
      const maxX = Math.max(...data.value.map(v => v.dependencia), 10);
      const maxY = Math.max(...data.value.map(v => v.influencia), 12);
      const centroX = maxX / 2;
      const centroY = maxY / 2;
      rows.value.forEach(r => r.variables = []);
      // Limpiar marcas de frontera
      rows.value.forEach(r => r.frontierVars = []);
      data.value.forEach(v => {
        let zona = '';
        let esFrontera = false;
        // Detectar frontera
        if (v.dependencia === centroX || v.influencia === centroY) {
          esFrontera = true;
          // Prioridad: Conflicto > Poder > Salida > Indiferencia
          if (v.dependencia > centroX && v.influencia >= centroY) zona = 'conflicto';
          else if (v.dependencia <= centroX && v.influencia > centroY) zona = 'poder';
          else if (v.dependencia > centroX && v.influencia < centroY) zona = 'salida';
          else zona = 'indiferencia';
        } else {
          if (v.dependencia <= centroX && v.influencia > centroY) zona = 'poder';
          else if (v.dependencia > centroX && v.influencia >= centroY) zona = 'conflicto';
          else if (v.dependencia <= centroX && v.influencia <= centroY) zona = 'indiferencia';
          else if (v.dependencia > centroX && v.influencia < centroY) zona = 'salida';
        }
        const row = rows.value.find(r => r.key === zona);
        if (row) {
          row.variables.push({ codigo: v.codigo, frontera: esFrontera });
        }
      });
    }

    function groupByRows(arr, size) {
      const grouped = [];
      for (let i = 0; i < arr.length; i += size) {
        grouped.push(arr.slice(i, i + size));
      }
      return grouped;
    }

    return { textsStore, rows, getZoneName, getDiagnosis, getDiagnosisClass, onCommentInput, descriptionWithCount, groupByRows, editingRow, handleEditSave };
  }
};
</script>

<style lang="scss" scoped>
.variables-container {
    padding: 20px;
}

.b-table {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  font-size: 14px;
}
.b-table th, .b-table td {
  text-align: center !important;
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
</style> 