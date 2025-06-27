import { defineStore } from "pinia";
export const useTextsStore = defineStore('texts', {
    state: () => ({
        /**
         * Texts are created setTexts(),
         * defined from ../../../resources/config/shared-variables/main.php
         * Or other resource to view
         * These will not be visible in the development console
         */
        locale: null, //Current language to translations
        graphics: {
            title: 'Gráfica Variables',
            zone_power: 'Zona de Poder',
            zone_indifference: 'Zona de Indiferencia',
            zone_conflict: 'Zona de Conflicto',
            zone_exit: 'Zona de Salida',
            x_axis: 'DEPENDENCIA',
            y_axis: 'INFLUENCIA'
        },
        analysis: {
            title: 'Análisis Mapa de Variables',
            description: 'De 5 Variables propuestas que se encuentran enunciadas en la tabla Matriz Variables, se hizo un proceso de calificación del nivel de influencia que tenían entre ellas. Dicha calificación arrojó un gráfico que permite visualizar su ubicación en diferentes zonas de la Matriz de Análisis Estructural.',
            zones: [
                { key: 'poder', name: 'Zona de Poder' },
                { key: 'conflicto', name: 'Zona de Conflicto' },
                { key: 'indiferencia', name: 'Zona de Indiferencia' },
                { key: 'salida', name: 'Zona de Salida' }
            ],
            diagnosis: [
                { min: 0, max: 40, text: 'DEBES MEJORAR', color: 'red' },
                { min: 41, max: 80, text: 'FALTA ALGO MAS', color: 'orange' },
                { min: 81, max: 120, text: 'UN ESFUERZO MAS', color: 'blue' },
                { min: 121, max: 9999, text: 'LO LOGRASTE', color: 'green' }
            ]
        }
    }),
    getters: {
        /**
         * User storeText.[name_text], Ex storeText.home, storeText.participants.list_users
         */
        //Get text in the first level
        getText: (state) => {
            return (name) => {
                //Use for get texts with structure object. Ex, texts.section.title
                const keys = name.split('.');
                let current = state;
                for(const key of keys)
                {
                    if(current === null || current === undefined || current[key] === undefined)
                    {
                        return;
                    }
                    current = current[key];
                }
                return current;
            }
        },
        getLocale: (state) => {
            return state.locale
        }
    },
    actions: {
        setTexts(objTexts){
            for(const key in objTexts)
            {
                this[key] = objTexts[key];
            }
        },
        setLocale(locale){
            this.locale = locale;
        }
    }
});
