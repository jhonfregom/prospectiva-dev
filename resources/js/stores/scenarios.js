import { defineStore } from 'pinia';
import axios from 'axios';

export const useScenariosStore = defineStore('scenarios', {
    state: () => ({
        scenarios: [],
        isLoading: false,
    }),
    actions: {
        async fetchScenarios() {
            this.isLoading = true;
            try {
                const res = await axios.get('/scenarios');
                this.scenarios = res.data;
            } finally {
                this.isLoading = false;
            }
        },
        async updateScenario(id, data) {
            this.isLoading = true;
            try {
                const res = await axios.put(`/scenarios/${id}`, data);
                const idx = this.scenarios.findIndex(s => s.id === id);
                if (idx !== -1) this.scenarios[idx] = res.data;
            } finally {
                this.isLoading = false;
            }
        }
    }
}); 