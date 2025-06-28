import { defineStore } from 'pinia';
import { useTextsStore } from './texts';

export const useAnalysisStore = defineStore('analysis', {
  state: () => ({
    zones: [], // Se llenará desde textsStore
    rows: [
      // Filas vacías que se llenarán dinámicamente
      { key: 'poder', variables: [], comment: '', score: 0 },
      { key: 'conflicto', variables: [], comment: '', score: 0 },
      { key: 'indiferencia', variables: [], comment: '', score: 0 },
      { key: 'salida', variables: [], comment: '', score: 0 }
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