
import { defineStore } from "pinia";
import { useSectionStore } from "./section";

export const useSessionStore = defineStore('session',{
    state: () => ({

        company: new Object(),
        participant: new Object(),
        contentActive: {
            main: true,
            //parameters: false,
            //roles: false,
            variables: false,
        },
    }),
    getters: {
        activeContent: (state) => {
            return Object.keys(state.contentActive).find(
                key => state.contentActive[key]
            )
        },
    },
    actions: {
        setActiveContent(item){
            for (const key in this.contentActive)
            {
                this.contentActive[key] = key === item;
            }
        },
        toBack(){
            //Set active content
            this.setActiveContent('main');
            //Handle title section
            const storeSection = useSectionStore();
            storeSection.setTitleSection(null);
            storeSection.clearDynamicButtons();
        }
    }
});
