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
            // Verificar si cualquiera de las dos hipótesis está bloqueada
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
                name_hypothesis: nameHypothesis, // 'H1' o 'H2'
                description: description,         // texto del textarea
                secondary_hypothesis: secondaryHypothesis, // 'H0' o 'H1'
                zone_id: zoneId,
            };
            
            // Solo enviar state si se especifica explícitamente (para cerrar/regresar módulo)
            if (state !== undefined && state !== null) {
                payload.state = state;
            }
            
            try {
                // Buscar el driver existente
                const existing = this.drivers.find(d => d.variable_id === variableId);
                let recordId = null;
                
                if (existing) {
                    // Si es H0, usar idH0, si es H1, usar idH1
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
                
                // Guarda H0
                const resultH0 = await this.saveDriver(variableId, nameHypothesis, h0Text, 'H0', zoneId, state);
                if (!resultH0.success) {
                    return resultH0;
                }
                
                // Guarda H1
                const resultH1 = await this.saveDriver(variableId, nameHypothesis, h1Text, 'H1', zoneId, state);
                if (!resultH1.success) {
                    return resultH1;
                }
                
                // Recargar datos para obtener el estado actualizado
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