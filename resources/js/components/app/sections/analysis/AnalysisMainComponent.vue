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
        <span>
          <span v-for="v in props.row.variables" :key="v.codigo" style="display:inline-block;text-align:center;">
            <b-tag
              type="is-info"
              class="mr-1"
              :style="v.frontera ? 'border: 2px solid #f9d423; box-shadow: 0 0 4px #f9d423;' : ''"
              :title="v.frontera ? 'En frontera: asignada a zona crítica' : ''"
            >
              {{ v.codigo }}
            </b-tag>
          </span>
        </span>
      </b-table-column>
      <b-table-column field="comment" label="ANÁLISIS" v-slot="props" centered>
        <b-input v-model="props.row.comment" placeholder="Escribe tu análisis..." @input="onCommentInput(props.row)" />
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
import { onMounted, watch, computed } from 'vue';
import { storeToRefs } from 'pinia';

export default {
  setup() {
    const analysisStore = useAnalysisStore();
    const textsStore = useTextsStore();
    const graphicsStore = useGraphicsStore();
    const sectionStore = useSectionStore();
    const { rows } = storeToRefs(analysisStore);
    const { data } = storeToRefs(graphicsStore);

    const descriptionWithCount = computed(() => {
      const count = data.value ? data.value.length : 0;
      return textsStore.analysis.description.replace(/de \d+ variables/i, `de ${count} variables`);
    });

    onMounted(async () => {
      sectionStore.setTitleSection(textsStore.analysis.title);
      analysisStore.initZones();
      await graphicsStore.fetchGraphicsData();
      updateVariablesByZone();
    });

    // Actualiza las variables por zona en tiempo real
    watch(data, () => {
      updateVariablesByZone();
    }, { immediate: true });

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
      // Calcular score por cantidad de palabras
      row.score = (row.comment || '').split(/\s+/).filter(w => w.length > 0).length;
    }

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

    return { textsStore, rows, getZoneName, getDiagnosis, getDiagnosisClass, onCommentInput, descriptionWithCount };
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