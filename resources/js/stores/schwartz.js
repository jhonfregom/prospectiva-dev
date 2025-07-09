import { defineStore } from 'pinia';
import axios from 'axios';

export const useSchwartzStore = defineStore('schwartz', {
    state: () => ({
        escenarios: [
            { id: null, titulo: 'ESCENARIO 1', texto: '', edits: 0, state: 0 },
            { id: null, titulo: 'ESCENARIO 2', texto: '', edits: 0, state: 0 },
            { id: null, titulo: 'ESCENARIO 3', texto: '', edits: 0, state: 0 },
            { id: null, titulo: 'ESCENARIO 4', texto: '', edits: 0, state: 0 },
        ]
    }),
    actions: {
        setEscenario(index, texto) {
            if (this.escenarios[index]) {
                this.escenarios[index].texto = texto;
            }
        },
        setTitulo(index, titulo) {
            if (this.escenarios[index]) {
                this.escenarios[index].titulo = titulo;
            }
        },
        incrementEdit(index) {
            if (this.escenarios[index] && this.escenarios[index].edits < 3) {
                this.escenarios[index].edits++;
                if (this.escenarios[index].edits >= 3) {
                    this.escenarios[index].state = 1;
                }
            }
        },
        setState(index, value) {
            if (this.escenarios[index]) {
                this.escenarios[index].state = value;
            }
        },
        async saveScenario(index, numScenario) {
            const escenario = this.escenarios[index];
            const payload = {
                titulo: escenario.texto,
                edits: escenario.edits,
                state: escenario.state,
                num_scenario: numScenario // num_scenario explícito
            };
            try {
                // Usar solo POST, el backend decide si crea o actualiza
                const res = await axios.post('/scenarios', payload);
                if (res.data && res.data.data) {
                    this.escenarios[index].id = res.data.data.id;
                }
                return { success: true };
            } catch (error) {
                return { success: false, message: error.response?.data?.message || error.message };
            }
        },
        async fetchScenarios() {
            try {
                const res = await axios.get('/scenarios');
                if (res.data && res.data.data && Array.isArray(res.data.data)) {
                    // Limpiar los escenarios actuales
                    this.escenarios.forEach(e => {
                        e.id = null;
                        e.texto = '';
                        e.edits = 0;
                        e.state = 0;
                    });
                    // Poblar por num_scenario
                    res.data.data.forEach(item => {
                        const idx = (item.num_scenario || 1) - 1;
                        if (this.escenarios[idx]) {
                            this.escenarios[idx].id = item.id;
                            this.escenarios[idx].texto = item.titulo;
                            this.escenarios[idx].edits = item.edits;
                            this.escenarios[idx].state = item.state === undefined ? 0 : (item.state === '1' || item.state === 1 ? 1 : 0);
                        }
                    });
                }
                return { success: true };
            } catch (error) {
                return { success: false, message: error.response?.data?.message || error.message };
            }
        }
    }
}); 