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
        }
    }
}); 