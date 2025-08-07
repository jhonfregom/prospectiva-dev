import { defineStore } from "pinia";
import { useSectionStore } from "./section";

export const useSessionStore = defineStore('session',{
    
    state: () => ({
        
        contentActive: {
            main: true,        
            variables: false,  
            matrix: false,     
            graphics: false,   
            analysis: false,    
            hypothesis: false, 
            schwartz: false,  
            initialconditions: false, 
            scenarios: false,    
            conclusions: false,  
            results: false,      
        },

        company: {},           
        participant: {},       
    }),
    getters: {
        
        activeContent: (state) => {
            return Object.keys(state.contentActive).find(key => state.contentActive[key]) || 'main';
        },
    },
    actions: {
        
        setActiveContent(section) {
            
            for (const key in this.contentActive) {
                this.contentActive[key] = false;
            }
            
            if (this.contentActive.hasOwnProperty(section)) {
                this.contentActive[section] = true;
            }
        },
        
        toBack(){
            this.setActiveContent('main');
            
            const storeSection = useSectionStore();
            storeSection.setTitleSection(null);
            storeSection.clearDynamicButtons();
        }
    }
});