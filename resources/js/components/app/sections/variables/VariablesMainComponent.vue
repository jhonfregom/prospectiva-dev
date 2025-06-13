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
         * Maneja el cambio en la descripción de una variable
         * 
         * Este método:
         * 1. Calcula el nuevo score basado en el conteo de palabras
         * 2. Actualiza el estado local
         * 3. Programa una actualización en el servidor
         * 
         * @param {Event} event - Evento de input
         * @param {Object} row - Fila de la variable siendo editada
         */
        handleDescriptionChange(event, row) {
            // Calcula el score basado en el conteo de palabras
            const wordCount = event.target.value.trim().split(/\s+/).length;
            row.score = wordCount;

            // Programa la actualización en el servidor
            this.debouncedUpdate(row);
        },

        /**
         * Maneja el clic en el botón de editar/guardar
         * 
         * Este método:
         * 1. Alterna el modo de edición
         * 2. Actualiza el servidor si se está guardando
         * 
         * @param {Object} row - Fila de la variable siendo editada
         */
        async handleEditSave(row) {
            if (this.editingRow === row.id) {
                // Estamos guardando
                await this.updateVariableInServer(row);
                this.editingRow = null;
            } else {
                // Estamos entrando en modo edición
                this.editingRow = row.id;
            }
        },

        /**
         * Actualiza una variable en el servidor
         * 
         * Este método:
         * 1. Envía los cambios al servidor
         * 2. Maneja errores de la operación
         * 
         * @param {Object} variable - Variable a actualizar
         */
        async updateVariableInServer(variable) {
            try {
                await this.variablesStore.updateVariable(variable);
            } catch (error) {
                this.$buefy.toast.open({
                    message: 'Error al actualizar la variable',
                    type: 'is-danger'
                });
            }
        },

        /**
         * Confirma la eliminación de una variable
         * 
         * Este método:
         * 1. Muestra un diálogo de confirmación
         * 2. Elimina la variable si se confirma
         * 
         * @param {Object} row - Fila de la variable a eliminar
         */
        confirmDelete(row) {
            this.$buefy.dialog.confirm({
                title: 'Eliminar Variable',
                message: '¿Está seguro de eliminar esta variable?',
                confirmText: 'Eliminar',
                cancelText: 'Cancelar',
                type: 'is-danger',
                onConfirm: () => this.deleteVariable(row)
            });
        },

        /**
         * Elimina una variable
         * 
         * Este método:
         * 1. Envía la solicitud de eliminación al servidor
         * 2. Actualiza la UI según el resultado
         * 
         * @param {Object} row - Fila de la variable a eliminar
         */
        async deleteVariable(row) {
            try {
                const success = await this.variablesStore.deleteVariable(row.id);
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
    }
};
</script>

<style lang="scss" scoped>
.variables-container {
    padding: 20px;
}

.description-column {
    min-width: 300px;
}

.buttons {
    gap: 0.5rem;
}
</style>