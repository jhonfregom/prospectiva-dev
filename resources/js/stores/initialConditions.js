import { defineStore } from 'pinia';
import axios from 'axios';
import { useTextsStore } from './texts';

export const useInitialConditionsStore = defineStore('initialConditions', {
    state: () => ({
        conditions: [], // [{ id, id_variable, name_variable, now_condition, state }]
        isLoading: false,
        error: null
    }),
    getters: {
        getConditionByIndex: (state) => (index) => {
            return state.conditions[index] || {};
        },
        isLocked: (state) => (index) => {
            const cond = state.conditions[index];
            return cond ? cond.state === '1' : false;
        }
    },
    actions: {
        async fetchConditions() {
            this.isLoading = true;
            try {
                const response = await axios.get('/initial-conditions');
                if (response.data.status === 200) {
                    this.conditions = response.data.data;
                }
            } catch (error) {
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        },
        async updateCondition(id, nowCondition) {
            try {
                const payload = { now_condition: nowCondition };
                const response = await axios.put(`/initial-conditions/${id}`, payload);
                
                if (response.data.status === 200) {
                    // Recargar datos para obtener el estado actualizado
                    await this.fetchConditions();
                    return { success: true };
                }
                return { success: false, message: 'Error al actualizar' };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                return { success: false, message: this.error };
            }
        },
        async closeAllConditions() {
            try {
                // Usar el endpoint masivo
                await axios.post('/initial-conditions/close-all');
                await this.fetchConditions();
                return { success: true };
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