import { defineStore } from 'pinia';
import axios from 'axios';
import { useTextsStore } from './texts';

export const useFutureDriversStore = defineStore('futureDrivers', {
    state: () => ({
        drivers: [], // [{ id, variable_id, variable_name, descriptionH0, descriptionH1, state }]
        isLoading: false,
        error: null
    }),
    getters: {
        getDriverByIndex: (state) => (index) => {
            return state.drivers[index] || {};
        },
        isLocked: (state) => (index) => {
            const driver = state.drivers[index];
            return driver ? driver.state === '1' : false;
        }
    },
    actions: {
        async fetchDrivers() {
            this.isLoading = true;
            try {
                const response = await axios.get('/hypothesis');
                if (response.data.status === 200) {
                    this.drivers = response.data.data;
                }
            } catch (error) {
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        },
        async saveDriver(variableId, nameHypothesis, description, secondaryHypothesis, zoneId, state) {
            const payload = {
                variable_id: variableId,
                name_hypothesis: nameHypothesis, // 'H1' o 'H2'
                description: description,         // texto del textarea
                secondary_hypothesis: secondaryHypothesis, // 'H0' o 'H1'
                zone_id: zoneId,
                state: state
            };
            console.log('Payload enviado:', payload);
            try {
                await axios.post('/hypothesis', payload);
                return { success: true };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            }
        },
        async saveBothHypotheses(variableId, nameHypothesis, h0Text, h1Text, zoneId, state) {
            // Guarda H0
            await this.saveDriver(variableId, nameHypothesis, h0Text, 'H0', zoneId, state);
            // Guarda H1
            await this.saveDriver(variableId, nameHypothesis, h1Text, 'H1', zoneId, state);
            await this.fetchDrivers();
        },
        clearError() {
            this.error = null;
        }
    }
}); 