import { defineStore } from 'pinia';
import axios from 'axios';

export const useConclusionsStore = defineStore('conclusions', {
    state: () => ({
        conclusions: {
            id: null,
            component_practice: '',
            actuality: '',
            aplication: '',
            state: '0',
            user_id: null
        },
        isLoading: false,
        error: null
    }),

    getters: {
        /**
         * Verificar si todas las conclusiones están bloqueadas
         */
        isAllBlocked: (state) => {
            return state.conclusions.state === '1';
        }
    },

    actions: {
        /**
         * Cargar las conclusiones del usuario
         */
        async fetchConclusions() {
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/conclusions');
                console.log('Fetch conclusions response:', response.data);
                
                if (response.data.status === 200) {
                    this.conclusions = { ...response.data.data };
                    console.log('Conclusions state:', this.conclusions.state);
                }
                
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Crear o actualizar las conclusiones
         */
        async updateConclusions(data) {
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await axios.post('/conclusions', data);
                
                if (response.data.status === 200) {
                    this.conclusions = { ...response.data.data };
                }
                
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Actualizar un campo específico de las conclusiones
         */
        async updateField(fieldName, value) {
            this.isLoading = true;
            this.error = null;
            
            try {
                const updateData = {
                    ...this.conclusions,
                    [fieldName]: value
                };

                const response = await axios.put(`/conclusions/${this.conclusions.id}`, updateData);
                
                if (response.data.status === 200) {
                    this.conclusions = { ...response.data.data };
                }
                
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Actualizar el estado de las conclusiones
         */
        async updateState(state) {
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/conclusions/${this.conclusions.id}/state`, { state });
                
                if (response.data.status === 200) {
                    this.conclusions.state = state;
                }
                
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Bloquear las conclusiones
         */
        async blockConclusions() {
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/conclusions/${this.conclusions.id}/block`);
                
                if (response.data.status === 200) {
                    this.conclusions.state = '1';
                }
                
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Desbloquear las conclusiones
         */
        async unblockConclusions() {
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await axios.put(`/conclusions/${this.conclusions.id}/unblock`);
                
                if (response.data.status === 200) {
                    this.conclusions.state = '0';
                }
                
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                throw error;
            } finally {
                this.isLoading = false;
            }
        }
    }
}); 