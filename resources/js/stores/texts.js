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
            subtitle: 'Analiza y describe cada zona del mapa de variables',
            description: 'De 5 Variables propuestas que se encuentran enunciadas en la tabla Matriz Variables, se hizo un proceso de calificación del nivel de influencia que tenían entre ellas. Dicha calificación arrojó un gráfico que permite visualizar su ubicación en diferentes zonas de la Matriz de Análisis Estructural.',
            description_placeholder: 'Describe el análisis de esta zona...',
            score: 'Puntaje',
            score_help: 'Ingresa un puntaje entre 0 y 100',
            save: 'Guardar',
            locked: 'Bloqueado',
            editable: 'Editable',
            zones: [
                { key: 'poder', name: 'Zona de Poder' },
                { key: 'conflicto', name: 'Zona de Conflicto' },
                { key: 'indiferencia', name: 'Zona de Indiferencia' },
                { key: 'salida', name: 'Zona de Salida' }
            ],
            zones_detail: {
                poder: {
                    title: 'Zona de Poder',
                    description: 'Variables con alta influencia y baja dependencia'
                },
                conflicto: {
                    title: 'Zona de Conflicto',
                    description: 'Variables con alta influencia y alta dependencia'
                },
                indiferencia: {
                    title: 'Zona de Indiferencia',
                    description: 'Variables con baja influencia y baja dependencia'
                },
                salida: {
                    title: 'Zona de Salida',
                    description: 'Variables con baja influencia y alta dependencia'
                }
            },
            diagnosis: [
                { min: 0, max: 40, text: 'DEBES MEJORAR', color: 'red' },
                { min: 41, max: 80, text: 'FALTA ALGO MAS', color: 'orange' },
                { min: 81, max: 120, text: 'UN ESFUERZO MAS', color: 'blue' },
                { min: 121, max: 9999, text: 'LO LOGRASTE', color: 'green' }
            ]
        },
        common: {
            loading: 'Cargando...',
            save_success: 'Guardado correctamente',
            save_error: 'Error al guardar',
            delete_success: 'Eliminado correctamente',
            delete_error: 'Error al eliminar',
            update_success: 'Actualizado correctamente',
            update_error: 'Error al actualizar'
        },
        hypothesis: {
            title: 'Direccionadores de futuro',
            subtitle: 'En esta sección se generan las hipótesis para las dos variables más cercanas a la zona de poder.',
            table: {
                h: 'H',
                variable: 'VARIABLE',
                descriptionH0: 'HIPÓTESIS H0',
                descriptionH1: 'HIPÓTESIS H1',
                edit: 'Editar',
                save: 'Guardar',
                locked: 'BLOQUEADO',
            },
            note: 'Las hipótesis se bloquean después de dos ediciones manuales. Cada textarea tiene un límite de 40 palabras. Ambos campos (H0 y H1) se editan juntos.'
        },
        initialConditions: {
            title: 'Condiciones Iniciales',
            subtitle: 'Registra la condición actual de cada variable. Puedes editar cada campo hasta dos veces antes de que se bloquee.',
            table: {
                variable: 'VARIABLES',
                name: 'NOMBRES',
                nowCondition: 'CONDICION ACTUAL DE LA VARIABLE',
                actions: 'ACCIONES',
                edit: 'Editar',
                save: 'Guardar',
                locked: 'Bloqueado'
            }
        },
        scenarios: {
            title: 'Escenarios',
            table: {
                description: 'Descripción',
                hypothesis: 'Hipótesis más cercanas',
                year1: 'AÑO 1',
                year2: 'AÑO 2',
                year3: 'AÑO 3',
                actions: 'Acciones',
                edit: 'Editar',
                save: 'Guardar',
                locked: 'Bloqueado'
            }
        },
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
