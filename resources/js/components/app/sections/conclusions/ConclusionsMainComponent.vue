<template>
    <div class="conclusions-container" :key="forceRerender">
        <!-- MiniStepper eliminado -->
        
        <!-- Mostrar mensaje de carga si los textos no están disponibles -->
        <div v-if="!textsStore.conclusions_section" class="loading-message">
            <p>Cargando textos...</p>
        </div>
        
        <div v-else>
            <!-- Letrero informativo -->
            <info-banner-component
                :description="textsStore.getText('conclusions_section.description')"
            />
            
            <div class="conclusions-table">
            <div class="table-content">
                <!-- Primera fila: Título -->
                <div class="table-row title-row">
                    <div class="row-content">
                        <h3 class="title is-4 has-text-centered">{{ textsStore.getText('conclusions_section.title') }}</h3>
                    </div>
                </div>

                <!-- Segunda fila: Subtítulo Componente Práctico -->
                <div class="table-row subtitle-row">
                    <div class="row-content">
                        <h4 class="title is-5 subtitle-text">{{ textsStore.getText('conclusions_section.component_practice_subtitle') }}</h4>
                    </div>
                </div>

                <!-- Tercera fila: Textarea Componente Práctico -->
                <div class="table-row textarea-row">
                    <div class="row-content">
                        <div class="textarea-flex">
                            <textarea
                                v-model="conclusionsData.component_practice"
                                :disabled="!isComponentPracticeEditing || !isComponentPracticeEditable"
                                :placeholder="textsStore.getText('conclusions_section.component_practice_placeholder')"
                                rows="4"
                                class="custom-textarea"
                                @input="handleTextInput('component_practice', $event)"
                                @paste="handleTextPaste('component_practice', $event)"
                                @keyup="handleTextKeyup('component_practice', $event)">
                            </textarea>
                        </div>
                    </div>
                    <div class="row-actions">
                        <edit-button-component
                            :is-editing="isComponentPracticeEditing"
                            :is-locked="!isComponentPracticeEditable"
                            :edit-text="textsStore.getText('conclusions_section.table.edit')"
                            :save-text="textsStore.getText('conclusions_section.table.save')"
                            :locked-text="textsStore.getText('conclusions_section.table.locked')"
                            @click="handleComponentPracticeEditSave"
                        />
                    </div>
                </div>

                <!-- Cuarta fila: Subtítulo Actualidad -->
                <div class="table-row subtitle-row">
                    <div class="row-content">
                        <h4 class="title is-5 subtitle-text">{{ textsStore.getText('conclusions_section.actuality_subtitle') }}</h4>
                    </div>
                </div>

                <!-- Quinta fila: Textarea Actualidad -->
                <div class="table-row textarea-row">
                    <div class="row-content">
                        <div class="textarea-flex">
                            <textarea
                                v-model="conclusionsData.actuality"
                                :disabled="!isActualityEditing || !isActualityEditable"
                                :placeholder="textsStore.getText('conclusions_section.actuality_placeholder')"
                                rows="4"
                                class="custom-textarea"
                                @input="handleTextInput('actuality', $event)"
                                @paste="handleTextPaste('actuality', $event)"
                                @keyup="handleTextKeyup('actuality', $event)">
                            </textarea>
                        </div>
                    </div>
                    <div class="row-actions">
                        <edit-button-component
                            :is-editing="isActualityEditing"
                            :is-locked="!isActualityEditable"
                            :edit-text="textsStore.getText('conclusions_section.table.edit')"
                            :save-text="textsStore.getText('conclusions_section.table.save')"
                            :locked-text="textsStore.getText('conclusions_section.table.locked')"
                            @click="handleActualityEditSave"
                        />
                    </div>
                </div>

                <!-- Sexta fila: Subtítulo Aplicación -->
                <div class="table-row subtitle-row">
                    <div class="row-content">
                        <h4 class="title is-5 subtitle-text">{{ textsStore.getText('conclusions_section.aplication_subtitle') }}</h4>
                    </div>
                </div>

                <!-- Séptima fila: Textarea Aplicación -->
                <div class="table-row textarea-row">
                    <div class="row-content">
                        <div class="textarea-flex">
                            <textarea
                                v-model="conclusionsData.aplication"
                                :disabled="!isAplicationEditing || !isAplicationEditable"
                                :placeholder="textsStore.getText('conclusions_section.aplication_placeholder')"
                                rows="4"
                                class="custom-textarea"
                                @input="handleTextInput('aplication', $event)"
                                @paste="handleTextPaste('aplication', $event)"
                                @keyup="handleTextKeyup('aplication', $event)">
                            </textarea>
                        </div>
                    </div>
                    <div class="row-actions">
                        <edit-button-component
                            :is-editing="isAplicationEditing"
                            :is-locked="!isAplicationEditable"
                            :edit-text="textsStore.getText('conclusions_section.table.edit')"
                            :save-text="textsStore.getText('conclusions_section.table.save')"
                            :locked-text="textsStore.getText('conclusions_section.table.locked')"
                            @click="handleAplicationEditSave"
                        />
                    </div>
                </div>
            </div>
            </div>
        </div>
        <!-- Botón Cerrar -->
    </div>
    <!-- Botón cerrar/regresar en la esquina inferior derecha -->
    <div class="cerrar-container">
      <button
        class="cerrar-btn"
        v-if="!cerrado"
        @click="confirmarCerrar"
      >{{ textsStore.getText('conclusions_section.close_button') }}</button>
      <button
        class="cerrar-btn"
        v-else-if="state !== null && state === '0'"
        @click="mostrarModalRegresar = true"
      >{{ textsStore.getText('conclusions_section.return_button') }}</button>
    </div>
    <!-- Modal de confirmación -->
    <div v-if="mostrarModal" class="modal-confirm">
      <div class="modal-content">
        <p>{{ textsStore.getText('conclusions_section.close_confirm_message') }}</p>
        <button @click="cerrarModulo">{{ textsStore.getText('conclusions_section.confirm_yes') }}</button>
        <button @click="mostrarModal = false">{{ textsStore.getText('conclusions_section.confirm_no') }}</button>
      </div>
    </div>
    <!-- Modal de confirmación para regresar -->
    <div v-if="mostrarModalRegresar" class="modal-confirm">
      <div class="modal-content">
        <p>{{ textsStore.getText('conclusions_section.return_confirm_message') }}</p>
        <button @click="regresarModulo">{{ textsStore.getText('conclusions_section.confirm_yes_return') }}</button>
        <button @click="mostrarModalRegresar = false">{{ textsStore.getText('conclusions_section.confirm_no') }}</button>
      </div>
    </div>
</template>

<script>

import { useSectionStore } from '../../../../stores/section';
import { useTextsStore } from '../../../../stores/texts';
import { useConclusionsStore } from '../../../../stores/conclusions';
import { useSessionStore } from '../../../../stores/session';
import InfoBannerComponent from '../../ui/InfoBannerComponent.vue';
import EditButtonComponent from '../../ui/EditButtonComponent.vue';
import axios from 'axios';

export default {

    setup() {
        const sectionStore = useSectionStore();
        const textsStore = useTextsStore();
        const conclusionsStore = useConclusionsStore();
        const storeSession = useSessionStore();

        const MAX_CHARACTERS = 2000;

        const handleTextInput = (field, event = null) => {
            const text = event ? event.target.value : conclusionsStore.conclusions[field];
            if (text.length > MAX_CHARACTERS) {
                
                conclusionsStore.conclusions[field] = text.substring(0, MAX_CHARACTERS);
                console.log(`Límite de ${MAX_CHARACTERS} caracteres alcanzado`);
            }
        };

        const handleTextPaste = (field, event) => {
            const pastedText = (event.clipboardData || window.clipboardData).getData('text');
            const currentText = conclusionsStore.conclusions[field] || '';
            const combinedText = currentText + pastedText;
            
            if (combinedText.length <= MAX_CHARACTERS) {
                
                return;
            } else {
                
                event.preventDefault();
                
                const availableSpace = MAX_CHARACTERS - currentText.length;
                if (availableSpace > 0) {
                    const truncatedPastedText = pastedText.substring(0, availableSpace);
                    conclusionsStore.conclusions[field] = currentText + truncatedPastedText;
                    console.log(`Texto pegado truncado. Límite de ${MAX_CHARACTERS} caracteres alcanzado`);
                } else {
                    console.log(`No se puede pegar más texto. Límite de ${MAX_CHARACTERS} caracteres alcanzado`);
                }
            }
        };

        const handleTextKeyup = (field, event) => {
            const text = event.target.value;
            if (text.length >= MAX_CHARACTERS) {
                
                if (event.key !== 'Backspace' && event.key !== 'Delete' && event.key !== 'Tab') {
                    event.preventDefault();
                    console.log(`Límite de ${MAX_CHARACTERS} caracteres alcanzado`);
                }
            }
        };
        
        return { 
            sectionStore,
            textsStore,
            conclusionsStore,
            storeSession,
            handleTextInput,
            handleTextPaste,
            handleTextKeyup
        };
    },

    components: {
        InfoBannerComponent,
        EditButtonComponent
    },

    data() {
        return {
            
            isComponentPracticeEditing: false,
            isActualityEditing: false,
            isAplicationEditing: false,
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
            ],
            mostrarModal: false,
            mostrarModalRegresar: false,
            cerrado: false,
            forceRerender: 0,
            state: null, 
        };
    },

    computed: {
        conclusionsData() {
            return this.conclusionsStore.conclusions;
        },
        isLoading() {
            return this.conclusionsStore.isLoading;
        },
        isComponentPracticeEditable() {
            return this.conclusionsData.component_practice_edits < 3 && this.conclusionsData.state !== '1';
        },
        isActualityEditable() {
            return this.conclusionsData.actuality_edits < 3 && this.conclusionsData.state !== '1';
        },
        isAplicationEditable() {
            return this.conclusionsData.aplication_edits < 3 && this.conclusionsData.state !== '1';
        }
    },

    async mounted() {
        console.log('🔍 Debug: textsStore state:', this.textsStore);
        console.log('🔍 Debug: conclusions_section.title:', this.textsStore.getText('conclusions_section.title'));
        console.log('🔍 Debug: conclusions_section.component_practice_subtitle:', this.textsStore.getText('conclusions_section.component_practice_subtitle'));
        
        // Esperar a que los textos estén cargados
        if (this.textsStore.isLoading || !this.textsStore.conclusions_section) {
            console.log('🔍 Debug: Esperando a que los textos se carguen...');
            await this.waitForTexts();
        }
        
        this.sectionStore.setTitleSection(this.textsStore.getText('conclusions_section.title'));
        this.loadConclusions().then(() => {
            this.forceRerender++;
        });
        
        this.loadStateValue();
        
        if (typeof window !== 'undefined') {
            const user = JSON.parse(localStorage.getItem('user')) || {};
            const cerradoKey = 'conclusions_cerrado_' + (user.id || 'anon');
            this.cerrado = localStorage.getItem(cerradoKey) === 'true';
        }
    },

    beforeUnmount() {
        
        this.sectionStore.clearDynamicButtons();
    },

    watch: {
        'conclusionsData.state'(newState) {
            if (newState === '0') {
                this.conclusionsStore.resetEditCounts();
            }
        }
    },

    methods: {
        async loadConclusions() {
            try {
                await this.conclusionsStore.fetchConclusions();
            } catch (error) {
                this.$buefy.toast.open({
                    message: this.textsStore.getText('conclusions_section.messages.load_error'),
                    type: 'is-danger'
                });
            }
        },

        async waitForTexts() {
            return new Promise((resolve) => {
                const checkTexts = () => {
                    if (!this.textsStore.isLoading && this.textsStore.conclusions_section) {
                        console.log('🔍 Debug: Textos cargados correctamente');
                        resolve();
                    } else {
                        console.log('🔍 Debug: Textos aún no cargados, esperando...');
                        setTimeout(checkTexts, 100);
                    }
                };
                checkTexts();
            });
        },

        handleComponentPracticeChange() {
            
        },

        handleActualityChange() {
            
        },

        handleAplicationChange() {
            
        },

        async handleComponentPracticeEditSave() {
            if (!this.isComponentPracticeEditable) return;

            if (this.isComponentPracticeEditing) {
                
                await this.saveField('component_practice', this.conclusionsData.component_practice);
                this.isComponentPracticeEditing = false;
            } else {
                
                this.isComponentPracticeEditing = true;
            }
        },

        async handleActualityEditSave() {
            if (!this.isActualityEditable) return;

            if (this.isActualityEditing) {
                
                await this.saveField('actuality', this.conclusionsData.actuality);
                this.isActualityEditing = false;
            } else {
                
                this.isActualityEditing = true;
            }
        },

        async handleAplicationEditSave() {
            if (!this.isAplicationEditable) return;

            if (this.isAplicationEditing) {
                
                await this.saveField('aplication', this.conclusionsData.aplication);
                this.isAplicationEditing = false;
            } else {
                
                this.isAplicationEditing = true;
            }
        },

        async saveField(field, value) {
            try {
                const id = this.conclusionsData.id;
                await axios.put(`/conclusions/${id}/field`, { field, value });
                await this.loadConclusions();
                this.$buefy.toast.open({
                    message: this.textsStore.getText('conclusions_section.messages.save_success'),
                    type: 'is-success'
                });
            } catch (error) {
                this.$buefy.toast.open({
                    message: this.textsStore.getText('conclusions_section.messages.save_error'),
                    type: 'is-danger'
                });
            }
        },

        checkIfAllBlocked() {
            const allBlocked = this.editCounts.component_practice >= 3 && 
                              this.editCounts.actuality >= 3 && 
                              this.editCounts.aplication >= 3;
            
            if (allBlocked) {
                
                this.conclusionsStore.updateState('1');
            }
        },

        async saveConclusions() {
            try {
                await this.conclusionsStore.updateConclusions(this.conclusionsData);
                
                this.$buefy.toast.open({
                    message: this.textsStore.getText('conclusions_section.messages.save_success'),
                    type: 'is-success'
                });

                this.conclusionsStore.checkIfAllBlocked();
            } catch (error) {
                this.$buefy.toast.open({
                    message: this.textsStore.getText('conclusions_section.messages.save_error'),
                    type: 'is-danger'
                });
            }
        },
        async handleCloseConclusions() {
            try {
                await this.conclusionsStore.closeAllConclusions();
                this.$buefy.toast.open({
                    message: this.textsStore.getText('conclusions_section.messages.close_success') || 'Conclusiones cerradas correctamente',
                    type: 'is-success'
                });
                this.sectionStore.setSection('main');
            } catch (error) {
                this.$buefy.toast.open({
                    message: this.textsStore.getText('conclusions_section.messages.close_error') || 'Error al cerrar las conclusiones',
                    type: 'is-danger'
                });
            }
        },
        confirmarCerrar() {
            this.mostrarModal = true;
        },
        async cerrarModulo() {
            this.mostrarModal = false;
            
            try {
                await this.conclusionsStore.closeAllConclusions();
                
                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'cerrar', modulo: 'conclusions' }));
                
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const cerradoKey = 'conclusions_cerrado_' + (user.id || 'anon');
                localStorage.setItem(cerradoKey, 'true');
                this.$buefy.toast.open({
                    message: 'Módulo de conclusiones cerrado correctamente',
                    type: 'is-success'
                });
                this.storeSession.setActiveContent('main');
                
                setTimeout(async () => {
                    const { useTraceabilityStore } = await import('../../../../stores/traceability');
                    const traceabilityStore = useTraceabilityStore();
                    await traceabilityStore.markSectionCompleted('conclusions');
                }, 1000);
                
                this.cerrado = true;
            } catch (error) {
                this.$buefy.toast.open({
                    message: 'Error al cerrar el módulo de conclusiones',
                    type: 'is-danger'
                });
            }
        },
        async loadStateValue() {
            try {
                const response = await axios.get('/traceability/current-route-state');
                if (response.data && response.data.success && response.data.state !== undefined) {
                    this.state = response.data.state;
                }
            } catch (error) {
                console.error('Error al cargar state:', error);
            }
        },

        async incrementState() {
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
                
                await this.incrementState();
                
                await this.loadStateValue();
                
                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'regresar', modulo: 'conclusions' }));
                
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const posteriores = [
                  'results' 
                ];
                posteriores.forEach(mod => {
                  const cerradoKey = mod + '_cerrado_' + (user.id || 'anon');
                  localStorage.removeItem(cerradoKey);
                });
                
                this.storeSession.setActiveContent('main');
                
                const cerradoKey = 'conclusions_cerrado_' + (user.id || 'anon');
                localStorage.removeItem(cerradoKey);
                this.cerrado = false;
                
                await this.conclusionsStore.fetchConclusions();
                
                this.$buefy.toast.open({
                    message: 'Módulo de conclusiones reabierto correctamente',
                    type: 'is-success'
                });
            } catch (error) {
                this.$buefy.toast.open({
                    message: 'Error al regresar el módulo de conclusiones',
                    type: 'is-danger'
                });
            }
        }
    }
};
</script>

<style scoped>
.conclusions-container {
    padding: 20px;
}

.subtitle-text {
    text-align: justify !important;
    line-height: 1.5;
}

.conclusions-table {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(80, 80, 120, 0.10);
    overflow: hidden;
    font-family: 'Montserrat', Arial, sans-serif;
    margin: 0 auto;
    max-width: 1400px;
}

.table-content {
    padding: 0;
}

.table-row {
    display: flex;
    align-items: center;
    padding: 24px 28px;
    border-bottom: 1px solid #e0e7ff;
    background: #fff;
    min-height: 80px;
    transition: background 0.2s;
}

.table-row:nth-child(even) {
    background: #f8f7fa;
}

.table-row:last-child {
    border-bottom: none;
}

.title-row, .subtitle-row {
    background: #eef2ff !important;
}

.title-row h3 {
    color: #4f46e5;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 0;
    text-align: center;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.subtitle-row h4 {
    color: #7c5cbf;
    font-weight: 600;
    margin-bottom: 0;
    text-align: left;
    font-size: 1.1rem;
}

.textarea-row {
    background: #fff;
}

.row-content {
    flex: 1 1 auto;
    min-width: 0;
    padding-right: 20px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

.textarea-row .row-content {
    flex: 1 1 auto;
    min-width: 0;
    padding-right: 0;
    display: flex;
    align-items: center;
    justify-content: flex-start;
}

:deep(.textarea-row .b-input textarea) {
    width: 100% !important;
    min-width: unset !important;
    max-width: unset !important;
    box-sizing: border-box !important;
    display: block;
}
.b-input[type="textarea"]:focus {
    box-shadow: 0 0 0 2px #a5b4fc !important;
    outline: none;
}
.b-input[type="textarea"]:disabled {
    background: #f3f3f3 !important;
    color: #b0b3c6 !important;
}

.b-button {
    font-family: 'Montserrat', Arial, sans-serif;
    font-weight: 600;
    border-radius: 6px;
    min-width: 100px;
    transition: box-shadow 0.2s;
}
.b-button.is-info {
    background: #4f46e5;
    color: #fff;
    border: none;
}
.b-button.is-success {
    background: #48c774;
    color: #fff;
    border: none;
}
.b-button[disabled], .b-button.is-disabled {
    background: #e5e7eb !important;
    color: #b0b3c6 !important;
    border: none !important;
}

.tag {
    font-size: 0.85rem;
    border-radius: 6px;
    padding: 4px 10px;
    margin-top: 6px;
    background: #fde8e8;
    color: #d7263d;
    font-weight: 600;
}

.textarea-row .textarea-flex {
    flex: 1 1 100%;
    display: flex;
    width: 100%;
}
.textarea-row .textarea-flex .b-input {
    width: 100% !important;
}
.b-input[type="textarea"] {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    resize: vertical;
}
:deep(.b-input.textarea .control textarea) {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    resize: vertical !important;
    height: auto !important;
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

@media (max-width: 700px) {
    .conclusions-table {
        max-width: 100%;
        border-radius: 0;
        box-shadow: none;
    }
    .table-row {
        flex-direction: column;
        align-items: stretch;
        padding: 16px 8px;
    }
    .row-content {
        padding-right: 0;
        min-width: 0;
    }
    .b-input[type="textarea"] {
        min-width: 100px;
        max-width: 100%;
    }
    .row-actions {
        flex: 1 1 100%;
        align-items: stretch;
        margin-top: 10px;
    }
}
</style>

<style>
.b-input.textarea {
    width: 100% !important;
}
.b-input.textarea .control {
    width: 100% !important;
}
.b-input.textarea .control textarea {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    height: auto !important;
    resize: vertical !important;
}
.textarea-row .textarea-flex {
    flex: 1 1 100%;
    display: flex;
    width: 100%;
}
.row-content {
    flex: 1 1 auto;
    min-width: 0;
    padding-right: 0;
}
.custom-textarea {
    width: 99%;
    padding: 14px 18px;
    font-family: 'Montserrat', Arial, sans-serif;
    font-size: 15px;
    resize: vertical;
    border-radius: 8px;
    border: 1px solid #ccc;
}
</style>