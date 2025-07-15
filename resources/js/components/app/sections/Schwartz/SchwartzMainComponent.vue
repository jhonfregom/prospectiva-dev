<template>
    <div class="schwartz-container" :style="{ justifyContent: 'center', alignItems: 'center', display: 'flex' }">
        <div class="schwartz-matrix-container" :style="{ width: schwartzSize, margin: '0 auto', display: 'flex', justifyContent: 'center', alignItems: 'center' }">
            <div class="schwartz-matrix">
                <!-- Ejes rojos -->
                <div class="schwartz-axis schwartz-axis-x"></div>
                <div class="schwartz-axis schwartz-axis-y"></div>
                <!-- Fila 1 -->
                <div class="cell empty"></div>
                <div class="cell empty"></div>
                <div class="cell hypo top">
                    <div class="cell-title">{{ textsStore.getText('schwartz.hypothesis.h1_plus') }}</div>
                    <div class="cell-content" :style="readonly ? { maxHeight: 'none', overflow: 'visible', whiteSpace: 'pre-line' } : {}">{{ h1H1 }}</div>
                </div>
                <div class="cell empty"></div>
                <div class="cell empty"></div>

                <!-- Fila 2 -->
                <div class="cell empty"></div>
                <div class="cell scenario">
                    <div class="scenario-title">{{ textsStore.getText('schwartz.scenarios.scenario_4') }}</div>
                    <div v-if="readonly">
                        <div class="cell-content" :style="{ maxHeight: 'none', overflow: 'visible', whiteSpace: 'pre-line' }">{{ escenarios[3]?.texto || escenarios[3]?.titulo }}</div>
                    </div>
                    <div v-else>
                        <b-input type="textarea"
                            v-model="escenarios[3].texto"
                            :disabled="!editingScenario[3] || schwartzStore.isScenarioBlocked(3)"
                            class="scenario-input"
                            @input="handleTextInput(3, $event)"
                            @paste="handleTextPaste(3, $event)"
                            @keyup="handleTextKeyup(3, $event)" />
                        <div class="edit-btn-container">
                            <b-button
                                type="is-info"
                                size="is-small"
                                icon-left="edit"
                                @click="handleEditSave(3, 4)"
                                outlined
                                :disabled="schwartzStore.isEditLocked(3)"
                            >
                                {{ editingScenario[3] ? textsStore.getText('schwartz.actions.save') : textsStore.getText('schwartz.actions.edit') }}
                            </b-button>
                            <div v-if="editMessage[3]" class="edit-limit-message">{{ editMessage[3] }}</div>
                            <span v-if="schwartzStore.isScenarioBlocked(3)" class="tag is-warning ml-2">{{ textsStore.getText('schwartz.table.locked') }}</span>
                        </div>
                    </div>
                </div>
                <div class="cell empty"></div>
                <div class="cell scenario">
                    <div class="scenario-title">{{ textsStore.getText('schwartz.scenarios.scenario_1') }}</div>
                    <div v-if="readonly">
                        <div class="cell-content" :style="{ maxHeight: 'none', overflow: 'visible', whiteSpace: 'pre-line' }">{{ escenarios[0]?.texto || escenarios[0]?.titulo }}</div>
                    </div>
                    <div v-else>
                        <b-input type="textarea"
                            v-model="escenarios[0].texto"
                            :disabled="!editingScenario[0] || escenarios[0].state === 1"
                            class="scenario-input"
                            @input="handleTextInput(0, $event)"
                            @paste="handleTextPaste(0, $event)"
                            @keyup="handleTextKeyup(0, $event)" />
                        <div class="edit-btn-container">
                            <b-button
                                type="is-info"
                                size="is-small"
                                icon-left="edit"
                                @click="handleEditSave(0, 1)"
                                outlined
                                :disabled="schwartzStore.isEditLocked(0)"
                            >
                                {{ editingScenario[0] ? textsStore.getText('schwartz.actions.save') : textsStore.getText('schwartz.actions.edit') }}
                            </b-button>
                            <div v-if="editMessage[0]" class="edit-limit-message">{{ editMessage[0] }}</div>
                        </div>
                    </div>
                </div>
                <div class="cell empty"></div>

                <!-- Fila 3 -->
                <div class="cell hypo left">
                    <div class="cell-title">{{ textsStore.getText('schwartz.hypothesis.h2_minus') }}</div>
                    <div class="cell-content" :style="readonly ? { maxHeight: 'none', overflow: 'visible', whiteSpace: 'pre-line' } : {}">{{ h2H0 }}</div>
                </div>
                <div class="cell empty"></div>
                <div class="cell empty"></div>
                <div class="cell empty"></div>
                <div class="cell hypo right">
                    <div class="cell-title">{{ textsStore.getText('schwartz.hypothesis.h2_plus') }}</div>
                    <div class="cell-content" :style="readonly ? { maxHeight: 'none', overflow: 'visible', whiteSpace: 'pre-line' } : {}">{{ h2H1 }}</div>
                </div>

                <!-- Fila 4 -->
                <div class="cell empty"></div>
                <div class="cell scenario">
                    <div class="scenario-title">{{ textsStore.getText('schwartz.scenarios.scenario_3') }}</div>
                    <div v-if="readonly">
                        <div class="cell-content" :style="{ maxHeight: 'none', overflow: 'visible', whiteSpace: 'pre-line' }">{{ escenarios[2]?.texto || escenarios[2]?.titulo }}</div>
                    </div>
                    <div v-else>
                        <b-input type="textarea"
                            v-model="escenarios[2].texto"
                            :disabled="!editingScenario[2] || escenarios[2].state === 1"
                            class="scenario-input"
                            @input="handleTextInput(2, $event)"
                            @paste="handleTextPaste(2, $event)"
                            @keyup="handleTextKeyup(2, $event)" />
                        <div class="edit-btn-container">
                            <b-button
                                type="is-info"
                                size="is-small"
                                icon-left="edit"
                                @click="handleEditSave(2, 3)"
                                outlined
                                :disabled="schwartzStore.isEditLocked(2)"
                            >
                                {{ editingScenario[2] ? textsStore.getText('schwartz.actions.save') : textsStore.getText('schwartz.actions.edit') }}
                            </b-button>
                            <div v-if="editMessage[2]" class="edit-limit-message">{{ editMessage[2] }}</div>
                        </div>
                    </div>
                </div>
                <div class="cell empty"></div>
                <div class="cell scenario">
                    <div class="scenario-title">{{ textsStore.getText('schwartz.scenarios.scenario_2') }}</div>
                    <div v-if="readonly">
                        <div class="cell-content" :style="{ maxHeight: 'none', overflow: 'visible', whiteSpace: 'pre-line' }">{{ escenarios[1]?.texto || escenarios[1]?.titulo }}</div>
                    </div>
                    <div v-else>
                        <b-input type="textarea"
                            v-model="escenarios[1].texto"
                            :disabled="!editingScenario[1] || escenarios[1].state === 1"
                            class="scenario-input"
                            @input="handleTextInput(1, $event)"
                            @paste="handleTextPaste(1, $event)"
                            @keyup="handleTextKeyup(1, $event)" />
                        <div class="edit-btn-container">
                            <b-button
                                type="is-info"
                                size="is-small"
                                icon-left="edit"
                                @click="handleEditSave(1, 2)"
                                outlined
                                :disabled="schwartzStore.isEditLocked(1)"
                            >
                                {{ editingScenario[1] ? textsStore.getText('schwartz.actions.save') : textsStore.getText('schwartz.actions.edit') }}
                            </b-button>
                            <div v-if="editMessage[1]" class="edit-limit-message">{{ editMessage[1] }}</div>
                        </div>
                    </div>
                </div>
                <div class="cell empty"></div>

                <!-- Fila 5 -->
                <div class="cell empty"></div>
                <div class="cell empty"></div>
                <div class="cell hypo bottom">
                    <div class="cell-title">{{ textsStore.getText('schwartz.hypothesis.h1_minus') }}</div>
                    <div class="cell-content" :style="readonly ? { maxHeight: 'none', overflow: 'visible', whiteSpace: 'pre-line' } : {}">{{ h1H0 }}</div>
                </div>
                <div class="cell empty"></div>
                <div class="cell empty"></div>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, computed, ref } from 'vue';
import { useSectionStore } from '../../../../stores/section';
import { useFutureDriversStore } from '../../../../stores/futureDrivers';
import { useSchwartzStore } from '../../../../stores/schwartz';
import { useTextsStore } from '../../../../stores/texts';
import MiniStepper from '../../ui/MiniStepper.vue';

export default {
    name: 'SchwartzMainComponent',
    props: {
        readonly: {
            type: Boolean,
            default: false
        },
        pdfMode: {
            type: Boolean,
            default: false
        },
        size: {
            type: [String, Number],
            default: 'large'
        },
        externalScenarios: {
            type: Array,
            default: null
        },
        externalHypotheses: {
            type: Array,
            default: null
        }
    },
    components: {
        MiniStepper
    },
    setup(props) {
        const sectionStore = useSectionStore();
        const futureDriversStore = useFutureDriversStore();
        const schwartzStore = useSchwartzStore();
        const textsStore = useTextsStore();

        // Constante para el límite de caracteres
        const MAX_CHARACTERS = 255;

        onMounted(async () => {
            // Solo cambiar el título si no está en modo readonly (modal)
            if (!props.readonly) {
                sectionStore.setTitleSection(textsStore.getText('schwartz.title'));
            }
            if (futureDriversStore.drivers.length === 0) {
                await futureDriversStore.fetchDrivers();
            }
            // Cargar escenarios guardados
            await schwartzStore.fetchScenarios();
        });

        // Computed para obtener los textos de hipótesis
        const h1H0 = computed(() => {
            if (props.externalHypotheses && props.externalHypotheses.length > 0) {
                return props.externalHypotheses[0]?.descriptionH0 || '';
            }
            return futureDriversStore.drivers[0]?.descriptionH0 || '';
        });
        const h1H1 = computed(() => {
            if (props.externalHypotheses && props.externalHypotheses.length > 0) {
                return props.externalHypotheses[0]?.descriptionH1 || '';
            }
            return futureDriversStore.drivers[0]?.descriptionH1 || '';
        });
        const h2H0 = computed(() => {
            if (props.externalHypotheses && props.externalHypotheses.length > 1) {
                return props.externalHypotheses[1]?.descriptionH0 || '';
            }
            return futureDriversStore.drivers[1]?.descriptionH0 || '';
        });
        const h2H1 = computed(() => {
            if (props.externalHypotheses && props.externalHypotheses.length > 1) {
                return props.externalHypotheses[1]?.descriptionH1 || '';
            }
            return futureDriversStore.drivers[1]?.descriptionH1 || '';
        });

        // Computed para escenarios
        const escenarios = computed(() => {
            if (props.externalScenarios && Array.isArray(props.externalScenarios) && props.externalScenarios.length > 0) {
                return props.externalScenarios;
            }
            return schwartzStore.escenarios;
        });
        const setEscenario = (index, texto) => schwartzStore.setEscenario(index, texto);

        // Función para manejar input de texto
        const handleTextInput = (index, event) => {
            const text = event.target.value;
            if (text.length > MAX_CHARACTERS) {
                schwartzStore.setEscenario(index, text.substring(0, MAX_CHARACTERS));
                editMessage.value[index] = `Límite de ${MAX_CHARACTERS} caracteres alcanzado`;
                setTimeout(() => {
                    editMessage.value[index] = '';
                }, 2000);
            } else {
                schwartzStore.setEscenario(index, text);
                editMessage.value[index] = '';
            }
        };

        // Función para manejar pegado de texto
        const handleTextPaste = (index, event) => {
            const pastedText = (event.clipboardData || window.clipboardData).getData('text');
            const currentText = schwartzStore.escenarios[index].texto;
            const combinedText = currentText + pastedText;
            if (combinedText.length <= MAX_CHARACTERS) {
                return;
            }
            event.preventDefault();
            const availableSpace = MAX_CHARACTERS - currentText.length;
            if (availableSpace > 0) {
                const truncatedPastedText = pastedText.substring(0, availableSpace);
                schwartzStore.setEscenario(index, currentText + truncatedPastedText);
                editMessage.value[index] = `Texto pegado truncado. Límite de ${MAX_CHARACTERS} caracteres alcanzado`;
                setTimeout(() => {
                    editMessage.value[index] = '';
                }, 2000);
            } else {
                editMessage.value[index] = `No se puede pegar más texto. Límite de ${MAX_CHARACTERS} caracteres alcanzado`;
                setTimeout(() => {
                    editMessage.value[index] = '';
                }, 2000);
            }
        };

        // Función para manejar keyup (prevenir escritura adicional)
        const handleTextKeyup = (index, event) => {
            const text = event.target.value;
            if (text.length >= MAX_CHARACTERS) {
                if (event.key !== 'Backspace' && event.key !== 'Delete' && event.key !== 'Tab') {
                    event.preventDefault();
                    editMessage.value[index] = `Límite de ${MAX_CHARACTERS} caracteres alcanzado`;
                    setTimeout(() => {
                        editMessage.value[index] = '';
                    }, 2000);
                }
            }
        };

        // Nueva función para manejar edición/guardado
        const handleEditSave = async (index, numScenario) => {
            const escenario = schwartzStore.escenarios[index];
            if (schwartzStore.isEditLocked(index)) return;
            if (editingScenario.value[index]) {
                schwartzStore.incrementEdit(index);
                editingScenario.value[index] = false;
                const result = await schwartzStore.saveScenario(index, numScenario);
                if (!result.success) {
                    editMessage.value[index] = textsStore.getText('schwartz.messages.save_error') + (result.message || textsStore.getText('schwartz.messages.try_again'));
                } else if (schwartzStore.escenarios[index].state === 1) {
                    editMessage.value[index] = textsStore.getText('schwartz.messages.edit_limit_reached');
                } else {
                    editMessage.value[index] = '';
                }
            } else {
                editingScenario.value[index] = true;
            }
        };

        const editingScenario = ref([false, false, false, false]);
        const editMessage = ref(['', '', '', '']);

        // Para tamaño visualización
        const schwartzSize = computed(() => {
            if (props.pdfMode) return '900px';
            if (props.size === 'large') return '1200px';
            if (props.size === 'medium') return '900px';
            if (props.size === 'small') return '600px';
            if (typeof props.size === 'number') return props.size + 'px';
            if (typeof props.size === 'string' && props.size.endsWith('px')) return props.size;
            return '100%';
        });

        return {
            h1H0,
            h1H1,
            h2H0,
            h2H1,
            escenarios,
            setEscenario,
            textsStore,
            editingScenario,
            handleEditSave,
            editMessage,
            schwartzStore,
            handleTextInput,
            handleTextPaste,
            handleTextKeyup,
            schwartzSize,
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
    }
};
</script>



<style scoped>
.schwartz-matrix-container {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
    min-height: 600px;
    margin-top: 32px;
}
.schwartz-matrix.pdf-mode {
    grid-template-columns: repeat(5, minmax(220px, 1fr));
    grid-template-rows: repeat(5, minmax(100px, 1fr));
}
.schwartz-matrix {
    display: grid;
    grid-template-columns: repeat(5, minmax(160px, 1fr));
    grid-template-rows: repeat(5, minmax(80px, 1fr));
    gap: 0px;
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
}
.cell {
    background: #fff;
    border-radius: 8px;
    border: 1px solid #E0E7FF;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 12px 16px;
    font-size: 14px;
    min-height: 60px;
    min-width: 120px;
    width: 100%;
    height: 100%;
    transition: all 0.2s ease;
    box-sizing: border-box;
    word-break: break-word;
    white-space: normal;
}
.pdf-mode .cell {
    padding: 16px 18px;
    font-size: 15px;
    min-height: 80px;
    min-width: 180px;
    word-break: break-word;
    white-space: normal;
}
.cell-title {
    font-weight: 600;
    color: #4F46E5;
    font-size: 13px;
    margin-bottom: 8px;
    text-align: center;
    background: #EEF2FF;
    width: 100%;
    border-bottom: 2px solid #E0E7FF;
    padding: 8px 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: 6px 6px 0 0;
    box-sizing: border-box;
}
.cell-content {
    color: #1F2937;
    font-size: 14px;
    font-weight: 500;
    word-break: break-word;
    text-align: center;
    width: 100%;
    margin-top: 8px;
    line-height: 1.5;
    box-sizing: border-box;
    white-space: pre-line;
    max-height: none;
    overflow: visible;
}
.cell.hypo {
    background: #FAFAFA;
    border: 1px solid #E0E7FF;
    min-height: 120px;
    max-height: 300px;
    box-shadow: 0 3px 12px rgba(79, 70, 229, 0.08);
}
.cell.scenario {
    background: #fff;
    border: 1px solid #E0E7FF;
    min-width: 120px;
    min-height: 80px;
    align-items: stretch;
    justify-content: flex-start;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.scenario-title {
    font-weight: 600;
    color: #4F46E5;
    margin-bottom: 6px;
    text-align: center;
    background: #EEF2FF;
    border-bottom: 2px solid #E0E7FF;
    padding: 6px 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 12px;
    border-radius: 4px 4px 0 0;
}
.scenario-input {
    width: 100%;
    min-height: 52px;
    font-size: 14px;
    margin-top: 6px;
    resize: vertical;
    max-height: 120px;
    overflow-y: auto;
    border: 1px solid #E0E7FF;
    border-radius: 4px;
    padding: 8px;
    background: #fff;
    color: #1F2937;
    font-weight: 500;
}
.cell.empty {
    background: transparent;
    border: none;
    box-shadow: none;
}
.schwartz-axis {
    position: absolute;
    background: red;
    z-index: 1;
}
.schwartz-axis-x {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 61%;
    height: 4px;
    background: red;
    transform: translate(-50%, -50%);
}
.schwartz-axis-x::before,
.schwartz-axis-x::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 0;
    height: 0;
    border-top: 18px solid transparent;
    border-bottom: 18px solid transparent;
}
.schwartz-axis-x::before {
    left: 0;
    border-right: 28px solid red;
    transform: translateY(-50%);
}
.schwartz-axis-x::after {
    right: 0;
    border-left: 28px solid red;
    transform: translateY(-50%);
}
.schwartz-axis-y {
    position: absolute;
    left: 50%;
    top: 50%;
    width: 4px;
    height: 65%; /* antes 61% */
    background: red;
    transform: translate(-50%, -50%);
}
.schwartz-axis-y::before,
.schwartz-axis-y::after {
    content: '';
    position: absolute;
    left: 50%;
    width: 0;
    height: 0;
    border-left: 18px solid transparent;
    border-right: 18px solid transparent;
}
.schwartz-axis-y::before {
    top: 0;
    border-bottom: 28px solid red;
    transform: translateX(-50%);
}
.schwartz-axis-y::after {
    bottom: 0;
    border-top: 28px solid red;
    transform: translateX(-50%);
}
.cell.hypo.right {
    background: #FAFAFA;
    border: 1px solid #E0E7FF;
    position: relative;
    left: 0;
    z-index: 2;
    top: 0;
    box-shadow: 0 3px 12px rgba(79, 70, 229, 0.08);
}
.cell.hypo.left {
    background: #FAFAFA;
    border: 1px solid #E0E7FF;
    position: relative;
    left: 0;
    z-index: 2;
    top: 0;
    box-shadow: 0 3px 12px rgba(79, 70, 229, 0.08);
}
.cell.hypo.top {
    background: #FAFAFA;
    border: 1px solid #E0E7FF;
    position: relative;
    top: 0;
    z-index: 2;
    left: 0;
    box-shadow: 0 3px 12px rgba(79, 70, 229, 0.08);
}
.cell.hypo.bottom {
    background: #FAFAFA;
    border: 1px solid #E0E7FF;
    position: relative;
    top: 0;
    z-index: 2;
    left: 0;
    box-shadow: 0 3px 12px rgba(79, 70, 229, 0.08);
}
.cell.hypo .cell-content {
    text-align: justify;
}
.edit-btn-container {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    margin-top: 8px;
    width: 100%;
}
.edit-btn-container .b-button {
    min-width: 80px;
    max-width: 100px;
    width: auto;
    font-size: 12px;
    padding: 2px 10px;
    margin-bottom: 2px;
}
.edit-limit-message {
    color: #e53e3e;
    font-size: 13px;
    margin-top: 4px;
    text-align: right;
    width: 100%;
}
</style>