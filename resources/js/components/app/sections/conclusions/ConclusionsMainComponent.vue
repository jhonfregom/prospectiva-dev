<template>
    <div class="conclusions-container">
        <MiniStepper :steps="steps" :currentIndex="8" />
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
                        <h4 class="title is-5">{{ textsStore.getText('conclusions_section.component_practice_subtitle') }}</h4>
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
                        <b-button 
                            :type="isComponentPracticeEditing ? 'is-success' : 'is-info'"
                            size="is-small"
                            :icon-left="isComponentPracticeEditing ? 'save' : 'edit'"
                            @click="handleComponentPracticeEditSave"
                            outlined
                            :disabled="!isComponentPracticeEditable">
                            {{ isComponentPracticeEditing ? textsStore.getText('conclusions_section.table.save') : textsStore.getText('conclusions_section.table.edit') }}
                        </b-button>
                        <span v-if="!isComponentPracticeEditable" class="tag is-danger is-light">
                            {{ textsStore.getText('conclusions_section.table.locked') }}
                        </span>
                    </div>
                </div>

                <!-- Cuarta fila: Subtítulo Actualidad -->
                <div class="table-row subtitle-row">
                    <div class="row-content">
                        <h4 class="title is-5">{{ textsStore.getText('conclusions_section.actuality_subtitle') }}</h4>
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
                        <b-button 
                            :type="isActualityEditing ? 'is-success' : 'is-info'"
                            size="is-small"
                            :icon-left="isActualityEditing ? 'save' : 'edit'"
                            @click="handleActualityEditSave"
                            outlined
                            :disabled="!isActualityEditable">
                            {{ isActualityEditing ? textsStore.getText('conclusions_section.table.save') : textsStore.getText('conclusions_section.table.edit') }}
                        </b-button>
                        <span v-if="!isActualityEditable" class="tag is-danger is-light">
                            {{ textsStore.getText('conclusions_section.table.locked') }}
                        </span>
                    </div>
                </div>

                <!-- Sexta fila: Subtítulo Aplicación -->
                <div class="table-row subtitle-row">
                    <div class="row-content">
                        <h4 class="title is-5">{{ textsStore.getText('conclusions_section.aplication_subtitle') }}</h4>
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
                        <b-button 
                            :type="isAplicationEditing ? 'is-success' : 'is-info'"
                            size="is-small"
                            :icon-left="isAplicationEditing ? 'save' : 'edit'"
                            @click="handleAplicationEditSave"
                            outlined
                            :disabled="!isAplicationEditable">
                            {{ isAplicationEditing ? textsStore.getText('conclusions_section.table.save') : textsStore.getText('conclusions_section.table.edit') }}
                        </b-button>
                        <span v-if="!isAplicationEditable" class="tag is-danger is-light">
                            {{ textsStore.getText('conclusions_section.table.locked') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import { useSectionStore } from '../../../../stores/section';
import { useTextsStore } from '../../../../stores/texts';
import { useConclusionsStore } from '../../../../stores/conclusions';
import axios from 'axios';
import MiniStepper from '../../ui/MiniStepper.vue';


export default {


    setup() {
        const sectionStore = useSectionStore();
        const textsStore = useTextsStore();
        const conclusionsStore = useConclusionsStore();
        
        // Constante para el límite de caracteres
        const MAX_CHARACTERS = 2000;

        // Función para manejar input de texto con límite de caracteres
        const handleTextInput = (field, event = null) => {
            const text = event ? event.target.value : conclusionsStore.conclusions[field];
            if (text.length > MAX_CHARACTERS) {
                // Truncar el texto al límite
                conclusionsStore.conclusions[field] = text.substring(0, MAX_CHARACTERS);
                console.log(`Límite de ${MAX_CHARACTERS} caracteres alcanzado`);
            }
        };

        // Función para manejar pegado de texto
        const handleTextPaste = (field, event) => {
            const pastedText = (event.clipboardData || window.clipboardData).getData('text');
            const currentText = conclusionsStore.conclusions[field] || '';
            const combinedText = currentText + pastedText;
            
            if (combinedText.length <= MAX_CHARACTERS) {
                // Permitir el pegado normal
                return;
            } else {
                // Prevenir el pegado por defecto y manejar manualmente
                event.preventDefault();
                // Calcular cuántos caracteres se pueden agregar
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

        // Función para manejar keyup
        const handleTextKeyup = (field, event) => {
            const text = event.target.value;
            if (text.length >= MAX_CHARACTERS) {
                // Prevenir escritura adicional
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
            handleTextInput,
            handleTextPaste,
            handleTextKeyup
        };
    },

    components: {
        MiniStepper
    },

    data() {
        return {
            // Estados de edición
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
            ]
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

    mounted() {
        this.sectionStore.setTitleSection(this.textsStore.getText('conclusions_section.title'));
        this.loadConclusions();
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

        // Handlers para cambios en los campos
        handleComponentPracticeChange() {
            // Lógica adicional si es necesaria
        },

        handleActualityChange() {
            // Lógica adicional si es necesaria
        },

        handleAplicationChange() {
            // Lógica adicional si es necesaria
        },

        async handleComponentPracticeEditSave() {
            if (!this.isComponentPracticeEditable) return;

            if (this.isComponentPracticeEditing) {
                // Guardar
                await this.saveField('component_practice', this.conclusionsData.component_practice);
                this.isComponentPracticeEditing = false;
            } else {
                // Entrar en modo edición
                this.isComponentPracticeEditing = true;
            }
        },

        async handleActualityEditSave() {
            if (!this.isActualityEditable) return;

            if (this.isActualityEditing) {
                // Guardar
                await this.saveField('actuality', this.conclusionsData.actuality);
                this.isActualityEditing = false;
            } else {
                // Entrar en modo edición
                this.isActualityEditing = true;
            }
        },

        async handleAplicationEditSave() {
            if (!this.isAplicationEditable) return;

            if (this.isAplicationEditing) {
                // Guardar
                await this.saveField('aplication', this.conclusionsData.aplication);
                this.isAplicationEditing = false;
            } else {
                // Entrar en modo edición
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
                // Actualizar el state en el backend
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

                // Verificar si todos los campos están bloqueados
                this.conclusionsStore.checkIfAllBlocked();
            } catch (error) {
                this.$buefy.toast.open({
                    message: this.textsStore.getText('conclusions_section.messages.save_error'),
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

::v-deep(.textarea-row .b-input textarea) {
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
::v-deep(.b-input.textarea .control textarea) {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 100% !important;
    resize: vertical !important;
    height: auto !important;
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