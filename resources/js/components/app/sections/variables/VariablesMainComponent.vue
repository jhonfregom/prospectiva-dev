<!-- 
    VariablesMainComponent.vue
    
    Este componente implementa la vista principal del módulo de variables.
    Proporciona una interfaz para:
    - Visualizar todas las variables en una tabla
    - Editar descripciones y ver scores en tiempo real
    - Eliminar variables existentes
    - Crear nuevas variables mediante un modal
-->
<template>
    <div class="variables-container">
        <!-- Tabla principal de variables usando Buefy -->
        <b-table
            :data="variablesStore.variables"
            :loading="variablesStore.isLoading"
            :striped="true"
            :hoverable="true"
            default-sort="id"
            default-sort-direction="desc"
            sort-icon="arrow-up"
            icon-pack="fas">

            <!-- Columna para el ID de la variable (V1, V2, etc.) -->
            <b-table-column field="id" label="VARIABLE" v-slot="props" width="100" sortable>
                {{ props.row.id_variable }}
            </b-table-column>

            <!-- Columna para el nombre de la variable -->
            <b-table-column field="name_variable" label="NOMBRE" v-slot="props" width="150">
                {{ props.row.name_variable }}
            </b-table-column>

            <!-- Columna para la descripción con textarea editable -->
            <b-table-column field="description" label="DESCRIPCIÓN" v-slot="props" class="description-column">
                <b-input
                    type="textarea"
                    v-model="props.row.description"
                    @input="(event) => handleDescriptionChange(event, props.row)"
                    :disabled="editingRow !== props.row.id"
                    placeholder="Escriba la descripción de la variable">
                </b-input>
            </b-table-column>

            <!-- Columna para mostrar el score (conteo de palabras) -->
            <b-table-column field="score" label="SCORE" v-slot="props" numeric width="100">
                {{ props.row.score || 0 }}
            </b-table-column>

            <!-- Columna para mostrar el estado basado en el score -->
            <b-table-column field="score" label="ESTADO" v-slot="props" width="150">
                <span :class="getStatusClass(props.row.score || 0)">
                    {{ getStateText(props.row.score || 0) }}
                </span>
            </b-table-column>

            <!-- Columna para acciones (editar y eliminar) -->
            <b-table-column label="ACCIONES" v-slot="props" width="200" centered>
                <div class="buttons is-centered">
                    <!-- Botón de editar/guardar que cambia según el estado -->
                    <b-button 
                        :type="editingRow === props.row.id ? 'is-success' : 'is-info'"
                        size="is-small"
                        :icon-left="editingRow === props.row.id ? 'save' : 'edit'"
                        @click="handleEditSave(props.row)"
                        outlined>
                        {{ editingRow === props.row.id ? 'Guardar' : 'Editar' }}
                    </b-button>

                    <!-- Botón de eliminar con confirmación -->
                    <b-button 
                        type="is-danger"
                        size="is-small"
                        icon-left="delete"
                        @click="confirmDelete(props.row)"
                        outlined>
                        Eliminar
                    </b-button>
                </div>
            </b-table-column>
        </b-table>

        <!-- Modal para crear nuevas variables -->
        <variable-form-modal
            v-if="showModal"
            @close="closeModal" />
    </div>
</template>

<script>
import { useVariablesStore } from '../../../../stores/variables';
import { useSectionStore } from '../../../../stores/section';
import VariableFormModal from './VariableFormModal.vue';
import { debounce } from 'lodash';
import { storeToRefs } from 'pinia';

export default {
    // Registra el componente del modal de creación
    components: {
        VariableFormModal
    },

    /**
     * Setup del componente usando Composition API
     * 
     * Inicializa:
     * - Store de variables para CRUD
     * - Store de sección para UI
     * - Referencias reactivas a variables y estado de carga
     */
    setup() {
        const variablesStore = useVariablesStore();
        const sectionStore = useSectionStore();
        
        // Usamos storeToRefs para mantener la reactividad de las propiedades
        const { variables, isLoading } = storeToRefs(variablesStore);
        
        return { 
            variablesStore, 
            sectionStore,
            variables,
            isLoading
        };
    },

    /**
     * Estado local del componente
     * 
     * Maneja:
     * - Visibilidad del modal de creación
     * - Control de actualizaciones retardadas
     * - Estado de edición de filas
     */
    data() {
        return {
            showModal: false,           // Controla la visibilidad del modal
            updateTimeout: null,        // Para el debounce de actualizaciones
            editingRow: null,          // Controla qué fila está en modo edición
        };
    },

    /**
     * Ciclo de vida: Created
     * 
     * Configura el debounce para actualizaciones:
     * - Espera 1 segundo después del último cambio
     * - Evita múltiples llamadas al servidor
     */
    created() {
        this.debouncedUpdate = debounce(this.updateVariableInServer, 1000);
    },

    /**
     * Ciclo de vida: Mounted
     * 
     * Inicializa el componente:
     * 1. Configura el título de la sección
     * 2. Agrega el botón de creación
     * 3. Carga los datos iniciales
     */
    mounted() {
        // Configura el título de la sección
        this.sectionStore.setTitleSection('Variables');
        
        // Agrega el botón "Nuevo" en la barra de título
        this.sectionStore.addDynamicButton('Nuevo', () => {
            this.showModal = true;
        });
        
        // Carga las variables del servidor
        this.loadVariables();
    },

    methods: {
        /**
         * Carga inicial de variables desde el servidor
         * 
         * Utiliza el store para:
         * 1. Hacer la petición al servidor
         * 2. Actualizar el estado global
         * 3. Reflejar los cambios en la UI
         */
        async loadVariables() {
            await this.variablesStore.fetchVariables();
        },

        /**
         * Cierra el modal de creación
         * 
         * Se ejecuta cuando:
         * - Se cancela la creación
         * - Se completa la creación exitosamente
         */
        closeModal() {
            this.showModal = false;
        },

        /**
         * Extrae el ID numérico de la variable
         * 
         * Ejemplo: 'V1' -> '1'
         * 
         * @param {string} id_variable - ID con formato (ej: 'V1')
         * @returns {string} ID numérico
         */
        getVariableId(id_variable) {
            return id_variable.split(' ')[0];
        },

        /**
         * Obtiene el texto del estado basado en el score
         * 
         * Rangos:
         * - ≤ 25: "DEBES MEJORAR"
         * - ≤ 50: "FALTA ALGO MAS"
         * - ≤ 100: "UN ESFUERZO MAS"
         * - > 100: "LO LOGRASTE"
         * 
         * @param {number} score - Puntuación de la variable
         * @returns {string} Texto descriptivo del estado
         */
        getStateText(score) {
            if (score <= 25) {
                return 'DEBES MEJORAR';
            } else if (score <= 50) {
                return 'FALTA ALGO MAS';
            } else if (score <= 100) {
                return 'UN ESFUERZO MAS';
            } else {
                return 'LO LOGRASTE';
            }
        },

        /**
         * Determina la clase CSS basada en el score
         * 
         * Clases:
         * - ≤ 25: has-text-danger (rojo)
         * - ≤ 50: has-text-warning (amarillo)
         * - ≤ 100: has-text-info (azul)
         * - > 100: has-text-success (verde)
         * 
         * @param {number} score - Puntuación de la variable
         * @returns {Object} Objeto con clases CSS
         */
        getStatusClass(score) {
            return {
                'has-text-danger': score <= 25,
                'has-text-warning': score > 25 && score <= 50,
                'has-text-info': score > 50 && score <= 100,
                'has-text-success': score > 100
            };
        },

        /**
         * Maneja los cambios en la descripción
         * 
         * Este método:
         * 1. Verifica si la fila está en modo edición
         * 2. Actualiza la descripción localmente
         * 3. Calcula el nuevo score basado en palabras
         * 4. Actualiza el estado de la variable
         * 
         * @param {Event|string} event - Evento de input o valor directo
         * @param {Object} variable - Variable siendo editada
         */
        handleDescriptionChange(event, variable) {
            // Solo procesar cambios si la fila está en modo edición
            if (this.editingRow === variable.id) {
                // Obtiene el valor del evento
                const newDescription = typeof event === 'string' ? event : event.target.value;
                
                // Actualiza la descripción localmente
                variable.description = newDescription;
                
                // Calcula el score basado en el conteo de palabras
                if (!newDescription || newDescription.trim() === '') {
                    variable.score = 0;
                } else {
                    const wordCount = newDescription.trim().split(/\s+/).filter(word => word.length > 0).length;
                    variable.score = wordCount;
                }
            }
        },

        /**
         * Maneja el clic en el botón de editar/guardar
         * 
         * Comportamiento:
         * - Si no está editando: Activa modo edición
         * - Si está editando: Guarda cambios y desactiva edición
         * 
         * @param {Object} variable - Variable a editar/guardar
         */
        async handleEditSave(variable) {
            if (this.editingRow === variable.id) {
                // Estamos guardando
                try {
                    const success = await this.variablesStore.updateVariable({
                        id: variable.id,
                        description: variable.description,
                        score: variable.score
                    });

                    if (success) {
                        this.editingRow = null; // Desactivar modo edición
                    }
                } catch (error) {
                    this.$buefy.toast.open({
                        message: 'Error al actualizar la variable',
                        type: 'is-danger'
                    });
                }
            } else {
                // Estamos entrando en modo edición
                this.editingRow = variable.id;
            }
        },

        /**
         * Confirma y ejecuta la eliminación de una variable
         * 
         * Muestra un diálogo de confirmación y:
         * 1. Si se confirma: Elimina la variable
         * 2. Si se cancela: No hace nada
         * 3. Maneja errores mostrando notificaciones
         * 
         * @param {Object} variable - Variable a eliminar
         */
        confirmDelete(variable) {
            this.$buefy.dialog.confirm({
                title: 'Eliminar Variable',
                message: `¿Estás seguro de que deseas eliminar la variable <b>${variable.name_variable}</b>?`,
                confirmText: 'Eliminar',
                cancelText: 'Cancelar',
                type: 'is-danger',
                hasIcon: true,
                onConfirm: async () => {
                    try {
                        const success = await this.variablesStore.deleteVariable(variable.id);
                        if (success) {
                            this.$buefy.toast.open({
                                message: 'Variable eliminada correctamente',
                                type: 'is-success'
                            });
                        }
                    } catch (error) {
                        this.$buefy.toast.open({
                            message: 'Error al eliminar la variable',
                            type: 'is-danger'
                        });
                    }
                }
            });
        },

        /**
         * Actualiza la variable en el servidor
         * 
         * Este método:
         * 1. Envía los cambios al servidor
         * 2. Maneja errores de la actualización
         * 3. Muestra notificaciones de error
         * 
         * @param {Object} variable - Variable a actualizar
         */
        async updateVariableInServer(variable) {
            try {
                await this.variablesStore.updateVariable({
                    id: variable.id,
                    description: variable.description,
                    score: variable.score
                });
            } catch (error) {
                this.$buefy.toast.open({
                    message: 'Error al actualizar la variable',
                    type: 'is-danger'
                });
            }
        }
    }
};
</script>

<style lang="scss" scoped>
/* Contenedor principal */
.variables-container {
    padding: 1.5rem;
}

/* Estilos para la tabla */
:deep(.table) {
    background-color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Separadores entre filas */
:deep(.table tr) {
    border-bottom: 1px solid #f5f5f5;
}

:deep(.table tr:last-child) {
    border-bottom: none;
}

/* Estilos para el textarea */
:deep(.textarea) {
    min-height: 120px;
    border: 1px solid #dbdbdb;
    border-radius: 4px;
    padding: 0.5rem;
    width: 100%;
    resize: vertical;
    font-size: 0.95rem;
    line-height: 1.5;
}

:deep(.textarea:focus) {
    border-color: #485fc7;
    box-shadow: 0 0 0 0.125em rgba(72, 95, 199, 0.25);
}

/* Estilos para la columna de descripción */
:deep(.table td.description-column) {
    width: 50%;
    max-width: none;
}

/* Colores para los estados */
.has-text-success {
    color: #48c774 !important;
    font-weight: bold;
}

.has-text-info {
    color: #3e8ed0 !important;
    font-weight: bold;
}

.has-text-warning {
    color: #ffe08a !important;
    font-weight: bold;
}

.has-text-danger {
    color: #f14668 !important;
    font-weight: bold;
}
</style>