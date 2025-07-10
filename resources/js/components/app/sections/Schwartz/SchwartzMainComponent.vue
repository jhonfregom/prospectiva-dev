<template>
    <div class="schwartz-matrix-container">
        <div class="schwartz-matrix">
            <!-- Ejes rojos -->
            <div class="schwartz-axis schwartz-axis-x"></div>
            <div class="schwartz-axis schwartz-axis-y"></div>
            <!-- Fila 1 -->
            <div class="cell empty"></div>
            <div class="cell empty"></div>
            <div class="cell hypo top">
                <div class="cell-title">{{ textsStore.getText('schwartz.hypothesis.h1_plus') }}</div>
                <div class="cell-content">{{ h1H1 }}</div>
            </div>
            <div class="cell empty"></div>
            <div class="cell empty"></div>

            <!-- Fila 2 -->
            <div class="cell empty"></div>
            <div class="cell scenario">
                <div class="scenario-title">{{ textsStore.getText('schwartz.scenarios.scenario_4') }}</div>
                <b-input type="textarea"
                    v-model="escenarios[3].texto"
                    :disabled="!editingScenario[3] || escenarios[3].state === 1"
                    class="scenario-input" />
                <div class="edit-btn-container">
                    <b-button
                        type="is-info"
                        size="is-small"
                        icon-left="edit"
                        @click="handleEditSave(3, 4)"
                        outlined
                        :disabled="escenarios[3].state === 1"
                    >
                        {{ editingScenario[3] ? textsStore.getText('schwartz.actions.save') : textsStore.getText('schwartz.actions.edit') }}
                    </b-button>
                    <div v-if="editMessage[3]" class="edit-limit-message">{{ editMessage[3] }}</div>
                </div>
            </div>
            <div class="cell empty"></div>
            <div class="cell scenario">
                <div class="scenario-title">{{ textsStore.getText('schwartz.scenarios.scenario_1') }}</div>
                <b-input type="textarea"
                    v-model="escenarios[0].texto"
                    :disabled="!editingScenario[0] || escenarios[0].state === 1"
                    class="scenario-input" />
                <div class="edit-btn-container">
                    <b-button
                        type="is-info"
                        size="is-small"
                        icon-left="edit"
                        @click="handleEditSave(0, 1)"
                        outlined
                        :disabled="escenarios[0].state === 1"
                    >
                        {{ editingScenario[0] ? textsStore.getText('schwartz.actions.save') : textsStore.getText('schwartz.actions.edit') }}
                    </b-button>
                    <div v-if="editMessage[0]" class="edit-limit-message">{{ editMessage[0] }}</div>
                </div>
            </div>
            <div class="cell empty"></div>

            <!-- Fila 3 -->
            <div class="cell hypo left">
                <div class="cell-title">{{ textsStore.getText('schwartz.hypothesis.h2_minus') }}</div>
                <div class="cell-content">{{ h2H0 }}</div>
            </div>
            <div class="cell empty"></div>
            <div class="cell empty"></div>
            <div class="cell empty"></div>
            <div class="cell hypo right">
                <div class="cell-title">{{ textsStore.getText('schwartz.hypothesis.h2_plus') }}</div>
                <div class="cell-content">{{ h2H1 }}</div>
            </div>

            <!-- Fila 4 -->
            <div class="cell empty"></div>
            <div class="cell scenario">
                <div class="scenario-title">{{ textsStore.getText('schwartz.scenarios.scenario_3') }}</div>
                <b-input type="textarea"
                    v-model="escenarios[2].texto"
                    :disabled="!editingScenario[2] || escenarios[2].state === 1"
                    class="scenario-input" />
                <div class="edit-btn-container">
                    <b-button
                        type="is-info"
                        size="is-small"
                        icon-left="edit"
                        @click="handleEditSave(2, 3)"
                        outlined
                        :disabled="escenarios[2].state === 1"
                    >
                        {{ editingScenario[2] ? textsStore.getText('schwartz.actions.save') : textsStore.getText('schwartz.actions.edit') }}
                    </b-button>
                    <div v-if="editMessage[2]" class="edit-limit-message">{{ editMessage[2] }}</div>
                </div>
            </div>
            <div class="cell empty"></div>
            <div class="cell scenario">
                <div class="scenario-title">{{ textsStore.getText('schwartz.scenarios.scenario_2') }}</div>
                <b-input type="textarea"
                    v-model="escenarios[1].texto"
                    :disabled="!editingScenario[1] || escenarios[1].state === 1"
                    class="scenario-input" />
                <div class="edit-btn-container">
                    <b-button
                        type="is-info"
                        size="is-small"
                        icon-left="edit"
                        @click="handleEditSave(1, 2)"
                        outlined
                        :disabled="escenarios[1].state === 1"
                    >
                        {{ editingScenario[1] ? textsStore.getText('schwartz.actions.save') : textsStore.getText('schwartz.actions.edit') }}
                    </b-button>
                    <div v-if="editMessage[1]" class="edit-limit-message">{{ editMessage[1] }}</div>
                </div>
            </div>
            <div class="cell empty"></div>

            <!-- Fila 5 -->
            <div class="cell empty"></div>
            <div class="cell empty"></div>
            <div class="cell hypo bottom">
                <div class="cell-title">{{ textsStore.getText('schwartz.hypothesis.h1_minus') }}</div>
                <div class="cell-content">{{ h1H0 }}</div>
            </div>
            <div class="cell empty"></div>
            <div class="cell empty"></div>
        </div>
    </div>
</template>

<script>
import { onMounted, computed, ref } from 'vue';
import { useSectionStore } from '../../../../stores/section';
import { useFutureDriversStore } from '../../../../stores/futureDrivers';
import { useSchwartzStore } from '../../../../stores/schwartz';
import { useTextsStore } from '../../../../stores/texts';

export default {
    name: 'SchwartzMainComponent',
    setup() {
        const sectionStore = useSectionStore();
        const futureDriversStore = useFutureDriversStore();
        const schwartzStore = useSchwartzStore();
        const textsStore = useTextsStore();

        onMounted(async () => {
            sectionStore.setTitleSection(textsStore.getText('schwartz.title'));
            if (futureDriversStore.drivers.length === 0) {
                await futureDriversStore.fetchDrivers();
            }
            // Cargar escenarios guardados
            await schwartzStore.fetchScenarios();
        });

        // Computed para obtener los textos de hipótesis
        const h1H0 = computed(() => futureDriversStore.drivers[0]?.descriptionH0 || '');
        const h1H1 = computed(() => futureDriversStore.drivers[0]?.descriptionH1 || '');
        const h2H0 = computed(() => futureDriversStore.drivers[1]?.descriptionH0 || '');
        const h2H1 = computed(() => futureDriversStore.drivers[1]?.descriptionH1 || '');

        // Computed para escenarios
        const escenarios = computed(() => schwartzStore.escenarios);
        const setEscenario = (index, texto) => schwartzStore.setEscenario(index, texto);

        // Nueva función para manejar edición/guardado
        const handleEditSave = async (index, numScenario) => {
            const escenario = schwartzStore.escenarios[index];
            if (escenario.state === 1) return;
            if (editingScenario.value[index]) {
                // Guardar
                schwartzStore.incrementEdit(index);
                editingScenario.value[index] = false;
                // Guardar en backend
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
    min-height: 900px;
    margin-top: 32px;
}
.schwartz-matrix {
    display: grid;
    grid-template-columns: 220px 220px 220px 220px 220px;
    grid-template-rows: 120px 160px 120px 160px 120px;
    gap: 0px;
    position: relative;
    z-index: 2;
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
    min-height: 78px;
    min-width: 234px;
    width: 100%;
    height: 100%;
    transition: all 0.2s ease;
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
}
.cell-content {
    color: #1F2937;
    font-size: 14px;
    font-weight: 500;
    word-break: break-word;
    text-align: center;
    width: 100%;
    margin-top: 8px;
    max-height: 220px;
    overflow-y: auto;
    line-height: 1.5;
}
.cell.hypo {
    background: #FAFAFA;
    border: 1px solid #E0E7FF;
    min-height: 200px;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 3px 12px rgba(79, 70, 229, 0.08);
}
.cell.scenario {
    background: #fff;
    border: 1px solid #E0E7FF;
    min-width: 234px;
    min-height: 104px;
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
    left: 51%;
    width: 750px;
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
    height: 530px;
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
    left: 50px;
    z-index: 2;
    top: -40px;
    box-shadow: 0 3px 12px rgba(79, 70, 229, 0.08);
}
.cell.hypo.left {
    background: #FAFAFA;
    border: 1px solid #E0E7FF;
    position: relative;
    left: -40px;
    z-index: 2;
    top: -40px;
    box-shadow: 0 3px 12px rgba(79, 70, 229, 0.08);
}
.cell.hypo.top {
    background: #FAFAFA;
    border: 1px solid #E0E7FF;
    position: relative;
    top: -120px;
    z-index: 2;
    left: -10px;
    box-shadow: 0 3px 12px rgba(79, 70, 229, 0.08);
}
.cell.hypo.bottom {
    background: #FAFAFA;
    border: 1px solid #E0E7FF;
    position: relative;
    top: 40px;
    z-index: 2;
    left: -10px;
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