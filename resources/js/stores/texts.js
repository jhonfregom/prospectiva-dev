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
        isLoading: false, //Loading state for texts
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
            subtitle: 'Analiza y describe cada zona del mapa de variables para comprender la influencia y dependencia de las variables estratégicas',
            description: 'En esta sección se analiza la ubicación de las variables en el mapa de influencia y dependencia. Las variables se posicionan en diferentes zonas según su nivel de influencia sobre otras variables y su dependencia de ellas. Este análisis permite identificar variables clave, conflictivas, indiferentes o de salida, facilitando la toma de decisiones estratégicas y la comprensión de las dinámicas del sistema.',
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
                    description: 'Variables con alta influencia y baja dependencia. Estas variables son clave para el sistema ya que pueden influir significativamente en otras variables sin ser afectadas por ellas. Son fundamentales para la toma de decisiones estratégicas.'
                },
                conflicto: {
                    title: 'Zona de Conflicto',
                    description: 'Variables con alta influencia y alta dependencia. Estas variables son inestables y requieren atención especial. Pueden generar conflictos en el sistema debido a su alta sensibilidad a cambios y su capacidad de influir en otras variables.'
                },
                indiferencia: {
                    title: 'Zona de Indiferencia',
                    description: 'Variables con baja influencia y baja dependencia. Estas variables son relativamente independientes del sistema y tienen poco impacto en otras variables. Pueden ser consideradas como elementos estables pero de baja prioridad estratégica.'
                },
                salida: {
                    title: 'Zona de Salida',
                    description: 'Variables con baja influencia y alta dependencia. Estas variables son altamente dependientes de otras variables pero tienen poca capacidad de influencia. Son sensibles a cambios externos y pueden ser indicadores del estado del sistema.'
                }
            },
            diagnosis: [
                { min: 0, max: 40, text: 'DEBES MEJORAR', color: 'red' },
                { min: 41, max: 80, text: 'FALTA ALGO MÁS', color: 'orange' },
                { min: 81, max: 120, text: 'UN ESFUERZO MÁS', color: 'blue' },
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
                nowCondition: 'CONDICIÓN ACTUAL DE LA VARIABLE',
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

        results_section: {
            title: 'Resultados',
            table: {
                id: 'ID',
                first_name: 'Nombre',
                last_name: 'Apellido',
                document_id: 'Identificación',
                email: 'Correo',
                initial_conditions: 'Condiciones Iniciales',
                scenarios: 'Escenarios',
                conclusions: 'Conclusiones'
            }
        },
        
        // Variables Section - Textos adicionales
        variables_section: {
            close_button: 'Cerrar',
            return_button: 'Regresar',
            close_confirm_title: 'Confirmar Cierre',
            close_confirm_message: '¿Estás seguro de cerrar el módulo? No podrás editar más.',
            return_confirm_title: 'Confirmar Regreso',
            return_confirm_message: '¿Está seguro que desea regresar? Solo podrá hacer esto una vez.',
            confirm_yes: 'Sí, cerrar',
            confirm_yes_return: 'Sí, regresar',
            confirm_no: 'Cancelar',
            cancel: 'Cancelar'
        },
        
        // Matriz Section - Textos para botones y confirmaciones
        matriz_section: {
            close_button: 'Cerrar',
            return_button: 'Regresar',
            close_confirm_message: '¿Está seguro de que desea cerrar el módulo de matriz?',
            return_confirm_message: '¿Está seguro que desea regresar? Solo podrá hacer esto una vez.',
            confirm_yes: 'Sí, cerrar',
            confirm_yes_return: 'Sí, regresar',
            confirm_no: 'Cancelar'
        },
        
        // Analysis Section - Textos para botones y confirmaciones
        analysis_section: {
            close_button: 'Cerrar',
            return_button: 'Regresar',
            close_confirm_message: '¿Estás seguro de cerrar el módulo? No podrás editar más.',
            return_confirm_message: '¿Estás seguro de regresar el módulo? Podrás editar y guardar nuevamente.',
            confirm_yes: 'Sí, cerrar',
            confirm_yes_return: 'Sí, regresar',
            confirm_no: 'Cancelar'
        },
        
        // Hypothesis Section - Textos para botones y confirmaciones
        hypothesis_section: {
            close_button: 'Cerrar',
            return_button: 'Regresar',
            close_confirm_message: '¿Estás seguro de cerrar el módulo? No podrás editar más.',
            return_confirm_message: '¿Está seguro que desea regresar? Solo podrá hacer esto una vez.',
            confirm_yes: 'Sí, cerrar',
            confirm_yes_return: 'Sí, regresar',
            confirm_no: 'Cancelar'
        },
        
        // Schwartz Section - Textos para botones y confirmaciones
        schwartz_section: {
            close_button: 'Cerrar',
            return_button: 'Regresar',
            close_confirm_message: '¿Estás seguro de cerrar el módulo? No podrás editar más.',
            return_confirm_message: '¿Está seguro que desea regresar? Solo podrá hacer esto una vez.',
            confirm_yes: 'Sí, cerrar',
            confirm_yes_return: 'Sí, regresar',
            confirm_no: 'Cancelar'
        },
        
        // Initial Conditions Section - Textos para botones y confirmaciones
        initial_conditions_section: {
            close_button: 'Cerrar',
            return_button: 'Regresar',
            close_confirm_message: '¿Estás seguro de cerrar el módulo? No podrás editar más.',
            return_confirm_message: '¿Está seguro que desea regresar? Solo podrá hacer esto una vez.',
            confirm_yes: 'Sí, cerrar',
            confirm_yes_return: 'Sí, regresar',
            confirm_no: 'Cancelar'
        },
        
        // Scenarios Section - Textos para botones y confirmaciones
        scenarios_section: {
            close_button: 'Cerrar',
            return_button: 'Regresar',
            close_confirm_message: '¿Estás seguro de cerrar el módulo? No podrás editar más.',
            return_confirm_message: '¿Está seguro que desea regresar? Solo podrá hacer esto una vez.',
            confirm_yes: 'Sí, cerrar',
            confirm_yes_return: 'Sí, regresar',
            confirm_no: 'Cancelar'
        },
        
        // Conclusions Section - Textos para botones y confirmaciones
        conclusions_section: {
            title: 'Conclusiones de aprendizaje',
            component_practice_subtitle: 'DESDE EL COMPONENTE PRÁCTICO (Análisis del proceso, practicidad, comprensible, se adapta al proceso de aprendizaje y al objetivo del curso)',
            actuality_subtitle: 'Actualidad (Consideraciones del proceso para que sea implementado en las organizaciones, ¿deben las empresas hacer ejercicios de este tipo?)',
            aplication_subtitle: 'APLICACIÓN (Qué tanto se adapta a la organización para la que trabajas, o para tu emprendimiento, o para tu vida personal y profesional)',
            table: {
                edit: 'Editar',
                save: 'Guardar',
                locked: 'Bloqueado'
            },
            component_practice_placeholder: 'Describe el componente práctico de tu aprendizaje...',
            actuality_placeholder: 'Reflexiona sobre la actualidad y relevancia de lo aprendido...',
            aplication_placeholder: 'Explica cómo aplicarás lo aprendido en el futuro...',
            close_button: 'Cerrar',
            return_button: 'Regresar',
            close_confirm_message: '¿Estás seguro de cerrar el módulo? No podrás editar más.',
            return_confirm_message: '¿Está seguro que desea regresar? Solo podrá hacer esto una vez.',
            confirm_yes: 'Sí, cerrar',
            confirm_yes_return: 'Sí, regresar',
            confirm_no: 'Cancelar',
            messages: {
                load_error: 'Error al cargar las conclusiones',
                save_success: 'Conclusiones guardadas correctamente',
                save_error: 'Error al guardar las conclusiones',
                update_success: 'Conclusiones actualizadas correctamente',
                update_error: 'Error al actualizar las conclusiones',
                close_success: 'Conclusiones cerradas correctamente',
                close_error: 'Error al cerrar las conclusiones'
            }
        },

        // Results Section - Textos para botones y confirmaciones
        results_section: {
            close_button: 'Cerrar',
            return_button: 'Regresar',
            close_confirm_message: '¿Estás seguro de cerrar el módulo? No podrás editar más.',
            return_confirm_message: '¿Está seguro que desea regresar? Solo podrá hacer esto una vez.',
            confirm_yes: 'Sí, cerrar',
            confirm_yes_return: 'Sí, regresar',
            confirm_no: 'Cancelar'
        },
        
        // Floating Bubble - Textos del menú flotante
        floating_bubble: {
            notes: 'Notas',
            ai_assistant: 'ProspecIA',
            information: 'Información',
            new_note: 'Nueva',
            save: 'Guardar',
            note_without_title: 'Nota sin título',
            no_content: 'Sin contenido',
            note_placeholder: 'Escribe tu nota aquí...',
            ai_placeholder: 'Escribe tu texto para que lo analice o corrija... (Shift+Enter para nueva línea, Enter para enviar)',
            tools_title: 'Herramientas de prospectiva: Notas, ProspecIA e Información',
            notes_tooltip: 'Registrar observaciones y reflexiones sobre el entorno que influye en la organización',
            ai_tooltip: 'ProspecIA para análisis, corrección y mejora de textos',
            info_tooltip: 'Ver texto orientador',
            delete_note_tooltip: 'Eliminar nota'
        },
        
        // Register Form - Textos del formulario de registro
        register: {
            select_type_placeholder: 'Seleccione el tipo de registro',
            natural_person: 'Persona Natural',
            company: 'Empresa'
        },
        
        // Steps/Navigation - Textos de navegación
        steps: {
            variables: 'Variables',
            matrix: 'Matriz',
            graphics: 'Gráfica',
            analysis: 'Mapa',
            hypothesis: 'Direccionador',
            schwartz: 'Schwartz',
            initial_conditions: 'Condiciones',
            scenarios: 'Escenarios',
            conclusions: 'Conclusiones',
            results: 'Resultados',
            new: 'Nueva'
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
        },
        isTextsLoaded: (state) => {
            return !state.isLoading && state.conclusions_section !== undefined;
        }
    },
    actions: {
        async loadTexts() {
            this.isLoading = true;
            try {
                const response = await fetch('/texts');
                const result = await response.json();
                
                if (result.status === 200) {
                    console.log('🔍 Debug: loadTexts called with:', result.data);
                    console.log('🔍 Debug: conclusions_section in result.data:', result.data.conclusions_section);
                    
                    for(const key in result.data) {
                        this[key] = result.data[key];
                    }
                    
                    console.log('🔍 Debug: After loadTexts, this.conclusions_section:', this.conclusions_section);
                }
            } catch (error) {
                console.error('Error loading texts:', error);
            } finally {
                this.isLoading = false;
            }
        },
        setTexts(objTexts){
            console.log('🔍 Debug: setTexts called with:', objTexts);
            console.log('🔍 Debug: conclusions_section in objTexts:', objTexts.conclusions_section);
            
            for(const key in objTexts)
            {
                this[key] = objTexts[key];
            }
            
            console.log('🔍 Debug: After setTexts, this.conclusions_section:', this.conclusions_section);
        },
        setLocale(locale){
            this.locale = locale;
        }
    }
});
