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
        setActiveContent(content) {
            console.log('Activando contenido:', content);
            // Desactiva todos los contenidos
            Object.keys(this.contentActive).forEach(key => {
                this.contentActive[key] = false;
            });
            // Activa el contenido especificado
            this.contentActive[content] = true;
            console.log('Estado final:', this.contentActive);
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
