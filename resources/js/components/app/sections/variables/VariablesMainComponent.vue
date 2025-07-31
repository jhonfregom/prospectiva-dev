<template>
    <div class="variables-container">
        <!-- Letrero informativo -->
        <info-banner-component
            :description="textsStore.getText('variables_section.description')"
        />
        
        <!-- MiniStepper eliminado -->
        <b-table
            :data="variables"
            :loading="isLoading"
            :striped="true"
            :hoverable="true"
            default-sort="id"
            default-sort-direction="asc"
            :sort-icon="false"
            icon-pack="fas">

            <b-table-column field="id" :label="textsStore.getText('variables_section.table.variable')" v-slot="props" width="100" centered>
                {{ props.row.id_variable }}
            </b-table-column>

            <b-table-column field="name_variable" :label="textsStore.getText('variables_section.table.name')" v-slot="props" width="150" centered>
                {{ props.row.name_variable }}
            </b-table-column>

            <b-table-column field="description" :label="textsStore.getText('variables_section.table.description')" v-slot="props" class="description-column" centered>
                <b-input
                    type="textarea"
                    v-model="props.row.description"
                    @input="(event) => handleDescriptionChange(event, props.row)"
                    :disabled="editingRow !== props.row.id"
                    :placeholder="textsStore.getText('variables_section.description_placeholder')">
                </b-input>
            </b-table-column>

            <b-table-column field="score" :label="textsStore.getText('variables_section.table.score')" v-slot="props" numeric width="100" centered>
                {{ props.row.score || 0 }}
            </b-table-column>

            <b-table-column field="score" :label="textsStore.getText('variables_section.table.state')" v-slot="props" width="150" centered>
                <span :class="getStatusClass(props.row.score || 0)">
                    {{ getStateText(props.row.score || 0) }}
                </span>
            </b-table-column>

            <b-table-column :label="textsStore.getText('variables_section.table.actions')" v-slot="props" width="200" centered>
                <div class="buttons is-centered">
                    <b-button 
                        :type="editingRow === props.row.id ? 'is-success' : 'is-info'"
                        size="is-small"
                        :icon-left="editingRow === props.row.id ? 'save' : 'edit'"
                        @click="handleEditSave(props.row)"
                        outlined
                        :disabled="props.row.edits_variable >= 3"
                    >
                        {{ editingRow === props.row.id ? textsStore.getText('variables_section.table.save') : textsStore.getText('variables_section.table.edit') }}
                    </b-button>

                    <b-button 
                        v-if="isAdmin"
                        type="is-danger"
                        size="is-small"
                        icon-left="delete"
                        @click="confirmDelete(props.row)"
                        outlined>
                        {{ textsStore.getText('variables_section.table.delete') }}
                    </b-button>
                </div>
            </b-table-column>
        </b-table>

        <variable-form-modal
            v-if="showModal"
            @close="closeModal">
        </variable-form-modal>
    </div>
    <!-- Botón cerrar/regresar en la esquina inferior derecha -->
    <div class="cerrar-container">
      <button
        class="cerrar-btn"
        v-if="!cerrado"
        @click="confirmarCerrar"
        :disabled="cerrado"
      >Cerrar</button>
      <button
        class="cerrar-btn"
        v-else-if="state !== null && state === '0'"
        @click="confirmarRegresar"
      >Regresar</button>
    </div>
    <!-- Modal de confirmación -->
    <div v-if="mostrarModal" class="modal-confirm">
      <div class="modal-content">
        <p>¿Estás seguro de cerrar el módulo? No podrás editar más.</p>
        <button @click="cerrarModulo">Sí, cerrar</button>
        <button @click="mostrarModal = false">Cancelar</button>
      </div>
    </div>
    <!-- Modal de confirmación para regresar -->
    <div v-if="mostrarModalRegresar" class="modal-confirm">
      <div class="modal-content">
        <p>¿Está seguro que desea regresar? Solo podrá hacer esto una vez.</p>
        <button @click="regresarModulo">Sí, regresar</button>
        <button @click="mostrarModalRegresar = false">Cancelar</button>
      </div>
    </div>
    

</template>

<script>
import { useVariablesStore } from '../../../../stores/variables';
import { useSectionStore } from '../../../../stores/section';
import { useTextsStore } from '../../../../stores/texts';
import VariableFormModal from './VariableFormModal.vue';
import InfoBannerComponent from '../../ui/InfoBannerComponent.vue';
import { debounce } from 'lodash';
import { storeToRefs } from 'pinia';
import { useSessionStore } from '../../../../stores/session';
import axios from 'axios'; // Added axios import
import { computed } from 'vue';

const CERRADO_KEY_PREFIX = 'variables_cerrado_';

export default {
    components: {
        VariableFormModal,
        InfoBannerComponent,
    },

    setup() {
        const variablesStore = useVariablesStore();
        const sectionStore = useSectionStore();
        const textsStore = useTextsStore();
        const storeSession = useSessionStore();
        const { variables, isLoading } = storeToRefs(variablesStore);

        // Computed property para verificar si el usuario es administrador
        const isAdmin = computed(() => {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            return user.role === 1;
        });

        return { 
            variablesStore, 
            sectionStore,
            textsStore,
            variables,
            isLoading,
            storeSession,
            isAdmin
        };
    },

    data() {
        return {
            showModal: false,
            editingRow: null,
            debouncedUpdate: null,
            cerrado: false,
            mostrarModal: false,
            mostrarModalRegresar: false,
            state: null, // Se inicializa como null hasta cargar desde traceability
            steps: [
                { key: 'variables', label: 'Variables', icon: 'fas fa-list' },
                { key: 'matrix', label: 'Matriz', icon: 'fas fa-th' },
                { key: 'graphics', label: 'Gráfica', icon: 'fas fa-chart-bar' },
                { key: 'analysis', label: 'Mapa', icon: 'fas fa-map' },
                { key: 'hypothesis', label: 'Direccionador', icon: 'fas fa-bolt' },
                { key: 'schwartz', label: 'Schwartz', icon: 'fas fa-project-diagram' },
                { key: 'initialconditions', label: 'Condiciones', icon: 'fas fa-flag' },
                { key: 'scenarios', label: 'Escenarios', icon: 'fas fa-cubes' },
                { key: 'conclusions', label: 'Conclusiones', icon: 'fas fa-lightbulb' },
                { key: 'results', label: 'Resultados', icon: 'fas fa-trophy' },
                { key: 'nueva', label: 'Nueva', icon: 'fas fa-star' },
            ]
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

        // Leer estado de cerrado desde localStorage
        const user = JSON.parse(localStorage.getItem('user')) || {};
        const cerradoKey = CERRADO_KEY_PREFIX + (user.id || 'anon');
        const cerradoValue = localStorage.getItem(cerradoKey);
        this.cerrado = cerradoValue === 'true';
    },

    async mounted() {
        this.sectionStore.setTitleSection(this.textsStore.getText('variables.title'));
        await this.variablesStore.fetchVariables();
        // Cargar el valor de tried desde traceability
        await this.loadTriedValue();
        // Inicializar 'cerrado' leyendo de localStorage directamente
        if (typeof window !== 'undefined') {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            const cerradoKey = CERRADO_KEY_PREFIX + (user.id || 'anon');
            this.cerrado = localStorage.getItem(cerradoKey) === 'true';
        }
        this.sectionStore.addDynamicButton(this.textsStore.getText('variables_section.table.new'), () => {
            this.showModal = true;
        });
    },

    beforeUnmount() {
        // Limpiar botones dinámicos al desmontar el componente
        this.sectionStore.clearDynamicButtons();
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
            if (row.state === '1') return;

            if (this.editingRow === row.id) {
                // Guardar - incrementar edits_variable para ediciones manuales
                row.edits_variable = (row.edits_variable || 0) + 1;
                await this.updateVariableInServer(row);
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
                    message: this.textsStore.getText('variables_section.messages.update_error'),
                    type: 'is-danger'
                });
            }
        },

        confirmDelete(row) {
            this.$buefy.dialog.confirm({
                title: this.textsStore.getText('variables_section.messages.delete_confirm_title'),
                message: this.textsStore.getText('variables_section.messages.delete_confirm_message'),
                confirmText: this.textsStore.getText('variables_section.messages.delete_confirm_yes'),
                cancelText: this.textsStore.getText('variables_section.messages.delete_confirm_no'),
                type: 'is-danger',
                onConfirm: () => this.deleteVariable(row)
            });
        },

        async deleteVariable(row) {
            try {
                const success = await this.variablesStore.deleteVariable(row.id);
                if (success) {
                    this.$buefy.toast.open({
                        message: this.textsStore.getText('variables_section.messages.delete_success'),
                        type: 'is-success'
                    });
                }
            } catch (error) {
                this.$buefy.toast.open({
                    message: this.textsStore.getText('variables_section.messages.delete_error'),
                    type: 'is-danger'
                });
            }
        },

        getStatusClass(score) {
            if (score >= 0 && score <= 40) return 'has-text-danger';
            if (score >= 41 && score <= 80) return 'has-text-warning';
            if (score >= 81 && score <= 120) return 'has-text-info';
            return 'has-text-success';
        },

        getStateText(score) {
            if (score >= 0 && score <= 40) return 'DEBES MEJORAR';
            if (score >= 41 && score <= 80) return 'FALTA ALGO MAS';
            if (score >= 81 && score <= 120) return 'UN ESFUERZO MAS';
            return 'LO LOGRASTE';
        },

        closeModal() {
            this.showModal = false;
        },
        confirmarCerrar() {
          this.mostrarModal = true;
        },
        confirmarRegresar() {
          this.mostrarModalRegresar = true;
        },
        async cerrarModulo() {
          this.mostrarModal = false;
          try {
            // Cambia edits_variable a 3 en todas las variables y actualiza en el backend
            for (const v of this.variables) {
              v.edits_variable = 3;
              await this.updateVariableInServer(v);
            }
            // Guardar acción pendiente en localStorage
            localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'cerrar', modulo: 'variables' }));
            // Regresa a la vista principal
            this.storeSession.setActiveContent('main');
            // Guardar estado de cerrado en localStorage y cambiar la bandera después de volver al main
            const user = JSON.parse(localStorage.getItem('user')) || {};
            const cerradoKey = CERRADO_KEY_PREFIX + (user.id || 'anon');
            localStorage.setItem(cerradoKey, 'true');
            this.cerrado = true;
          } catch (error) {
            this.$buefy.toast.open({
              message: 'Error al cerrar el módulo de variables',
              type: 'is-danger'
            });
          }
        },
        async loadTriedValue() {
            try {
                const response = await axios.get('/traceability/current-route-state');
                if (response.data && response.data.success && response.data.state !== undefined) {
                    this.state = response.data.state;
                }
            } catch (error) {
                console.error('Error al cargar state:', error);
            }
        },

        async incrementTried() {
            try {
                await axios.put('/traceability/current-route-state', { state: '1' });
                this.state = '1';
            } catch (error) {
                console.error('Error al actualizar state:', error);
            }
        },

        async regresarModulo() {
            this.mostrarModalRegresar = false;
            try {
                // Incrementar state a 1
                await this.incrementTried();
                // Volver a cargar el valor actualizado de state
                await this.loadTriedValue();
                // Cambia edits_variable y state a 0 en todas las variables y actualiza en el backend
                for (const v of this.variables) {
                    v.edits_variable = 0;
                    v.state = 0;
                    await this.updateVariableInServer(v);
                }
                // Guardar acción pendiente en localStorage
                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'regresar', modulo: 'variables' }));
                // Regresa a la vista principal
                this.storeSession.setActiveContent('main');
                // Eliminar estado de cerrado en localStorage y cambiar la bandera después de volver al main
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const cerradoKey = CERRADO_KEY_PREFIX + (user.id || 'anon');
                localStorage.removeItem(cerradoKey);
                this.cerrado = false;
            } catch (error) {
                this.$buefy.toast.open({
                    message: 'Error al regresar el módulo de variables',
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

.has-text-danger {
    color: #ff3860 !important;
    font-weight: 600;
}

.has-text-warning {
    color: #e6a700 !important;
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
:deep(.b-table .table tbody td) {
    vertical-align: middle !important;
    height: 80px !important;
}

/* Centrado de encabezados de tabla */
:deep(.b-table .table thead th) {
    text-align: center !important;
}
.cerrar-container {
  position: fixed;
  bottom: 32px;
  right: 48px;
  z-index: 100;
}
.cerrar-btn {
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 14px 32px;
  font-size: 1.2rem;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(50,115,220,0.08);
  cursor: pointer;
  transition: background 0.2s;
}
.cerrar-btn:disabled {
  background: #b0b0b0;
  cursor: not-allowed;
}
.modal-confirm {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
}
.modal-content {
  background: white;
  padding: 32px 48px;
  border-radius: 12px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.15);
  text-align: center;
}
.modal-content button {
  margin: 0 12px;
  padding: 10px 24px;
  border-radius: 6px;
  border: none;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
}
</style>
