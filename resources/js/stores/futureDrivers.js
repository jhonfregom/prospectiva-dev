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
            console.log('=== STORE FETCH DRIVERS START ===');
            this.isLoading = true;
            try {
                console.log('Store fetchDrivers - Starting fetch...');
                const response = await axios.get('/hypothesis');
                console.log('Store fetchDrivers - Response received');
                console.log('Store fetchDrivers - Response data:', response.data);
                
                if (response.data.status === 200) {
                    console.log('Store fetchDrivers - Status is 200, setting drivers...');
                    this.drivers = response.data.data;
                    console.log('Store fetchDrivers - Drivers set successfully');
                } else {
                    console.error('Store fetchDrivers - Unexpected status:', response.data.status);
                }
            } catch (error) {
                console.error('Store fetchDrivers - ERROR:', error);
                this.error = error.message;
            } finally {
                this.isLoading = false;
                console.log('=== STORE FETCH DRIVERS END ===');
            }
        },
        async saveDriver(index, payload) {
            try {
                console.log('Store saveDriver - Index:', index);
                console.log('Store saveDriver - Payload:', payload);
                console.log('Store saveDriver - Variable ID:', payload.variable_id);
                
                const response = await axios.post('/hypothesis', payload);
                console.log('Store saveDriver - Response:', response.data);
                
                if (response.data.status === 201) {
                    let data = response.data.data;
                    // Normalizar el campo variable_id
                    if (data.id_variable && !data.variable_id) {
                        data.variable_id = data.id_variable;
                    }
                    this.drivers[index] = data;
                    return { success: true, message: response.data.message };
                }
                return { success: false, message: response.data.message };
            } catch (error) {
                console.error('Store saveDriver - Error:', error.response?.data || error.message);
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            }
        },
        clearError() {
            this.error = null;
        }
    }
}); 