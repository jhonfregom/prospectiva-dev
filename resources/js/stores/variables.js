import { defineStore } from 'pinia';
import axios from 'axios';
import { useTextsStore } from './texts';

export const useVariablesStore = defineStore('variables', {
    state: () => ({
        variables: [],
        isLoading: false,
        error: null
    }),

    getters: {
        getColumns: () => {
            const textsStore = useTextsStore();
            return [
                {
                    field: 'id',
                    label: textsStore.getText('variables.table.variable'),
                    width: '100',
                    sortable: true,
                    customKey: 'id_variable',
                    class: 'has-text-centered'
                },
                {
                    field: 'name_variable',
                    label: textsStore.getText('variables.table.name'),
                    width: '150',
                    class: 'has-text-left'
                },
                {
                    field: 'description',
                    label: textsStore.getText('variables.table.description'),
                    width: '400',
                    class: 'description-column has-text-left'
                },
                {
                    field: 'score',
                    label: textsStore.getText('variables.table.score'),
                    width: '100',
                    numeric: true,
                    class: 'has-text-centered'
                },
                {
                    field: 'score',
                    label: textsStore.getText('variables.table.state'),
                    width: '150',
                    class: 'has-text-centered'
                },
                {
                    field: 'actions',
                    label: textsStore.getText('variables.table.actions'),
                    width: '200',
                    centered: true,
                    class: 'has-text-centered'
                }
            ];
        },

        getVariableStatus: (state) => (score) => {
            const textsStore = useTextsStore();
            if (score <= 25) {
                return textsStore.getText('analysis.diagnosis.0.text');
            } else if (score <= 50) {
                return textsStore.getText('analysis.diagnosis.1.text');
            } else if (score <= 100) {
                return textsStore.getText('analysis.diagnosis.2.text');
            } else {
                return textsStore.getText('analysis.diagnosis.3.text');
            }
        },

        getStateText: () => (state) => {
            const textsStore = useTextsStore();
            const states = {
                0: textsStore.getText('analysis.diagnosis.0.text'),
                1: textsStore.getText('analysis.diagnosis.1.text'),
                2: textsStore.getText('analysis.diagnosis.2.text'),
                3: textsStore.getText('analysis.diagnosis.3.text')
            };
            return states[state] || textsStore.getText('analysis.diagnosis.0.text');
        }
    },

    actions: {
        async fetchVariables() {
            this.isLoading = true;
            try {
                const response = await axios.get('/variables');
                if (response.data.status === 200) {
                    this.variables = [...response.data.data];
                }
            } catch (error) {
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        },

        async createVariable(variable) {
            try {
                const response = await axios.post('/variables', variable);
                if (response.data.status === 201) {
                    this.variables = [response.data.data, ...this.variables];
                    return true;
                }
                return false;
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                throw error;
            }
        },

        async updateVariable(variable) {
            try {
                const response = await axios.put(`/variables/${variable.id}`, {
                    description: variable.description,
                    score: variable.score,
                    edits_variable: variable.edits_variable,
                    state: variable.state // <-- AÑADIDO
                });

                if (response.data.status === 200) {
                    const index = this.variables.findIndex(v => v.id === variable.id);
                    if (index !== -1) {
                        // Forzar reactividad reemplazando todo el array
                        this.variables = [
                            ...this.variables.slice(0, index),
                            response.data.data,
                            ...this.variables.slice(index + 1)
                        ];
                    }
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Error updating variable:', error);
                throw error;
            }
        },

        async deleteVariable(id) {
            try {
                const response = await axios.delete(`/variables/${id}`);
                if (response.data.status === 200) {
                    this.variables = this.variables.filter(variable => variable.id !== id);
                    return true;
                }
                return false;
            } catch (error) {
                this.error = error.message;
                return false;
            }
        },

        async updateVariableState(id, state) {
            try {
                const response = await axios.put(`/variables/${id}/state`, { state });
                if (response.data.status === 200) {
                    const index = this.variables.findIndex(v => v.id === id);
                    if (index !== -1) {
                        this.variables[index].state = state;
                    }
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Error updating variable state:', error);
                return false;
            }
        }
    }
});
