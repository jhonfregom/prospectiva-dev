import { defineStore } from "pinia";
import { useSectionStore } from "./section";

// Define el store 'session' usando Pinia
export const useSessionStore = defineStore('session',{
    // Estado inicial del store
    state: () => ({
        // Objeto que controla qué componente está activo en la aplicación
        contentActive: {
            main: true,        // Vista principal/dashboard está activa por defecto
            variables: false,  // Vista de variables está inactiva por defecto
            matrix: false,     // Vista de matriz está inactiva por defecto
            graphics: false,   // Vista de gráfica está inactiva por defecto
            analysis: false,    // Vista de análisis mapa de variables
            hypothesis: false, // Vista de hipótesis está inactiva por defecto
            schwartz: false,  // Vista de ejes de Peter Schwartz está inactiva por defecto
            initialconditions: false, // Vista de condiciones iniciales está inactiva por defecto
            scenarios: false,    // Vista de escenarios está inactiva por defecto
        },
        
        // Otros estados del store...
        company: {},           // Información de la empresa actual
        participant: {},       // Información del participante actual
    }),
    getters: {
        // Retorna el contenido activo actual
        activeContent: (state) => {
            return Object.keys(state.contentActive).find(key => state.contentActive[key]) || 'main';
        },
    },
    actions: {
        // Activa un componente específico y desactiva los demás
        setActiveContent(section) {
            // Desactiva todos los contenidos
            for (const key in this.contentActive) {
                this.contentActive[key] = false;
            }
            // Activa el contenido especificado
            if (this.contentActive.hasOwnProperty(section)) {
                this.contentActive[section] = true;
            }
        },
        // Vuelve a la vista principal
        toBack(){
            this.setActiveContent('main');
            //Handle title section
            const storeSection = useSectionStore();
            storeSection.setTitleSection(null);
            storeSection.clearDynamicButtons();
        }
    }
});
