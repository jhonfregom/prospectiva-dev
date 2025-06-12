import { defineStore } from 'pinia';
import axios from 'axios';

/**
 * Store de Pinia para manejar el estado y lógica de las variables
 * 
 * Este store centraliza:
 * 1. Estado global de las variables
 * 2. Getters para computar estados y textos
 * 3. Acciones para interactuar con la API
 * 
 * El store implementa un patrón de manejo de estado que:
 * - Mantiene un estado reactivo de las variables
 * - Proporciona métodos para manipular variables
 * - Maneja la comunicación con el backend
 * - Gestiona estados de carga y errores
 */
export const useVariablesStore = defineStore('variables', {
    /**
     * Estado inicial del store
     * 
     * Contiene:
     * @property {Array} variables - Lista de variables del sistema
     * @property {boolean} isLoading - Indica si hay operaciones en curso
     * @property {string|null} error - Almacena mensajes de error si ocurren
     */
    state: () => ({
        variables: [],    // Lista de variables
        isLoading: false, // Estado de carga
        error: null       // Almacena errores si ocurren
    }),

    /**
     * Getters para computar valores derivados del estado
     * 
     * Estos getters proporcionan:
     * 1. Cálculo de estados basados en scores
     * 2. Traducción de estados numéricos a texto
     * 3. Formateo de datos para la UI
     */
    getters: {
        /**
         * Determina el estado de una variable basado en su score
         * 
         * Reglas de estado:
         * - ≤ 25: "DEBES MEJORAR"
         * - ≤ 50: "FALTA ALGO MAS"
         * - ≤ 100: "UN ESFUERZO MAS"
         * - > 100: "LO LOGRASTE"
         * 
         * @param {number} score - Puntuación de la variable
         * @returns {string} Estado calculado en texto
         */
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

        /**
         * Traduce el estado numérico a texto descriptivo
         * 
         * Mapeo de estados:
         * 0: "DEBES MEJORAR"
         * 1: "FALTA ALGO MAS"
         * 2: "UN ESFUERZO MAS"
         * 3: "LO LOGRASTE"
         * 
         * @param {number} state - Estado numérico (0-3)
         * @returns {string} Texto descriptivo del estado
         */
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

    /**
     * Acciones para modificar el estado y comunicarse con el backend
     * 
     * Estas acciones manejan:
     * 1. Comunicación con la API REST
     * 2. Actualización del estado local
     * 3. Manejo de errores
     * 4. Estados de carga
     */
    actions: {
        /**
         * Obtiene todas las variables del servidor
         * 
         * Este método:
         * 1. Activa el estado de carga
         * 2. Realiza la petición GET a /variables
         * 3. Actualiza el estado con la respuesta
         * 4. Maneja posibles errores
         * 5. Desactiva el estado de carga
         */
        async fetchVariables() {
            this.isLoading = true;
            try {
                const response = await axios.get('/variables');
                if (response.data.status === 200) {
                    // Actualizamos el estado de forma reactiva
                    this.variables = [...response.data.data];
                }
            } catch (error) {
                this.error = error.message;
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Crea una nueva variable
         * 
         * Este método:
         * 1. Envía los datos al servidor vía POST
         * 2. Actualiza el estado local si la creación es exitosa
         * 3. Maneja errores de la operación
         * 
         * @param {Object} variable - Datos de la nueva variable
         * @property {string} variable.name_variable - Nombre de la variable
         * @returns {boolean} true si la creación fue exitosa, false en caso contrario
         */
        async createVariable(variable) {
            try {
                const response = await axios.post('/variables', variable);
                if (response.data.status === 200) {
                    // Actualizamos el estado de forma reactiva
                    this.variables = [response.data.data, ...this.variables];
                    return true;
                }
                return false;
            } catch (error) {
                this.error = error.message;
                return false;
            }
        },

        /**
         * Actualiza una variable existente
         * 
         * Este método:
         * 1. Envía los datos actualizados al servidor vía PUT
         * 2. Actualiza el estado local si la modificación es exitosa
         * 3. Mantiene la reactividad del estado
         * 
         * @param {Object} variable - Variable con datos actualizados
         * @property {number} variable.id - ID de la variable a actualizar
         * @property {string} variable.description - Nueva descripción
         * @property {number} variable.score - Nueva puntuación
         * @returns {boolean} true si la actualización fue exitosa, false en caso contrario
         */
        async updateVariable(variable) {
            try {
                const response = await axios.put(`/variables/${variable.id}`, {
                    description: variable.description,
                    score: variable.score
                });

                if (response.data.status === 200) {
                    // Encontramos el índice de la variable en el array
                    const index = this.variables.findIndex(v => v.id === variable.id);
                    if (index !== -1) {
                        // Actualizamos el estado de forma reactiva
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
                this.error = error.message;
                return false;
            }
        },

        /**
         * Elimina una variable existente
         * 
         * Este método:
         * 1. Envía la solicitud de eliminación al servidor
         * 2. Actualiza el estado local si la eliminación es exitosa
         * 3. Mantiene la reactividad del estado
         * 
         * @param {number} id - ID de la variable a eliminar
         * @returns {boolean} true si la eliminación fue exitosa, false en caso contrario
         */
        async deleteVariable(id) {
            try {
                const response = await axios.delete(`/variables/${id}`);
                if (response.data.status === 200) {
                    // Filtramos la variable eliminada del array
                    this.variables = this.variables.filter(variable => variable.id !== id);
                    return true;
                }
                return false;
            } catch (error) {
                this.error = error.message;
                return false;
            }
        }
    }
});