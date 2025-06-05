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
        /**
         * Get title section
         * @returns {String} title, null if not set
         */
        getTitleSection: (state) => {
            if( state.titleSection === null || state.titleSection === undefined || state.titleSection === '')
            {
                return null;
            }

            return state.titleSection
        }
    },
    actions: {
        /**
         * Set title section
         * @param {String} title
         */
        setTitleSection(title){
            this.titleSection = title;
        },
        /**
         * Enable or disable back button
         * @param {Boolean} enable
        */
        setEnableBackButton(enable){
            this.showBackButton = enable;
        },
        /**
        * Sets dynamic buttons in the store.
        *
        * Each button must include:
        * - `label`: Text to display on the button (string)
        * - `action`: Function to execute when the button is clicked (function)
        *
        * Example of valid format:
        * [
        *   {
        *     label: 'Click me',
        *     action: () => { console.log('Button clicked') }
        *   },
        *   {
        *     label: 'Do something else',
        *     action: (event) => { alert('Another action') }
        *   }
        * ]
        *
        * @param {Array} buttons - Array of objects containing buttons with label and action
        */
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
        /**
        * Add dynamic button
        * @param {Array} buttons, require label and action (function)
        */
        addDynamicButton(label, action){
            this.dynamicButtons.push({
                label: label,
                action: action
            });
        },
        /**
        * Clear dynamic buttons
        */
        clearDynamicButtons(){
            this.dynamicButtons = [];
        },
        /**
        * Sets dynamic custom components in the store.
        *
        * Each component can be:
        * - A Vue component (object)
        * - An HTML string
        *
        * Optionally, it can include an `actions` object with functions that will be executed
        * when the child component emits certain events.
        *
        * Example of valid format:
        * [
        *   {
        *     value: MyComponent,
        *     actions: {
        *       'event-1': () => { ... },
        *       'event-2': (payload) => { ... }
        *     }
        *   },
        *   {
        *     value: '<div>HTML content</div>'
        *   }
        * ]
        *
        * @param {Array} components - Array of objects containing components or HTML to inject
        */
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
                        //Mark component as raw, to avoid vue reactivity
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
        /**
        * Clear custom components
        */
        clearCustomComponents(){
            this.customComponents = [];
        }
    }
});
