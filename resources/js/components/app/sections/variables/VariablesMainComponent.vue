<script>
import { useVariablesStore } from '../../../../stores/variables';
import { useSectionStore } from '../../../../stores/section';
import VariableFormModal from './VariableFormModal.vue';
import { debounce } from 'lodash';
import { storeToRefs } from 'pinia';

export default {
    components: {
        VariableFormModal
    },

    setup() {
        const variablesStore = useVariablesStore();
        const sectionStore = useSectionStore();
        const { variables, isLoading } = storeToRefs(variablesStore);

        return { 
            variablesStore, 
            sectionStore,
            variables,
            isLoading
        };
    },

    data() {
        return {
            showModal: false,
            editingRow: null,
            debouncedUpdate: null
        };
    },

    created() {
        // Crear función debounced para actualizar automáticamente
        this.debouncedUpdate = this.debounce(async (row) => {
            // Solo actualizar si no está bloqueada y no está en modo edición manual
            if (row.state !== '1' && this.editingRow !== row.id) {
                await this.updateVariableInServer(row);
            }
        }, 1000);
    },

    mounted() {
        this.sectionStore.setTitleSection('Variables');
        this.sectionStore.addDynamicButton('Nuevo', () => {
            this.showModal = true;
        });
        this.loadVariables();
    },

    methods: {
        // Función debounce helper
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        async loadVariables() {
            await this.variablesStore.fetchVariables();
        },

        handleDescriptionChange(event, row) {
            row.score = event.target.value.split(/\s+/).filter(word => word.length > 0).length;
            // this.debouncedUpdate(row); // Comentado temporalmente
        },

        async handleEditSave(row) {
            console.log('handleEditSave - Variable ID:', row.id, 'Current state:', row.state);
            
            if (row.state === '1') return;

            if (this.editingRow === row.id) {
                // Guardar
                console.log('Saving variable ID:', row.id);
                await this.updateVariableInServer(row);
                console.log('After save - Variable ID:', row.id, 'New state:', row.state);
                this.editingRow = null;
            } else {
                // Entrar en modo edición
                this.editingRow = row.id;
            }
        },

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
        },

        closeModal() {
            this.showModal = false;
        },

        getStatusClass(score) {
            if (score <= 25) {
                return 'has-text-danger';
            } else if (score <= 50) {
                return 'has-text-warning';
            } else if (score <= 100) {
                return 'has-text-info';
            } else {
                return 'has-text-success';
            }
        },

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
        }
    }
};
</script>

<template>
    <div class="variables-container">
        <b-table
            :data="variables"
            :loading="isLoading"
            :striped="true"
            :hoverable="true"
            default-sort="id"
            default-sort-direction="desc"
            sort-icon="arrow-up"
            icon-pack="fas">

            <b-table-column field="id" label="VARIABLE" v-slot="props" width="100" sortable>
                {{ props.row.id_variable }}
            </b-table-column>

            <b-table-column field="name_variable" label="NOMBRE" v-slot="props" width="150">
                {{ props.row.name_variable }}
            </b-table-column>

            <b-table-column field="description" label="DESCRIPCIÓN" v-slot="props" class="description-column">
                <b-input
                    type="textarea"
                    v-model="props.row.description"
                    @input="(event) => handleDescriptionChange(event, props.row)"
                    :disabled="editingRow !== props.row.id"
                    placeholder="Escriba la descripción de la variable">
                </b-input>
            </b-table-column>

            <b-table-column field="score" label="SCORE" v-slot="props" numeric width="100">
                {{ props.row.score || 0 }}
            </b-table-column>

            <b-table-column field="score" label="ESTADO" v-slot="props" width="150">
                <span :class="getStatusClass(props.row.score || 0)">
                    {{ getStateText(props.row.score || 0) }}
                </span>
            </b-table-column>

            <b-table-column label="ACCIONES" v-slot="props" width="200" centered>
                <div class="buttons is-centered">
                    <b-button 
                        :type="editingRow === props.row.id ? 'is-success' : 'is-info'"
                        size="is-small"
                        :icon-left="editingRow === props.row.id ? 'save' : 'edit'"
                        @click="handleEditSave(props.row)"
                        outlined
                        :disabled="props.row.state === '1'"
                    >
                        {{ editingRow === props.row.id ? 'Guardar' : 'Editar' }}
                    </b-button>

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

        <variable-form-modal
            v-if="showModal"
            @close="closeModal">
        </variable-form-modal>
    </div>
</template>

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

.has-text-danger {
    color: #ff3860 !important;
    font-weight: 600;
}

.has-text-warning {
    color: #ffdd57 !important;
    font-weight: 600;
}

.has-text-info {
    color: #3298dc !important;
    font-weight: 600;
}

.has-text-success {
    color: #48c774 !important;
    font-weight: 600;
}

/* Centrado vertical SOLO en filas de datos (tbody td) de la tabla de variables */
::v-deep .b-table .table tbody td {
    vertical-align: middle !important;
    height: 80px !important;
}
</style>
