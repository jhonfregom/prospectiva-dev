import { defineStore } from 'pinia';
import axios from 'axios';

export const useVariablesStore = defineStore('variables', {
    state: () => ({
        variables: [],
        isLoading: false,
        error: null
    }),

    getters: {
        getColumns: () => [
            {
                field: 'id',
                label: 'VARIABLE',
                width: '100',
                sortable: true,
                customKey: 'id_variable',
                class: 'has-text-centered'
            },
            {
                field: 'name_variable',
                label: 'NOMBRE',
                width: '150',
                class: 'has-text-left'
            },
            {
                field: 'description',
                label: 'DESCRIPCIÓN',
                width: '400',
                class: 'description-column has-text-left'
            },
            {
                field: 'score',
                label: 'SCORE',
                width: '100',
                numeric: true,
                class: 'has-text-centered'
            },
            {
                field: 'score',
                label: 'ESTADO',
                width: '150',
                class: 'has-text-centered'
            },
            {
                field: 'actions',
                label: 'ACCIONES',
                width: '200',
                centered: true,
                class: 'has-text-centered'
            }
        ],

        getVariableStatus: (state) => (score) => {
            if (score <= 25) {
                return 'DEBES MEJORAR';
            } else if (score <= 50) {
                return 'FALTA ALGO MAS';
            } else if (score <= 100) {
                return 'UN ESFUERZO MAS';
            } else {
                return 'LO LOGRASTE';
            }
        },

        getStateText: () => (state) => {
            const states = {
                0: 'DEBES MEJORAR',
                1: 'FALTA ALGO MAS',
                2: 'UN ESFUERZO MAS',
                3: 'LO LOGRASTE'
            };
            return states[state] || 'DEBES MEJORAR';
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
                    score: variable.score
                });

                if (response.data.status === 200) {
                    const index = this.variables.findIndex(v => v.id === variable.id);
                    if (index !== -1) {
                        const updatedVariable = {
                            ...this.variables[index],
                            description: variable.description,
                            score: variable.score
                        };
                        
                        this.variables = [
                            ...this.variables.slice(0, index),
                            updatedVariable,
                            ...this.variables.slice(index + 1)
                        ];
                    }
                    return true;
                }
                return false;
            } catch (error) {
                console.error('Error updating variable:', error);
                const index = this.variables.findIndex(v => v.id === variable.id);
                if (index !== -1) {
                    const updatedVariable = {
                        ...this.variables[index],
                        description: variable.description,
                        score: variable.score
                    };
                    
                    this.variables = [
                        ...this.variables.slice(0, index),
                        updatedVariable,
                        ...this.variables.slice(index + 1)
                    ];
                }
                return true;
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
