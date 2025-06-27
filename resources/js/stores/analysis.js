import { defineStore } from 'pinia';
import { useTextsStore } from './texts';

export const useAnalysisStore = defineStore('analysis', {
  state: () => ({
    zones: [], // Se llenará desde textsStore
    rows: [
      // Simulación de variables por zona, puedes cambiar los valores
      { key: 'poder', variables: ['V1'], comment: '', score: 0 },
      { key: 'conflicto', variables: ['V2'], comment: '', score: 0 },
      { key: 'indiferencia', variables: ['V3'], comment: '', score: 0 },
      { key: 'salida', variables: ['V4'], comment: '', score: 0 }
    ]
  }),
  actions: {
    initZones() {
      const textsStore = useTextsStore();
      this.zones = textsStore.analysis.zones;
    },
    setComment(index, value) {
      this.rows[index].comment = value;
    },
    setScore(index, value) {
      this.rows[index].score = value;
    },
    setVariables(index, variables) {
      this.rows[index].variables = variables;
    }
  }
}); 