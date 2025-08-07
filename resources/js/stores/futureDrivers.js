import { defineStore } from 'pinia';
import axios from 'axios';
import { useTextsStore } from './texts';

export const useFutureDriversStore = defineStore('futureDrivers', {
    state: () => ({
        drivers: [], 
        isLoading: false,
        error: null
    }),
    getters: {
        getDriverByIndex: (state) => (index) => {
            return state.drivers[index] || {};
        },
        isLocked: (state) => (index) => {
            const driver = state.drivers[index];
            
            return driver ? (driver.stateH0 === '1' || driver.stateH1 === '1') : false;
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
                name_hypothesis: nameHypothesis, 
                description: description,         
                secondary_hypothesis: secondaryHypothesis, 
                zone_id: zoneId,
            };

            if (state !== undefined && state !== null) {
                payload.state = state;
            }
            
            try {
                
                const existing = this.drivers.find(d => d.variable_id === variableId);
                let recordId = null;
                
                if (existing) {
                    
                    if (secondaryHypothesis === 'H0') {
                        recordId = existing.idH0;
                    } else if (secondaryHypothesis === 'H1') {
                        recordId = existing.idH1;
                    }
                }
                
                if (existing && recordId) {
                    const response = await axios.put(`/hypothesis/${recordId}`, payload);
                } else {
                    const response = await axios.post('/hypothesis', payload);
                }
                return { success: true };
            } catch (error) {
                console.error('saveDriver - Error:', error.response?.data || error.message);
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            }
        },
        async saveBothHypotheses(variableId, nameHypothesis, h0Text, h1Text, zoneId, state) {
            try {

                const resultH0 = await this.saveDriver(variableId, nameHypothesis, h0Text, 'H0', zoneId, state);
                if (!resultH0.success) {
                    return resultH0;
                }

                const resultH1 = await this.saveDriver(variableId, nameHypothesis, h1Text, 'H1', zoneId, state);
                if (!resultH1.success) {
                    return resultH1;
                }

                await this.fetchDrivers();
                return { success: true, message: 'Hipótesis guardadas correctamente' };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            }
        },

        async closeAllHypotheses() {
            try {
                const response = await axios.post('/hypothesis/close-all');
                if (response.data.status === 200) {
                    await this.fetchDrivers();
                    return { success: true };
                }
                return { success: false, message: response.data.message };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            }
        },

        async reopenAllHypotheses() {
            try {
                const response = await axios.post('/hypothesis/reopen-all');
                if (response.data.status === 200) {
                    await this.fetchDrivers();
                    return { success: true };
                }
                return { success: false, message: response.data.message };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            }
        },

        clearError() {
            this.error = null;
        }
    }
});