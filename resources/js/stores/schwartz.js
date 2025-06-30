import { defineStore } from 'pinia';

export const useSchwartzStore = defineStore('schwartz', {
    state: () => ({
        escenarios: [
            { titulo: 'ESCENARIO 1', texto: '' },
            { titulo: 'ESCENARIO 2', texto: '' },
            { titulo: 'ESCENARIO 3', texto: '' },
            { titulo: 'ESCENARIO 4', texto: '' },
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
        }
    }
}); 