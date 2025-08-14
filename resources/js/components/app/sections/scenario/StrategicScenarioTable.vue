<template>
  <div class="variables-container">
    <div class="main-title">{{ textsStore.getText('strategic.main_title') }}</div>
    <div class="scenario-table-container">
      <div class="scenario-label">
        {{ textsStore.getText('strategic.scenario_label') }} <strong>{{ scenario1?.titulo || '' }}</strong>
      </div>
      <!-- Tabla Escenario 1 -->
      <b-table :data="[{}]" :bordered="true" :striped="false" :hoverable="false">
        <b-table-column centered>
          <div class="fixed-label multiline-label">
            {{ textsStore.getText('strategic.plan_label') }}
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.name') }}
            </span>
          </template>
          <div class="hypothesis-block">
            <div class="hypo-title">{{ textsStore.getText('strategic.hypothesis1') }}</div>
            <div class="hypo-content">{{ hypothesis1_1 }}</div>
            <div class="hypo-title">{{ textsStore.getText('strategic.hypothesis2') }}</div>
            <div class="hypo-content">{{ hypothesis1_2 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year1') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear1_1"
              type="textarea"
              :disabled="!editingYear1_1 || (scenario1?.edits_year1 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear1_1, $event)"
              @paste="handleTextPaste(localYear1_1, $event)"
              @keyup="handleTextKeyup(localYear1_1, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(1, 'year1')"
                  :disabled="(scenario1?.edits_year1 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear1_1 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage1.year1" class="edit-limit-message">{{ editMessage1.year1 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year2') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear1_2"
              type="textarea"
              :disabled="!editingYear1_2 || (scenario1?.edits_year2 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear1_2, $event)"
              @paste="handleTextPaste(localYear1_2, $event)"
              @keyup="handleTextKeyup(localYear1_2, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(1, 'year2')"
                  :disabled="(scenario1?.edits_year2 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear1_2 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage1.year2" class="edit-limit-message">{{ editMessage1.year2 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year3') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear1_3"
              type="textarea"
              :disabled="!editingYear1_3 || (scenario1?.edits_year3 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear1_3, $event)"
              @paste="handleTextPaste(localYear1_3, $event)"
              @keyup="handleTextKeyup(localYear1_3, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(1, 'year3')"
                  :disabled="(scenario1?.edits_year3 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear1_3 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage1.year3" class="edit-limit-message">{{ editMessage1.year3 }}</div>
          </div>
        </b-table-column>
      </b-table>
    </div>
    <div class="scenario-table-container">
      <div class="scenario-label" style="margin-top: 50px;">
        {{ textsStore.getText('strategic.scenario_label') }} <strong>{{ scenario2?.titulo || '' }}</strong>
      </div>
      <b-table :data="[{}]" :bordered="true" :striped="false" :hoverable="false">
        <b-table-column centered>
          <div class="fixed-label multiline-label">
            {{ textsStore.getText('strategic.plan_label') }}
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.name') }}
            </span>
          </template>
          <div class="hypothesis-block">
            <div class="hypo-title">Hipótesis 2+</div>
            <div class="hypo-content">{{ hypothesis2_1 }}</div>
            <div class="hypo-title">Hipótesis 1-</div>
            <div class="hypo-content">{{ hypothesis2_2 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year1') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear2_1"
              type="textarea"
              :disabled="!editingYear2_1 || (scenario2?.edits_year1 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear2_1, $event)"
              @paste="handleTextPaste(localYear2_1, $event)"
              @keyup="handleTextKeyup(localYear2_1, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(2, 'year1')"
                  :disabled="(scenario2?.edits_year1 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear2_1 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage2.year1" class="edit-limit-message">{{ editMessage2.year1 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year2') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear2_2"
              type="textarea"
              :disabled="!editingYear2_2 || (scenario2?.edits_year2 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear2_2, $event)"
              @paste="handleTextPaste(localYear2_2, $event)"
              @keyup="handleTextKeyup(localYear2_2, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(2, 'year2')"
                  :disabled="(scenario2?.edits_year2 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear2_2 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage2.year2" class="edit-limit-message">{{ editMessage2.year2 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year3') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear2_3"
              type="textarea"
              :disabled="!editingYear2_3 || (scenario2?.edits_year3 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear2_3, $event)"
              @paste="handleTextPaste(localYear2_3, $event)"
              @keyup="handleTextKeyup(localYear2_3, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(2, 'year3')"
                  :disabled="(scenario2?.edits_year3 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear2_3 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage2.year3" class="edit-limit-message">{{ editMessage2.year3 }}</div>
          </div>
        </b-table-column>
      </b-table>
    </div>
    <div class="scenario-table-container">
      <div class="scenario-label" style="margin-top: 50px;">
        {{ textsStore.getText('strategic.scenario_label') }} <strong>{{ scenario3?.titulo || '' }}</strong>
      </div>
      <b-table :data="[{}]" :bordered="true" :striped="false" :hoverable="false">
        <b-table-column centered>
          <div class="fixed-label multiline-label">
            {{ textsStore.getText('strategic.plan_label') }}
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.name') }}
            </span>
          </template>
          <div class="hypothesis-block">
            <div class="hypo-title">Hipótesis 1-</div>
            <div class="hypo-content">{{ hypothesis3_1 }}</div>
            <div class="hypo-title">Hipótesis 2-</div>
            <div class="hypo-content">{{ hypothesis3_2 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year1') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear3_1"
              type="textarea"
              :disabled="!editingYear3_1 || (scenario3?.edits_year1 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear3_1, $event)"
              @paste="handleTextPaste(localYear3_1, $event)"
              @keyup="handleTextKeyup(localYear3_1, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(3, 'year1')"
                  :disabled="(scenario3?.edits_year1 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear3_1 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage3.year1" class="edit-limit-message">{{ editMessage3.year1 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year2') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear3_2"
              type="textarea"
              :disabled="!editingYear3_2 || (scenario3?.edits_year2 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear3_2, $event)"
              @paste="handleTextPaste(localYear3_2, $event)"
              @keyup="handleTextKeyup(localYear3_2, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(3, 'year2')"
                  :disabled="(scenario3?.edits_year2 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear3_2 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage3.year2" class="edit-limit-message">{{ editMessage3.year2 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year3') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear3_3"
              type="textarea"
              :disabled="!editingYear3_3 || (scenario3?.edits_year3 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear3_3, $event)"
              @paste="handleTextPaste(localYear3_3, $event)"
              @keyup="handleTextKeyup(localYear3_3, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(3, 'year3')"
                  :disabled="(scenario3?.edits_year3 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear3_3 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage3.year3" class="edit-limit-message">{{ editMessage3.year3 }}</div>
          </div>
        </b-table-column>
      </b-table>
    </div>
    <div class="scenario-table-container">
      <div class="scenario-label" style="margin-top: 50px;">
        {{ textsStore.getText('strategic.scenario_label') }} <strong>{{ scenario4?.titulo || '' }}</strong>
      </div>
      <b-table :data="[{}]" :bordered="true" :striped="false" :hoverable="false">
        <b-table-column centered>
          <div class="fixed-label multiline-label">
            {{ textsStore.getText('strategic.plan_label') }}
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.name') }}
            </span>
          </template>
          <div class="hypothesis-block">
            <div class="hypo-title">Hipótesis 2-</div>
            <div class="hypo-content">{{ hypothesis4_1 }}</div>
            <div class="hypo-title">Hipótesis 1+</div>
            <div class="hypo-content">{{ hypothesis4_2 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year1') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear4_1"
              type="textarea"
              :disabled="!editingYear4_1 || (scenario4?.edits_year1 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear4_1, $event)"
              @paste="handleTextPaste(localYear4_1, $event)"
              @keyup="handleTextKeyup(localYear4_1, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(4, 'year1')"
                  :disabled="(scenario4?.edits_year1 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear4_1 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage4.year1" class="edit-limit-message">{{ editMessage4.year1 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year2') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear4_2"
              type="textarea"
              :disabled="!editingYear4_2 || (scenario4?.edits_year2 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear4_2, $event)"
              @paste="handleTextPaste(localYear4_2, $event)"
              @keyup="handleTextKeyup(localYear4_2, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(4, 'year2')"
                  :disabled="(scenario4?.edits_year2 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear4_2 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage4.year2" class="edit-limit-message">{{ editMessage4.year2 }}</div>
          </div>
        </b-table-column>
        <b-table-column centered>
          <template #header>
            <span class="table-header-centered">
              {{ textsStore.getText('strategic.year3') }}
            </span>
          </template>
          <div class="year-block">
            <b-input
              v-model="localYear4_3"
              type="textarea"
              :disabled="!editingYear4_3 || (scenario4?.edits_year3 || 0) >= 3"
              rows="5"
              @input="handleTextInput(localYear4_3, $event)"
              @paste="handleTextPaste(localYear4_3, $event)"
              @keyup="handleTextKeyup(localYear4_3, $event)"
            />
            <div class="edit-btn-container">
              <div class="buttons is-centered">
                <b-button
                  size="is-small"
                  @click="handleEditSave(4, 'year3')"
                  :disabled="(scenario4?.edits_year3 || 0) >= 3"
                  class="edit-btn"
                >
                  <b-icon icon="edit" size="is-small" />
                  {{ editingYear4_3 ? textsStore.getText('strategic.save') : textsStore.getText('strategic.edit') }}
                </b-button>
              </div>
            </div>
            <div v-if="editMessage4.year3" class="edit-limit-message">{{ editMessage4.year3 }}</div>
          </div>
        </b-table-column>
      </b-table>
    </div>
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
      @click="mostrarModalRegresar = true"
    >Regresar</button>
  </div>
  <!-- Modal de confirmación -->
  <div v-if="mostrarModal" class="modal-confirm">
    <div class="modal-content">
      <p>{{ textsStore.getText('scenarios_section.close_confirm_message') }}</p>
      <button @click="cerrarModulo">{{ textsStore.getText('scenarios_section.confirm_yes') }}</button>
      <button @click="mostrarModal = false">{{ textsStore.getText('scenarios_section.confirm_no') }}</button>
    </div>
  </div>
      <!-- Modal de confirmación para regresar -->
    <div v-if="mostrarModalRegresar" class="modal-confirm">
      <div class="modal-content">
        <p>{{ textsStore.getText('scenarios_section.return_confirm_message') }}</p>
        <button @click="regresarModulo">{{ textsStore.getText('scenarios_section.confirm_yes_return') }}</button>
        <button @click="mostrarModalRegresar = false">{{ textsStore.getText('scenarios_section.confirm_no') }}</button>
      </div>
    </div>
</template>

<script>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { useStrategicScenarioStore } from '../../../../stores/strategicScenario';
import { useTextsStore } from '../../../../stores/texts';
import { useSessionStore } from '../../../../stores/session';
import axios from 'axios';
import { useSectionStore } from '../../../../stores/section';

export default {
    name: 'StrategicScenarioTable',
    setup() {
        const store = useStrategicScenarioStore();
        const textsStore = useTextsStore();
        const sectionStore = useSectionStore();
        const sessionStore = useSessionStore();

        const cerrado = ref(false);
        const mostrarModal = ref(false);
        const state = ref(null); 

        const MAX_CHARACTERS = 255;

        const handleTextInput = (localValue, event = null) => {
            const text = event ? event.target.value : localValue.value;
            if (text.length > MAX_CHARACTERS) {
                
                localValue.value = text.substring(0, MAX_CHARACTERS);
            }
        };

        const handleTextPaste = (localValue, event) => {
            const pastedText = (event.clipboardData || window.clipboardData).getData('text');
            const currentText = localValue.value;
            const combinedText = currentText + pastedText;
            
            if (combinedText.length <= MAX_CHARACTERS) {
                
                return;
            } else {
                
                event.preventDefault();
                
                const availableSpace = MAX_CHARACTERS - currentText.length;
                if (availableSpace > 0) {
                    const truncatedPastedText = pastedText.substring(0, availableSpace);
                    localValue.value = currentText + truncatedPastedText;
                } else {
                }
            }
        };

        const handleTextKeyup = (localValue, event) => {
            const text = event.target.value;
            if (text.length >= MAX_CHARACTERS) {
                
                if (event.key !== 'Backspace' && event.key !== 'Delete' && event.key !== 'Tab') {
                    event.preventDefault();
                }
            }
        };

        const scenario1 = computed(() => store.scenario1);
        const localYear1_1 = ref('');
        const localYear1_2 = ref('');
        const localYear1_3 = ref('');
        const editingYear1_1 = ref(false);
        const editingYear1_2 = ref(false);
        const editingYear1_3 = ref(false);
        const editMessage1 = ref({ year1: '', year2: '', year3: '' });
        const hypothesis1_1 = ref('');
        const hypothesis1_2 = ref('');

        const scenario2 = computed(() => store.scenario2);
        const localYear2_1 = ref('');
        const localYear2_2 = ref('');
        const localYear2_3 = ref('');
        const editingYear2_1 = ref(false);
        const editingYear2_2 = ref(false);
        const editingYear2_3 = ref(false);
        const editMessage2 = ref({ year1: '', year2: '', year3: '' });
        const hypothesis2_1 = ref('');
        const hypothesis2_2 = ref('');

        const scenario3 = computed(() => store.scenario3);
        const localYear3_1 = ref('');
        const localYear3_2 = ref('');
        const localYear3_3 = ref('');
        const editingYear3_1 = ref(false);
        const editingYear3_2 = ref(false);
        const editingYear3_3 = ref(false);
        const editMessage3 = ref({ year1: '', year2: '', year3: '' });
        const hypothesis3_1 = ref('');
        const hypothesis3_2 = ref('');

        const scenario4 = computed(() => store.scenario4);
        const localYear4_1 = ref('');
        const localYear4_2 = ref('');
        const localYear4_3 = ref('');
        const editingYear4_1 = ref(false);
        const editingYear4_2 = ref(false);
        const editingYear4_3 = ref(false);
        const editMessage4 = ref({ year1: '', year2: '', year3: '' });
        const hypothesis4_1 = ref('');
        const hypothesis4_2 = ref('');

        onMounted(async () => {
            sectionStore.setTitleSection('Escenarios');
            await store.fetchScenarios();
            
            await loadStateValue();
            
            if (typeof window !== 'undefined') {
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const cerradoKey = 'scenarios_cerrado_' + (user.id || 'anon');
                cerrado.value = localStorage.getItem(cerradoKey) === 'true';
            }
            if (scenario1.value) {
                localYear1_1.value = scenario1.value.year1 || '';
                localYear1_2.value = scenario1.value.year2 || '';
                localYear1_3.value = scenario1.value.year3 || '';
            }
            if (scenario2.value) {
                localYear2_1.value = scenario2.value.year1 || '';
                localYear2_2.value = scenario2.value.year2 || '';
                localYear2_3.value = scenario2.value.year3 || '';
            }
            if (scenario3.value) {
                localYear3_1.value = scenario3.value.year1 || '';
                localYear3_2.value = scenario3.value.year2 || '';
                localYear3_3.value = scenario3.value.year3 || '';
            }
            if (scenario4.value) {
                localYear4_1.value = scenario4.value.year1 || '';
                localYear4_2.value = scenario4.value.year2 || '';
                localYear4_3.value = scenario4.value.year3 || '';
            }
            
            try {
                const res = await axios.get('/hypothesis');
                const data = res.data.data;
                if (Array.isArray(data) && data.length > 0) {
                    
                    hypothesis1_1.value = data[0]?.descriptionH1 || '';
                    hypothesis1_2.value = data[1]?.descriptionH1 || '';
                    
                    hypothesis2_1.value = data[1]?.descriptionH1 || '';
                    hypothesis2_2.value = data[0]?.descriptionH0 || '';
                    
                    hypothesis3_1.value = data[0]?.descriptionH0 || '';
                    hypothesis3_2.value = data[1]?.descriptionH0 || '';
                    
                    hypothesis4_1.value = data[1]?.descriptionH0 || '';
                    hypothesis4_2.value = data[0]?.descriptionH1 || '';
                }
            } catch (e) {
                hypothesis1_1.value = '';
                hypothesis1_2.value = '';
                hypothesis2_1.value = '';
                hypothesis2_2.value = '';
                hypothesis3_1.value = '';
                hypothesis3_2.value = '';
                hypothesis4_1.value = '';
                hypothesis4_2.value = '';
            }
        });

        onBeforeUnmount(() => {
            
            sectionStore.clearDynamicButtons();
        });

        const loadStateValue = async () => {
            try {
                const response = await axios.get('/traceability/current-route-state');
                if (response.data && response.data.success && response.data.state !== undefined) {
                    state.value = response.data.state;
                }
            } catch (error) {
                console.error('Error al cargar state:', error);
            }
        };

        const incrementState = async () => {
            try {
                await axios.put('/traceability/current-route-state', { state: '1' });
                state.value = '1';
            } catch (error) {
                console.error('Error al actualizar state:', error);
            }
        };

        const handleEditSave = async (numScenario, yearField) => {
            
            let scenario, localValue, editingRef, editsField, editMessage;
            if (numScenario === 1) {
                scenario = scenario1.value;
                editMessage = editMessage1;
                if (yearField === 'year1') {
                    localValue = localYear1_1;
                    editingRef = editingYear1_1;
                    editsField = 'edits_year1';
                } else if (yearField === 'year2') {
                    localValue = localYear1_2;
                    editingRef = editingYear1_2;
                    editsField = 'edits_year2';
                } else if (yearField === 'year3') {
                    localValue = localYear1_3;
                    editingRef = editingYear1_3;
                    editsField = 'edits_year3';
                }
            } else if (numScenario === 2) {
                scenario = scenario2.value;
                editMessage = editMessage2;
                if (yearField === 'year1') {
                    localValue = localYear2_1;
                    editingRef = editingYear2_1;
                    editsField = 'edits_year1';
                } else if (yearField === 'year2') {
                    localValue = localYear2_2;
                    editingRef = editingYear2_2;
                    editsField = 'edits_year2';
                } else if (yearField === 'year3') {
                    localValue = localYear2_3;
                    editingRef = editingYear2_3;
                    editsField = 'edits_year3';
                }
            } else if (numScenario === 3) {
                scenario = scenario3.value;
                editMessage = editMessage3;
                if (yearField === 'year1') {
                    localValue = localYear3_1;
                    editingRef = editingYear3_1;
                    editsField = 'edits_year1';
                } else if (yearField === 'year2') {
                    localValue = localYear3_2;
                    editingRef = editingYear3_2;
                    editsField = 'edits_year2';
                } else if (yearField === 'year3') {
                    localValue = localYear3_3;
                    editingRef = editingYear3_3;
                    editsField = 'edits_year3';
                }
            } else if (numScenario === 4) {
                scenario = scenario4.value;
                editMessage = editMessage4;
                if (yearField === 'year1') {
                    localValue = localYear4_1;
                    editingRef = editingYear4_1;
                    editsField = 'edits_year1';
                } else if (yearField === 'year2') {
                    localValue = localYear4_2;
                    editingRef = editingYear4_2;
                    editsField = 'edits_year2';
                } else if (yearField === 'year3') {
                    localValue = localYear4_3;
                    editingRef = editingYear4_3;
                    editsField = 'edits_year3';
                }
            }

            if (!scenario || scenario.state === 1) {
                return;
            }

            if (editingRef.value) {
                
                const result = await store.saveScenario(numScenario, yearField, localValue.value);
                if (result.success) {
                    editingRef.value = false;
                    editMessage.value[yearField] = '';
                } else {
                    editMessage.value[yearField] = textsStore.getText('strategic.messages.save_error');
                }
            } else {
                
                editingRef.value = true;
            }
        };

        const updateScenarioInServer = async (scenarioId) => {
            try {
                const result = await store.updateScenario(scenarioId);
                if (result.success) {
                    
                }
            } catch (error) {
                console.error('Error al actualizar escenario:', error);
            }
        };

        return {
            store,
            textsStore,
            sectionStore,
            sessionStore,
            cerrado,
            mostrarModal,
            state,
            handleTextInput,
            handleTextPaste,
            handleTextKeyup,
            scenario1,
            localYear1_1,
            localYear1_2,
            localYear1_3,
            editingYear1_1,
            editingYear1_2,
            editingYear1_3,
            editMessage1,
            hypothesis1_1,
            hypothesis1_2,
            scenario2,
            localYear2_1,
            localYear2_2,
            localYear2_3,
            editingYear2_1,
            editingYear2_2,
            editingYear2_3,
            editMessage2,
            hypothesis2_1,
            hypothesis2_2,
            scenario3,
            localYear3_1,
            localYear3_2,
            localYear3_3,
            editingYear3_1,
            editingYear3_2,
            editingYear3_3,
            editMessage3,
            hypothesis3_1,
            hypothesis3_2,
            scenario4,
            localYear4_1,
            localYear4_2,
            localYear4_3,
            editingYear4_1,
            editingYear4_2,
            editingYear4_3,
            editMessage4,
            hypothesis4_1,
            hypothesis4_2,
            handleEditSave,
            updateScenarioInServer,
            MAX_CHARACTERS
        };
    },

    methods: {
        confirmarCerrar() {
            this.mostrarModal = true;
        },

        async cerrarModulo() {
            this.mostrarModal = false;
            
            const result = await this.store.closeAllScenarios();
            if (result.success) {
                
                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'cerrar', modulo: 'scenarios' }));
                
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const cerradoKey = 'scenarios_cerrado_' + (user.id || 'anon');
                localStorage.setItem(cerradoKey, 'true');
                this.$buefy.toast.open({
                    message: 'Módulo de escenarios estratégicos cerrado correctamente',
                    type: 'is-success'
                });
                this.sessionStore.setActiveContent('main');
                
                setTimeout(async () => {
                    const { useTraceabilityStore } = await import('../../../../stores/traceability');
                    const traceabilityStore = useTraceabilityStore();
                    await traceabilityStore.markSectionCompleted('scenarios');
                }, 1000);
                
                this.cerrado = true;
            } else {
                this.$buefy.toast.open({
                    message: 'Error al cerrar el módulo de escenarios estratégicos',
                    type: 'is-danger'
                });
            }
        },

        async regresarModulo() {
            this.mostrarModalRegresar = false;
            try {
                
                await this.incrementState();
                
                await this.loadStateValue();
                
                localStorage.setItem('accion_pendiente', JSON.stringify({ tipo: 'regresar', modulo: 'scenarios' }));
                
                const user = JSON.parse(localStorage.getItem('user')) || {};
                const posteriores = [
                  'conclusions', 'results' 
                ];
                posteriores.forEach(mod => {
                  const cerradoKey = mod + '_cerrado_' + (user.id || 'anon');
                  localStorage.removeItem(cerradoKey);
                });
                
                this.sessionStore.setActiveContent('main');
                
                const cerradoKey = 'scenarios_cerrado_' + (user.id || 'anon');
                localStorage.removeItem(cerradoKey);
                this.cerrado = false;
                this.$buefy.toast.open({
                    message: 'Módulo de escenarios estratégicos reabierto correctamente',
                    type: 'is-success'
                });
            } catch (error) {
                this.$buefy.toast.open({
                    message: 'Error al regresar el módulo de escenarios estratégicos',
                    type: 'is-danger'
                });
            }
        }
    }
};
</script>

<style scoped>
.main-title {
  font-weight: bold;
  font-size: 20px;
  text-align: center;
  margin-top: 50px;
  margin-bottom: 40px;
  color: #1F2937;
}
.scenario-label {
  font-size: 16px;
  margin-bottom: 30px;
  text-align: center;
  color: #374151;
}
.scenario-title {
  font-weight: bold;
  margin-bottom: 12px;
  font-size: 16px;
}
.fixed-label {
  font-weight: bold;
  font-size: 15px;
  margin-top: 20px;
}
.multiline-label {
  max-width: 220px;
  white-space: normal;
  word-break: break-word;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  min-height: 80px;
}
.hypothesis-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: center;
  justify-content: center;
}
.hypo-title {
  font-weight: 600;
  color: #F47920;
  font-size: 13px;
}
.hypo-content {
  color: #1F2937;
  font-size: 14px;
  font-weight: 500;
  word-break: break-word;
  text-align: center;
  background: #f5f6fa;
  padding: 6px 10px;
  border-radius: 4px;
  white-space: pre-line;
  max-width: 220px;
  min-height: 32px;
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.year-block {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  justify-content: center;
  gap: 6px;
  height: 100%;
  width: 100%;
  padding: 0;
  margin: 0;
}
.edit-btn-container {
  display: flex;
  justify-content: center;
  width: 100%;
}
.year-block :deep(.b-input),
.year-block :deep(textarea) {
  width: 100% !important;
  min-width: 0 !important;
  max-width: 100% !important;
  box-sizing: border-box !important;
  text-align: center;
  display: block !important;
  min-height: 62px !important; 
}
.edit-btn {
  min-width: 80px;
  max-width: 100px;
  font-size: 12px;
  padding: 2px 10px;
  border-color: #005883 !important;
  color: #005883 !important;
  background-color: transparent !important;
}

.edit-btn:hover:not(:disabled) {
  background-color: #005883 !important;
  color: white !important;
  border-color: #005883 !important;
}

/* Cambiar el color de los botones de editar por el azul clave */
:deep(.edit-button-component .edit-button.is-info) {
  border-color: #005883 !important;
  color: #005883 !important;
}

:deep(.edit-button-component .edit-button.is-info:hover:not(:disabled)) {
  background-color: #005883 !important;
  color: white !important;
}
.edit-limit-message {
  color: #e53e3e;
  font-size: 13px;
  margin-top: 4px;
  text-align: right;
  width: 100%;
}
:deep(.b-table th),
:deep(.b-table td) {
  width: 180px;
  min-width: 180px;
  max-width: 180px;
  text-align: center !important;
  vertical-align: middle !important;
  word-break: break-word;
}
:deep(.table-header-centered) {
  display: block;
  text-align: center;
  width: 100%;
}
.variables-container {
    padding: 20px;
}

.buttons {
    gap: 0.5rem;
}

:deep(.b-table .table tbody td) {
    vertical-align: middle !important;
    height: 80px !important;
}

.scenario-table-container {
  background: #f5f5fa;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  margin-bottom: 32px;
  padding: 18px 10px 10px 10px;
  border: 1px solid #ede7f6;
}

:deep(.b-table .table) {
  border-radius: 12px;
  overflow: hidden;
}

:deep(.b-table .table th),
:deep(.b-table .table td) {
  background: #f5f5fa !important;
  border-color: #ede7f6 !important;
}

:deep(.b-table .table tbody tr:nth-child(even) td) {
  background: #f3f0fa !important;
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
</style>

<style>
.b-table th,
.is-centered {
  text-align: center !important;
}
</style>