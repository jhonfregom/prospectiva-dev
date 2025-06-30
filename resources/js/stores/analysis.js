import { defineStore } from 'pinia';
import { useTextsStore } from './texts';
import axios from 'axios';

export const useAnalysisStore = defineStore('analysis', {
    state: () => ({
        zones: [], // Se llenará desde textsStore
        rows: [
            // Filas vacías que se llenarán dinámicamente
            { key: 'poder', variables: [], comment: '', score: 0, state: '0' },
            { key: 'conflicto', variables: [], comment: '', score: 0, state: '0' },
            { key: 'salida', variables: [], comment: '', score: 0, state: '0' },
            { key: 'indiferencia', variables: [], comment: '', score: 0, state: '0' }
        ],
        isLoading: false,
        error: null
    }),

    getters: {
        getAnalysesByZone: (state) => (zoneId) => {
            return state.analyses.find(analysis => analysis.zone_id === zoneId);
        },

        getAnalysisState: (state) => (zoneId) => {
            const analysis = state.analyses.find(analysis => analysis.zone_id === zoneId);
            return analysis ? analysis.state : '0';
        },

        isAnalysisLocked: (state) => (zoneId) => {
            const analysis = state.analyses.find(analysis => analysis.zone_id === zoneId);
            return analysis ? analysis.state === '1' : false;
        },

        getAnalysisScore: (state) => (zoneId) => {
            const analysis = state.analyses.find(analysis => analysis.zone_id === zoneId);
            return analysis ? analysis.score : 0;
        },

        getAnalysisDescription: (state) => (zoneId) => {
            const analysis = state.analyses.find(analysis => analysis.zone_id === zoneId);
            return analysis ? analysis.description : '';
        }
    },

    actions: {
        initZones() {
            const textsStore = useTextsStore();
            this.zones = textsStore.analysis.zones;
        },

        setComment(index, value) {
            this.rows[index].comment = value;
        },

        setScore(index, value) {
            this.rows[index].score = value;
        },

        setVariables(index, variables) {
            this.rows[index].variables = variables;
        },

        setState(index, value) {
            this.rows[index].state = value;
        },

        async fetchAnalyses() {
            this.isLoading = true;
            try {
                const response = await axios.get('/analysis');
                if (response.data.status === 200) {
                    return response.data;
                }
            } catch (error) {
                this.error = error.message;
                console.error('Error al obtener análisis:', error);
            } finally {
                this.isLoading = false;
            }
        },

        async saveAnalysis(analysisData) {
            try {
                const response = await axios.post('/analysis', analysisData);
                console.log('Analysis save response:', response.data);
                
                if (response.data.status === 201) {
                    // Actualizar el state en el store si la respuesta incluye el análisis actualizado
                    if (response.data.data) {
                        const zoneKey = analysisData.zone_id;
                        const rowIndex = this.rows.findIndex(row => row.key === zoneKey);
                        if (rowIndex !== -1) {
                            this.rows[rowIndex].state = response.data.data.state;
                            console.log('Analysis updated, new state:', this.rows[rowIndex].state);
                        }
                    }
                    return { success: true, data: response.data.data, message: response.data.message };
                }
                return { success: false, message: response.data.message };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                console.error('Error al guardar análisis:', error);
                return { success: false, message: this.error };
            }
        },

        async updateAnalysis(id, analysisData) {
            try {
                const response = await axios.put(`/analysis/${id}`, analysisData);
                if (response.data.status === 200) {
                    return { success: true, data: response.data.data, message: response.data.message };
                }
                return { success: false, message: response.data.message };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                console.error('Error al actualizar análisis:', error);
                return { success: false, message: this.error };
            }
        },

        async deleteAnalysis(id) {
            try {
                const response = await axios.delete(`/analysis/${id}`);
                if (response.data.status === 200) {
                    return { success: true, message: response.data.message };
                }
                return { success: false, message: response.data.message };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                console.error('Error al eliminar análisis:', error);
                return { success: false, message: this.error };
            }
        },

        async resetAutoIncrement() {
            try {
                const response = await axios.post('/analysis/reset-auto-increment');
                return { success: response.data.status === 200, message: response.data.message };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                console.error('Error al reiniciar AUTO_INCREMENT:', error);
                return { success: false, message: this.error };
            }
        },

        async deleteAllAndReset() {
            try {
                const response = await axios.post('/analysis/delete-all-reset');
                if (response.data.status === 200) {
                    this.rows = [
                        { key: 'poder', variables: [], comment: '', score: 0, state: '0' },
                        { key: 'conflicto', variables: [], comment: '', score: 0, state: '0' },
                        { key: 'salida', variables: [], comment: '', score: 0, state: '0' },
                        { key: 'indiferencia', variables: [], comment: '', score: 0, state: '0' }
                    ];
                }
                return { success: response.data.status === 200, message: response.data.message };
            } catch (error) {
                this.error = error.response?.data?.message || error.message;
                console.error('Error al borrar todos los registros:', error);
                return { success: false, message: this.error };
            }
        },

        clearError() {
            this.error = null;
        }
    }
}); 