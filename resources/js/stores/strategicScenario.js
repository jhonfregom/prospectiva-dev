import { defineStore } from 'pinia';
import axios from 'axios';

export const useStrategicScenarioStore = defineStore('strategicScenario', {
    state: () => ({
        scenario1: null,
        scenario2: null,
        scenario3: null,
        scenario4: null,
        loading: false,
        error: null,
    }),
    actions: {
        async fetchScenarios() {
            this.loading = true;
            try {
                const res = await axios.get('/scenarios');
                const scenarios = res.data.data || [];
                this.scenario1 = scenarios.find(s => s.num_scenario == 1) || null;
                this.scenario2 = scenarios.find(s => s.num_scenario == 2) || null;
                this.scenario3 = scenarios.find(s => s.num_scenario == 3) || null;
                this.scenario4 = scenarios.find(s => s.num_scenario == 4) || null;
                this.error = null;
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
            } finally {
                this.loading = false;
            }
        },
        async updateScenario(numScenario, payload) {
            this.loading = true;
            try {
                // El backend decide si crea o actualiza
                const res = await axios.post('/scenarios', { ...payload, num_scenario: numScenario });
                if (numScenario == 1) {
                    this.scenario1 = res.data.data;
                } else if (numScenario == 2) {
                    this.scenario2 = res.data.data;
                } else if (numScenario == 3) {
                    this.scenario3 = res.data.data;
                } else if (numScenario == 4) {
                    this.scenario4 = res.data.data;
                }
                this.error = null;
                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            } finally {
                this.loading = false;
            }
        },
        async saveScenario(numScenario, yearField, value) {
            this.loading = true;
            try {
                // Obtener el escenario actual para los valores por defecto
                const currentScenario = this[`scenario${numScenario}`];
                
                // Determinar qué contador de edición incrementar
                let editsField = 'edits_year1';
                if (yearField === 'year2') {
                    editsField = 'edits_year2';
                } else if (yearField === 'year3') {
                    editsField = 'edits_year3';
                }
                
                // Incrementar el contador correspondiente
                const currentEdits = currentScenario?.[editsField] || 0;
                const newEdits = currentEdits + 1;
                
                // Preparar el payload con el campo específico y los campos obligatorios
                const payload = {
                    num_scenario: numScenario,
                    [yearField]: value,
                    edits: currentScenario?.edits || 0,
                    state: currentScenario?.state || 0,
                    // Incluir los contadores de edición específicos con el incremento
                    edits_year1: yearField === 'year1' ? newEdits : (currentScenario?.edits_year1 || 0),
                    edits_year2: yearField === 'year2' ? newEdits : (currentScenario?.edits_year2 || 0),
                    edits_year3: yearField === 'year3' ? newEdits : (currentScenario?.edits_year3 || 0)
                };
                
                // El backend decide si crea o actualiza
                const res = await axios.post('/scenarios', payload);
                
                // Recargar los datos para obtener el estado actualizado
                await this.fetchScenarios();
                
                this.error = null;
                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            } finally {
                this.loading = false;
            }
        },
        async closeAllScenarios() {
            try {
                for (let i = 1; i <= 4; i++) {
                    const scenario = this[`scenario${i}`];
                    if (scenario) {
                        await axios.post('/scenarios', {
                            ...scenario,
                            state: 1,
                            edits_year1: 3,
                            edits_year2: 3,
                            edits_year3: 3,
                            num_scenario: i
                        });
                    }
                }
                await this.fetchScenarios();
                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            }
        }
    }
}); 