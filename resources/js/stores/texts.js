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
        variables: {
            title: 'Variables',
            subtitle: 'Gestiona las variables del proyecto',
            table: {
                variable: 'VARIABLE',
                name: 'NOMBRE',
                description: 'DESCRIPCIÓN',
                score: 'SCORE',
                state: 'ESTADO',
                actions: 'ACCIONES',
                edit: 'Editar',
                save: 'Guardar',
                delete: 'Eliminar',
                new: 'Nuevo'
            },
            modal: {
                title: 'Nueva Variable',
                name_label: 'Nombre de la Variable',
                name_placeholder: 'Ingrese el nombre de la variable',
                save: 'Guardar',
                cancel: 'Cancelar'
            },
            messages: {
                create_success: 'Variable creada exitosamente',
                create_error: 'Error al crear la variable',
                update_error: 'Error al actualizar la variable',
                delete_success: 'Variable eliminada correctamente',
                delete_error: 'Error al eliminar la variable',
                delete_confirm_title: 'Eliminar Variable',
                delete_confirm_message: '¿Está seguro de eliminar esta variable?',
                delete_confirm_yes: 'Eliminar',
                delete_confirm_no: 'Cancelar',
                limit_reached: 'Se ha alcanzado el límite máximo de 15 variables permitidas'
            },
            description_placeholder: 'Escriba la descripción de la variable'
        },
        schwartz: {
            title: 'Ejes de Peter Schwartz',
            subtitle: 'Define los escenarios según los ejes de Peter Schwartz',
            hypothesis: {
                h1_plus: 'H1+',
                h1_minus: 'H1-',
                h2_plus: 'H2+',
                h2_minus: 'H2-'
            },
            scenarios: {
                scenario_1: 'ESCENARIO 1',
                scenario_2: 'ESCENARIO 2',
                scenario_3: 'ESCENARIO 3',
                scenario_4: 'ESCENARIO 4'
            },
            actions: {
                edit: 'Editar',
                save: 'Guardar'
            },
            messages: {
                save_error: 'Error al guardar: ',
                try_again: 'Intenta de nuevo.',
                edit_limit_reached: 'Has alcanzado el límite de ediciones para este escenario.'
            }
        },
        strategic: {
            main_title: 'Escenarios Estratégicos',
            scenario_label: 'Escenario:',
            plan_label: 'PLAN',
            name: 'NOMBRE',
            hypothesis1: 'Hipótesis 1',
            hypothesis2: 'Hipótesis 2',
            year1: 'AÑO 1',
            year2: 'AÑO 2',
            year3: 'AÑO 3',
            edit: 'Editar',
            save: 'Guardar',
            edit_limit: 'Has alcanzado el límite de ediciones para este año.',
            save_error: 'Error al guardar el escenario.'
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
                actions: 'ACCIONES'
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
