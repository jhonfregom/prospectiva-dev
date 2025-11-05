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
        
        isAllBlocked: (state) => {
            return state.conclusions.state === '1';
        }
    },

    actions: {
        
        async fetchConclusions() {
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/conclusions');
                
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
        },

        async closeAllConclusions() {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await axios.post('/conclusions/close-all');
                if (response.data.status === 200) {
                    
                    this.conclusions.component_practice_edits = 3;
                    this.conclusions.actuality_edits = 3;
                    this.conclusions.aplication_edits = 3;
                    this.conclusions.state = '1';
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