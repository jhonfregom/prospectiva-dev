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

    async function loadSavedAnalysis() {
      try {
        const res = await axios.get('/variables-map-analysis/user');
        
        if (res.data && res.data.data) {
          res.data.data.forEach(analysis => {
            
            const row = rows.value.find(r => r.key === analysis.zone_id);
            
            if (row) {
              row.comment = analysis.description || '';
              row.score = analysis.score || 0;
              row.state = analysis.state || '0';
            }
          });
        }
      } catch (e) {
        
      }
    }

    async function saveOrCreateAnalysis(row, isManualSave = false) {
      try {
        const payload = {
          description: row.comment || '',
          score: Number(row.score) || 0,
          zone_id: row.key, 
          state: String(row.state || 0), 
          is_manual_save: isManualSave 
        };
        const res = await axios.post('/variables-map-analysis/save', payload);
        if (res.data && res.data.data) {
          row.state = res.data.data.state;
        }
      } catch (e) {
        
      }
    }

    async function handleEditSave(row) {
      if (row.state === '1') return; 
      
      if (editingRow.value === row.key) {
        
        debouncedSaveAnalysis.cancel(); 
        await saveOrCreateAnalysis(row, true);
        editingRow.value = null;
      } else {

        if (!row.comment && !row.score && row.state === '0') {
          await saveOrCreateAnalysis(row, true);
        }
        editingRow.value = row.key;
      }
    }

    onMounted(async () => {
      sectionStore.setTitleSection(textsStore.analysis.title);
      analysisStore.initZones();
      
      await graphicsStore.fetchGraphicsData();
      updateVariablesByZone();

      await loadSavedAnalysis();

    });

    watch(data, () => {
      updateVariablesByZone();
    }, { immediate: true });

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

      if (row.comment && row.comment.trim().length > 0 && editingRow.value === row.key) {
        debouncedSaveAnalysis(row);
      }
    }

    const debouncedSaveAnalysis = debounce(async (row) => {
      
      if (editingRow.value === row.key && row.comment && row.comment.trim().length > 0) {
        await saveOrCreateAnalysis(row, false);
      }
    }, 1000); 

    const cancelAutoSave = debounce(() => {
      
    }, 0);

    function updateVariablesByZone() {
      if (!data.value || data.value.length === 0) return;
      const maxX = Math.max(...data.value.map(v => v.dependencia), 10);
      const maxY = Math.max(...data.value.map(v => v.influencia), 12);
      const centroX = maxX / 2;
      const centroY = maxY / 2;
      rows.value.forEach(r => r.variables = []);
      
      rows.value.forEach(r => r.frontierVars = []);
      data.value.forEach(v => {
        let zona = '';
        let esFrontera = false;
        
        if (v.dependencia === centroX || v.influencia === centroY) {
          esFrontera = true;
          
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