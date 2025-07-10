<template>
  <div class="variables-container">
    <div class="main-title">{{ textsStore.getText('strategic.main_title') }}</div>
    <div class="scenario-table-container">
      <div class="scenario-label">
        {{ textsStore.getText('strategic.scenario_label') }} <strong>{{ scenario1?.titulo || '' }}</strong>
      </div>
      <!-- Tabla Escenario 1 -->
      <b-table :data="[{}]" :bordered="true" :striped="false" :hoverable="false">
        <b-table-column>
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
        <b-table-column>
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
        <b-table-column>
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
        <b-table-column>
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
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useStrategicScenarioStore } from '../../../../stores/strategicScenario';
import { useTextsStore } from '../../../../stores/texts';
import axios from 'axios';
import { useSectionStore } from '../../../../stores/section';

const store = useStrategicScenarioStore();
const textsStore = useTextsStore();
const sectionStore = useSectionStore();

// Escenario 1
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

// Escenario 2
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

// Agregar variables y lógica para escenario 3 y 4
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
  // Traer hipótesis
  try {
    const res = await axios.get('/hypothesis');
    const data = res.data.data;
    if (Array.isArray(data) && data.length > 0) {
      // Escenario 1 (antes: H0, H0) → ahora: H1, H1
      hypothesis1_1.value = data[0]?.descriptionH1 || '';
      hypothesis1_2.value = data[1]?.descriptionH1 || '';
      // Escenario 2 (antes: H0, H1) → ahora: H1, H0
      hypothesis2_1.value = data[1]?.descriptionH1 || '';
      hypothesis2_2.value = data[0]?.descriptionH0 || '';
      // Escenario 3 (antes: H1, H1) → ahora: H0, H0
      hypothesis3_1.value = data[0]?.descriptionH0 || '';
      hypothesis3_2.value = data[1]?.descriptionH0 || '';
      // Escenario 4 (antes: H1, H0) → ahora: H0, H1
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

const handleEditSave = async (numScenario, yearField) => {
  // numScenario: 1 o 2
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
    } else {
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
    } else {
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
    } else {
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
    } else {
      localValue = localYear4_3;
      editingRef = editingYear4_3;
      editsField = 'edits_year3';
    }
  }

  if ((scenario[editsField] || 0) >= 3) {
    console.log(`${yearField} está bloqueado - ${scenario[editsField]} ediciones`);
    return;
  }

  if (editingRef.value) {
    // Guardar
    let currentEdits = (scenario[editsField] || 0) + 1;
    const payload = {
      titulo: scenario.titulo,
      edits: scenario.edits,
      state: scenario.state,
      num_scenario: numScenario,
      [yearField]: localValue.value,
      [editsField]: currentEdits
    };
    try {
      const res = await axios.post('/scenarios', payload);
      if (res.data && res.data.data) {
        await store.fetchScenarios();
        editingRef.value = false;
        if (currentEdits >= 3) {
          editMessage.value[yearField] = textsStore.getText('strategic.edit_limit');
        } else {
          editMessage.value[yearField] = '';
        }
      } else {
        editMessage.value[yearField] = textsStore.getText('strategic.save_error');
      }
    } catch (error) {
      editMessage.value[yearField] = textsStore.getText('strategic.save_error');
    }
  } else {
    editingRef.value = true;
    editMessage.value[yearField] = '';
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
  color: #4F46E5;
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
  min-height: 62px !important; /* 20% más que 52px */
}
.edit-btn {
  min-width: 80px;
  max-width: 100px;
  font-size: 12px;
  padding: 2px 10px;
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

/* Centrado vertical SOLO en filas de datos (tbody td) de la tabla de variables y escenarios */
::v-deep .b-table .table tbody td {
    vertical-align: middle !important;
    height: 80px !important;
}

/* Opcional: puedes agregar .description-column si tienes columnas de texto largo */
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
</style>

<style>
.b-table th,
.is-centered {
  text-align: center !important;
}
</style> 