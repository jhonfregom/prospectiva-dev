<template>
    <div class="variables-container">
        <!-- Letrero informativo -->
        <info-banner-component
            :description="textsStore.getText('variables_section.description')"
            class="description-banner"
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
                    <edit-button-component
                        :is-editing="editingRow === props.row.id"
                        :is-locked="props.row.edits_variable >= 3"
                        :edit-text="textsStore.getText('variables_section.table.edit')"
                        :save-text="textsStore.getText('variables_section.table.save')"
                        :locked-text="textsStore.getText('variables_section.table.locked')"
                        @click="handleEditSave(props.row)"
                    />

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
      >{{ textsStore.getText('variables_section.close_button') || 'Cerrar' }}</button>
      <button
        class="cerrar-btn"
        v-else-if="state !== null && state === '0'"
        @click="confirmarRegresar"
      >{{ textsStore.getText('variables_section.return_button') || 'Regresar' }}</button>
    </div>
    <!-- Modal de confirmación -->
    <div v-if="mostrarModal" class="modal-confirm">
      <div class="modal-content">
        <p class="modal-text">{{ textsStore.getText('variables_section.close_confirm_message') || '¿Estás seguro de cerrar el módulo? No podrás editar más.' }}</p>
        <button @click="cerrarModulo">{{ textsStore.getText('variables_section.confirm_yes') || 'Sí, cerrar' }}</button>
        <button @click="mostrarModal = false">{{ textsStore.getText('variables_section.confirm_no') || 'Cancelar' }}</button>
      </div>
    </div>
    <!-- Modal de confirmación para regresar -->
    <div v-if="mostrarModalRegresar" class="modal-confirm">
      <div class="modal-content">
        <p class="modal-text">{{ textsStore.getText('variables_section.return_confirm_message') || '¿Está seguro que desea regresar? Solo podrá hacer esto una vez.' }}</p>
        <button @click="regresarModulo">{{ textsStore.getText('variables_section.confirm_yes_return') || 'Sí, regresar' }}</button>
        <button @click="mostrarModalRegresar = false">{{ textsStore.getText('variables_section.confirm_no') || 'Cancelar' }}</button>
      </div>
    </div>

</template>

<script>
import { useVariablesStore } from '../../../../stores/variables';
import { useSectionStore } from '../../../../stores/section';
import { useTextsStore } from '../../../../stores/texts';
import { useTraceabilityStore } from '../../../../stores/traceability';
import VariableFormModal from './VariableFormModal.vue';
import InfoBannerComponent from '../../ui/InfoBannerComponent.vue';
import EditButtonComponent from '../../ui/EditButtonComponent.vue';
import { debounce } from 'lodash';
import { storeToRefs } from 'pinia';
import { useSessionStore } from '../../../../stores/session';
import axios from 'axios'; 
import { computed } from 'vue';

const CERRADO_KEY_PREFIX = 'variables_cerrado_';

export default {
    components: {
        VariableFormModal,
        InfoBannerComponent,
        EditButtonComponent,
    },

    setup() {
        const variablesStore = useVariablesStore();
        const sectionStore = useSectionStore();
        const textsStore = useTextsStore();
        const traceabilityStore = useTraceabilityStore();
        const storeSession = useSessionStore();
        const { variables, isLoading } = storeToRefs(variablesStore);

        const isAdmin = computed(() => {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            return user.role === 1;
        });

        const steps = computed(() => [
            { key: 'variables', label: textsStore.getText('steps.variables'), icon: 'fas fa-list' },
            { key: 'matrix', label: textsStore.getText('steps.matrix'), icon: 'fas fa-th' },
            { key: 'graphics', label: textsStore.getText('steps.graphics'), icon: 'fas fa-chart-bar' },
            { key: 'analysis', label: textsStore.getText('steps.analysis'), icon: 'fas fa-map' },
            { key: 'hypothesis', label: textsStore.getText('steps.hypothesis'), icon: 'fas fa-bolt' },
            { key: 'schwartz', label: textsStore.getText('steps.schwartz'), icon: 'fas fa-project-diagram' },
            { key: 'initialconditions', label: textsStore.getText('steps.initial_conditions'), icon: 'fas fa-flag' },
            { key: 'scenarios', label: textsStore.getText('steps.scenarios'), icon: 'fas fa-cubes' },
            { key: 'conclusions', label: textsStore.getText('steps.conclusions'), icon: 'fas fa-lightbulb' },
            { key: 'results', label: textsStore.getText('steps.results'), icon: 'fas fa-trophy' },
            { key: 'nueva', label: textsStore.getText('steps.new'), icon: 'fas fa-star' },
        ]);

        return { 
            variablesStore, 
            sectionStore,
            textsStore,
            traceabilityStore,
            variables,
            isLoading,
            storeSession,
            isAdmin,
            steps
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
            state: null, 
            steps: []
        };
    },

    created() {
        
        this.debouncedUpdate = this.debounce(async (row) => {
            
            if (row.state !== '1' && this.editingRow !== row.id) {
                await this.updateVariableInServer(row);
            }
        }, 1000);

        const user = JSON.parse(localStorage.getItem('user')) || {};
        const cerradoKey = CERRADO_KEY_PREFIX + (user.id || 'anon');
        const cerradoValue = localStorage.getItem(cerradoKey);
        this.cerrado = cerradoValue === 'true';
    },

    async mounted() {
        this.sectionStore.setTitleSection(this.textsStore.getText('variables.title'));
        await this.variablesStore.fetchVariables();
        
        await this.loadTriedValue();
        
        if (typeof window !== 'undefined') {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            const cerradoKey = CERRADO_KEY_PREFIX + (user.id || 'anon');
            this.cerrado = localStorage.getItem(cerradoKey) === 'true';
        }
        this.sectionStore.addDynamicButton(this.textsStore.getText('variables_section.table.new'), () => {
            this.showModal = true;
        });

        console.log('Debug textos botón cerrar:', {
            close_button: this.textsStore.getText('variables_section.close_button'),
            return_button: this.textsStore.getText('variables_section.return_button'),
            close_confirm_message: this.textsStore.getText('variables_section.close_confirm_message'),
            confirm_yes: this.textsStore.getText('variables_section.confirm_yes'),
            confirm_no: this.textsStore.getText('variables_section.confirm_no')
        });

        window.addEventListener('route-created', this.handleRouteCreated);
    },

    beforeUnmount() {
        
        this.sectionStore.clearDynamicButtons();
        
        window.removeEventListener('route-created', this.handleRouteCreated);
    },

    methods: {
        
        handleRouteCreated() {
            console.log('handleRouteCreated ejecutado');

            this.$forceUpdate();

            this.loadTriedValue();

            this.sectionStore.clearDynamicButtons();
            this.sectionStore.addDynamicButton(this.textsStore.getText('variables_section.table.new'), () => {
                this.showModal = true;
            });

            console.log('Debug textos después de route-created:', {
                close_button: this.textsStore.getText('variables_section.close_button'),
                return_button: this.textsStore.getText('variables_section.return_button'),
                close_confirm_message: this.textsStore.getText('variables_section.close_confirm_message'),
                confirm_yes: this.textsStore.getText('variables_section.confirm_yes'),
                confirm_no: this.textsStore.getText('variables_section.confirm_no')
            });
        },

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
            
        },

        async handleEditSave(row) {
            if (row.state === '1') return;

            if (this.editingRow === row.id) {
                
                row.edits_variable = (row.edits_variable || 0) + 1;
                await this.updateVariableInServer(row);
                this.editingRow = null;
            } else {
                
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
            
            for (const v of this.variables) {
              v.edits_variable = 3;
              await this.updateVariableInServer(v);
            }

            await this.traceabilityStore.markSectionCompleted('variables');

            localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'cerrar', modulo: 'variables' }));
            
            this.storeSession.setActiveContent('main');
            
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
                
                await this.incrementTried();
                
                await this.loadTriedValue();
                
                for (const v of this.variables) {
                    v.edits_variable = 0;
                    v.state = 0;
                    await this.updateVariableInServer(v);
                }
                
                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'regresar', modulo: 'variables' }));
                
                this.storeSession.setActiveContent('main');
                
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

.description-banner {
    text-align: justify !important;
    line-height: 1.6;
}

.modal-text {
    text-align: justify !important;
    line-height: 1.5;
    margin-bottom: 20px;
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

:deep(.b-table .table tbody td) {
    vertical-align: middle !important;
    height: 80px !important;
}

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
  background: #005883;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 14px 32px;
  font-size: 1.2rem;
  font-weight: bold;
  box-shadow: 0 2px 8px rgba(0,88,131,0.2);
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
  background: #005883;
  color: white;
  transition: background 0.2s;
}

.modal-content button:hover {
  background: #004466;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .variables-container {
    padding: 15px;
  }
  
  .description-column {
    min-width: 250px;
  }
}

@media (max-width: 768px) {
  .variables-container {
    padding: 10px;
  }
  
  .description-column {
    min-width: 200px;
  }
  
  .cerrar-container {
    bottom: 20px;
    right: 20px;
  }
  
  .cerrar-btn {
    padding: 12px 24px;
    font-size: 1rem;
  }
  
  .modal-content {
    padding: 24px 32px;
    margin: 20px;
  }
  
  .modal-content button {
    margin: 8px;
    padding: 8px 16px;
    font-size: 0.9rem;
  }
  
  /* Tabla responsive */
  :deep(.b-table) {
    overflow-x: auto;
  }
  
  :deep(.b-table .table) {
    min-width: 600px;
  }
  
  :deep(.b-table .table tbody td) {
    height: 60px !important;
    padding: 8px 4px !important;
    font-size: 0.9rem;
  }
  
  :deep(.b-table .table thead th) {
    padding: 8px 4px !important;
    font-size: 0.9rem;
  }
}

@media (max-width: 480px) {
  .variables-container {
    padding: 5px;
  }
  
  .description-column {
    min-width: 150px;
  }
  
  .cerrar-container {
    bottom: 15px;
    right: 15px;
  }
  
  .cerrar-btn {
    padding: 10px 20px;
    font-size: 0.9rem;
  }
  
  .modal-content {
    padding: 20px 24px;
    margin: 10px;
  }
  
  .modal-content button {
    margin: 4px;
    padding: 6px 12px;
    font-size: 0.8rem;
  }
  
  :deep(.b-table .table tbody td) {
    height: 50px !important;
    padding: 6px 2px !important;
    font-size: 0.8rem;
  }
  
  :deep(.b-table .table thead th) {
    padding: 6px 2px !important;
    font-size: 0.8rem;
  }
}
</style>