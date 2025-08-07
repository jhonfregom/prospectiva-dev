import { defineStore } from "pinia";
import { markRaw } from "vue";

export const useSectionStore = defineStore('section',{
    state: () => ({
        titleSection: null,
        showBackButton: true,
        dynamicButtons: [],
        customComponents: [],
    }),
    getters: {
        
        getTitleSection: (state) => {
            if( state.titleSection === null || state.titleSection === undefined || state.titleSection === '')
            {
                return null;
            }

            return state.titleSection
        }
    },
    actions: {
        
        setTitleSection(title){
            this.titleSection = title;
        },
        
        setEnableBackButton(enable){
            this.showBackButton = enable;
        },
        
        setDynamicButtons(buttons){
            let valid = buttons.every( (button) => button.label && typeof button.action === 'function');
            if( valid )
            {
                this.dynamicButtons = buttons;
            }else{
                console.warn('buttons is not valid. Each item must have:',
                    'label (non-empty string), action (function)');
            }
        },
        setDynamicButtons(buttons){
            let valid = buttons.every( (button) => button.label && typeof button.action === 'function');
            if( valid )
            {
                this.dynamicButtons = buttons;
            }else{
                console.warn('buttons is not valid, require label and action');
            }
        },
        
        addDynamicButton(label, action){
            this.dynamicButtons.push({
                label: label,
                action: action
            });
        },
        
        clearDynamicButtons(){
            this.dynamicButtons = [];
        },
        
        setCustomComponents(components){
            let valid = components.every( (component) =>
                ( typeof component.value === 'object' || typeof component.value === 'string' )
                && (!component.actions || typeof component.actions === 'object'
                    && Object.values(component.actions).every( (action) => typeof action === 'function' )
                    )
            );

            if( valid )
            {
                this.customComponents = components.map( (component) => {
                    if( typeof component.value === 'object' )
                    {
                        
                        return {
                            value: markRaw(component.value),
                            actions: component.actions,
                        }
                    }
                    return component;
                });
            }else{
                console.warn('components is not valid. Each item must have:',
                    'value (object or string), actions? (object with functions)');
            }
        },
        
        clearCustomComponents(){
            this.customComponents = [];
        }
    }
});