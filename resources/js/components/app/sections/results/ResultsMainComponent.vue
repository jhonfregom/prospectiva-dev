<script>

import { useSectionStore } from '../../../../stores/section';
import { useTextsStore } from '../../../../stores/texts';
import { useResultsStore } from '../../../../stores/results';
import { storeToRefs } from 'pinia';
import { ref, computed } from 'vue';

export default {
    setup() {
        const sectionStore = useSectionStore();
        const textsStore = useTextsStore();
        const resultsStore = useResultsStore();
        const { users, isLoading } = storeToRefs(resultsStore);
        
        // Obtener el usuario autenticado desde localStorage o Pinia según tu app
        const user = JSON.parse(localStorage.getItem('user')) || {};
        const isAdmin = user.role === 1;
        
        // Filtros de búsqueda
        const filterId = ref('');
        const filterFirstName = ref('');
        const filterLastName = ref('');
        const filterDocumentId = ref('');
        
        // Computed para filtrar usuarios
        const filteredUsers = computed(() => {
            if (!users.value) return [];
            
            return users.value.filter(user => {
                const idMatch = !filterId.value || user.id.toString().includes(filterId.value);
                const firstNameMatch = !filterFirstName.value || 
                    user.first_name.toLowerCase().includes(filterFirstName.value.toLowerCase());
                const lastNameMatch = !filterLastName.value || 
                    user.last_name.toLowerCase().includes(filterLastName.value.toLowerCase());
                const documentIdMatch = !filterDocumentId.value || 
                    user.document_id.toString().includes(filterDocumentId.value);
                
                return idMatch && firstNameMatch && lastNameMatch && documentIdMatch;
            });
        });
        
        // 1. Agrega las refs y métodos para el modal propio
        const showVariableModal = ref(false);
        const showZoneModal = ref(false);
        const showScenarioModal = ref(false);
        const showConclusionModal = ref(false);
        const showZoneAnalysisModal = ref(false);
        const showFutureDriverModal = ref(false);
        const showInitialConditionModal = ref(false);
        const selectedVariable = ref(null);
        const selectedZone = ref(null);
        const selectedScenario = ref(null);
        const selectedConclusion = ref(null);
        const selectedZoneAnalysis = ref(null);
        const selectedFutureDriver = ref(null);
        const selectedInitialCondition = ref(null);

        function showVariableDescription(variable) {
            console.log('Abriendo modal para variable:', variable);
            selectedVariable.value = variable;
            showVariableModal.value = true;
        }

        function showZoneAnalysisDescription(analysis) {
            console.log('Abriendo modal para análisis de zona:', analysis);
            selectedZoneAnalysis.value = analysis;
            showZoneAnalysisModal.value = true;
        }

        function showConclusionDescription(conclusion) {
            console.log('Abriendo modal para conclusión:', conclusion);
            console.log('Datos de la conclusión:', {
              id: conclusion.id,
              title: conclusion.title,
              description: conclusion.description,
              component_practice: conclusion.component_practice,
              actuality: conclusion.actuality,
              aplication: conclusion.aplication
            });
            selectedConclusion.value = conclusion;
            showConclusionModal.value = true;
        }

        function showFutureDriverDescription(driver) {
            console.log('Abriendo modal para direccionador de futuro:', driver);
            selectedFutureDriver.value = driver;
            showFutureDriverModal.value = true;
        }

        function showInitialConditionDescription(condition) {
            console.log('Abriendo modal para condición inicial:', condition);
            selectedInitialCondition.value = condition;
            showInitialConditionModal.value = true;
        }

        function showScenarioDescription(scenario) {
            console.log('Abriendo modal para escenario:', scenario);
            selectedScenario.value = scenario;
            showScenarioModal.value = true;
        }

        return { 
            sectionStore,
            textsStore, 
            resultsStore, 
            users, 
            isLoading, 
            isAdmin,
            filterId,
            filterFirstName,
            filterLastName,
            filterDocumentId,
            filteredUsers,
            showVariableModal,
            showZoneModal,
            showScenarioModal,
            showConclusionModal,
            showZoneAnalysisModal,
            showFutureDriverModal,
            showInitialConditionModal,
            selectedVariable,
            selectedZone,
            selectedScenario,
            selectedConclusion,
            selectedZoneAnalysis,
            selectedFutureDriver,
            selectedInitialCondition,
            showVariableDescription,
            showZoneAnalysisDescription,
            showScenarioDescription,
            showConclusionDescription,
            showFutureDriverDescription,
            showInitialConditionDescription
        };
    },
    mounted() {
        this.sectionStore.setTitleSection(this.textsStore.getText('results_section.title'));
        this.resultsStore.fetchUsers();
        
        // Centrar el encabezado de la columna 'Correo' de forma más robusta
        this.$nextTick(() => {
            this.centerEmailHeader();
        });
        
        // Debug: verificar si los textos se cargan correctamente
        console.log('Textos cargados:', {
            variables_count: this.textsStore.getText('results_section.table.variables_count'),
            variables_list: this.textsStore.getText('results_section.table.variables_list')
        });
    },
    updated() {
        // Centrar el encabezado cuando la tabla se actualice
        this.$nextTick(() => {
            this.centerEmailHeader();
        });
    },
    methods: {
        centerEmailHeader() {
            // Método más robusto para centrar el encabezado de correo
            setTimeout(() => {
                const headers = this.$el.querySelectorAll('.b-table thead th');
                headers.forEach((th) => {
                    const text = th.textContent.trim().toLowerCase();
                    if (text.includes('correo') || text.includes('email') || text.includes('e-mail')) {
                        th.style.textAlign = 'center';
                        th.style.verticalAlign = 'middle';
                        th.classList.add('email-header-centered');
                    }
                });
            }, 200);
        },
        // Solo permitir números en el filtro de ID
        onIdInput(event) {
            const value = event.target.value;
            if (value === '' || /^\d+$/.test(value)) {
                this.filterId = value;
            } else {
                event.target.value = this.filterId;
            }
        },
        // Solo permitir números en el filtro de identificación
        onDocumentIdInput(event) {
            const value = event.target.value;
            if (value === '' || /^\d+$/.test(value)) {
                this.filterDocumentId = value;
            } else {
                event.target.value = this.filterDocumentId;
            }
        },
    }
}

</script>
<template>
    <div class="main-content">
    <!-- Barra de filtros alineada con las columnas -->
    <div class="filters-bar" v-if="isAdmin">
      <div class="columns is-multiline">
        <div class="column is-2">
          <b-field label="Buscar por ID">
            <b-input
              v-model="filterId"
              placeholder="Ingrese ID..."
              @input="onIdInput"
              type="text"
              icon="fas fa-search"
            />
          </b-field>
        </div>
        <div class="column is-2">
          <b-field label="Buscar por Nombre">
            <b-input
              v-model="filterFirstName"
              placeholder="Ingrese nombre..."
              type="text"
              icon="fas fa-search"
            />
          </b-field>
        </div>
        <div class="column is-2">
          <b-field label="Buscar por Apellido">
            <b-input
              v-model="filterLastName"
              placeholder="Ingrese apellido..."
              type="text"
              icon="fas fa-search"
            />
          </b-field>
        </div>
        <div class="column is-2">
          <b-field label="Buscar por Identificación">
            <b-input
              v-model="filterDocumentId"
              placeholder="Ingrese identificación..."
              @input="onDocumentIdInput"
              type="text"
              icon="fas fa-search"
            />
          </b-field>
        </div>
        <div class="column is-4">
          <!-- Espacio para la columna de email (sin filtro) -->
        </div>
      </div>
    </div>

    <!-- Tabla de resultados con columnas alineadas -->
    <div class="table-container">
      <b-table :data="filteredUsers" :loading="isLoading" :striped="true" :hoverable="true" icon-pack="fas">
        <b-table-column v-if="isAdmin" field="id" :label="textsStore.getText('results_section.table.id')" width="10%" centered v-slot="props">
          {{ props.row.id }}
        </b-table-column>
        <b-table-column field="first_name" :label="textsStore.getText('results_section.table.first_name')" width="12%" centered v-slot="props">
          {{ props.row.first_name }}
        </b-table-column>
        <b-table-column field="last_name" :label="textsStore.getText('results_section.table.last_name')" width="12%" centered v-slot="props">
          {{ props.row.last_name }}
        </b-table-column>
        <b-table-column field="document_id" :label="textsStore.getText('results_section.table.document_id')" width="12%" centered v-slot="props">
          {{ props.row.document_id }}
        </b-table-column>
        <b-table-column
          field="user"
          :label="textsStore.getText('results_section.table.email')"
          width="18%"
          centered
          class="email-column"
        >
          <template #default="props">
            {{ props.row.user }}
          </template>
        </b-table-column>
        <b-table-column field="variables_count" :label="textsStore.getText('results_section.table.variables_count') || 'Total Variables'" width="8%" centered v-slot="props">
          <div class="centered-cell">
            <b-tag type="is-info" size="is-medium">{{ props.row.variables_count || 0 }}</b-tag>
          </div>
        </b-table-column>
        <b-table-column field="variables_list" :label="textsStore.getText('results_section.table.variables_list') || 'Variables Creadas'" width="20%" centered v-slot="props">
          <div v-if="props.row.variables_list && props.row.variables_list.length > 0" class="variables-container">
            <div v-for="(variable, index) in props.row.variables_list" :key="variable.id_variable" class="variable-item">
              <span class="variable-link" @click="showVariableDescription(variable)">
                <strong>{{ variable.id_variable }}:</strong> {{ variable.name_variable }}
              </span>
            </div>
          </div>
          <span v-else class="has-text-grey-light">Sin variables</span>
        </b-table-column>
        <b-table-column field="zone_analyses" :label="textsStore.getText('results_section.table.zone_analyses') || 'Análisis Mapa de Variables'" width="20%" centered v-slot="props">
          <div v-if="props.row.zone_analyses && props.row.zone_analyses.length > 0" class="zone-analyses-container">
            <div v-for="(analysis, index) in props.row.zone_analyses" :key="analysis.zone_id" class="zone-analysis-item">
              <span class="zone-analysis-link" @click="showZoneAnalysisDescription(analysis)">
                <strong>{{ analysis.zone_name }}:</strong> 
                <span class="analysis-score">Puntaje: {{ analysis.score }}</span>
              </span>
            </div>
          </div>
          <span v-else class="has-text-grey-light">Sin análisis</span>
        </b-table-column>
        <b-table-column field="future_drivers" :label="textsStore.getText('results_section.table.future_drivers') || 'Direccionadores de Futuro'" width="20%" centered v-slot="props">
          <div v-if="props.row.future_drivers && props.row.future_drivers.length > 0" class="future-drivers-container">
            <div v-for="(driver, index) in props.row.future_drivers" :key="driver.id" class="future-driver-item">
              <span class="future-driver-link" @click="showFutureDriverDescription(driver)">
                <strong>Hipótesis {{ driver.name_hypothesis }}:</strong> 
                <span class="driver-variable">{{ driver.variable_name }} - {{ driver.secondary_hypotheses }}</span>
              </span>
            </div>
          </div>
          <span v-else class="has-text-grey-light">Sin direccionadores</span>
        </b-table-column>
        <b-table-column field="initial_conditions" :label="textsStore.getText('results_section.table.initial_conditions') || 'Condiciones Iniciales'" width="20%" centered v-slot="props">
          <div v-if="props.row.initial_conditions && props.row.initial_conditions.length > 0" class="initial-conditions-container">
            <div v-for="(condition, index) in props.row.initial_conditions" :key="condition.id" class="initial-condition-item">
              <span class="initial-condition-link" @click="showInitialConditionDescription(condition)">
                <strong>{{ condition.variable_id }}:</strong> 
                <span class="condition-variable">{{ condition.variable_name }}</span>
              </span>
            </div>
          </div>
          <span v-else class="has-text-grey-light">Sin condiciones</span>
        </b-table-column>
        <b-table-column field="scenarios" :label="textsStore.getText('results_section.table.scenarios') || 'Escenarios'" width="20%" centered v-slot="props">
          <div v-if="props.row.scenarios && props.row.scenarios.length > 0" class="scenarios-container">
            <div v-for="(scenario, index) in props.row.scenarios" :key="scenario.id" class="scenario-item">
              <span class="scenario-link" @click="showScenarioDescription(scenario)">
                <strong>Escenario {{ scenario.num_scenario }}:</strong> 
                <span class="scenario-title">{{ scenario.titulo || 'Sin título' }}</span>
              </span>
            </div>
          </div>
          <span v-else class="has-text-grey-light">Sin escenarios</span>
        </b-table-column>

        <b-table-column field="conclusions" :label="textsStore.getText('results_section.table.conclusions') || 'Conclusiones'" width="20%" centered v-slot="props">
          <div v-if="props.row.conclusions && props.row.conclusions.length > 0" class="conclusions-container">
            <div v-for="(conclusion, index) in props.row.conclusions" :key="conclusion.id" class="conclusion-item">
              <span class="conclusion-link" @click="showConclusionDescription(conclusion)">
                <strong>Conclusión {{ index + 1 }}:</strong> 
                <span class="conclusion-title">{{ conclusion.component_practice ? 'Con datos' : 'Sin datos' }}</span>
              </span>
            </div>
          </div>
          <span v-else class="has-text-grey-light">Sin conclusiones</span>
        </b-table-column>
      </b-table>
    </div>

    <!-- Modal funcional de Bulma para descripción de variable -->
    <div v-if="showVariableModal" class="modal is-active">
      <div class="modal-background" @click="showVariableModal = false"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <p class="modal-card-title">Detalles de la Variable</p>
          <button class="delete" aria-label="close" @click="showVariableModal = false"></button>
        </header>
        <section class="modal-card-body">
          <div class="modal-info">
            <p><strong>ID:</strong> {{ selectedVariable?.id_variable }}</p>
            <p><strong>Nombre:</strong> {{ selectedVariable?.name_variable }}</p>
            <p><strong>Descripción:</strong></p>
            <div class="modal-content">
              {{ selectedVariable?.description || 'Sin descripción' }}
            </div>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button is-danger" @click="showVariableModal = false">Cerrar</button>
        </footer>
      </div>
    </div>

    <!-- Modal funcional de Bulma para descripción de análisis de zona -->
    <div v-if="showZoneAnalysisModal" class="modal is-active">
      <div class="modal-background" @click="showZoneAnalysisModal = false"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <p class="modal-card-title">Detalles del Análisis de Zona</p>
          <button class="delete" aria-label="close" @click="showZoneAnalysisModal = false"></button>
        </header>
        <section class="modal-card-body">
          <div class="modal-info">
            <p><strong>Zona:</strong> {{ selectedZoneAnalysis?.zone_name }}</p>
            <p><strong>Puntaje:</strong> {{ selectedZoneAnalysis?.score }}</p>
            
            <div v-if="selectedZoneAnalysis?.variables_in_zone && selectedZoneAnalysis.variables_in_zone.length > 0">
              <p><strong>Variables en esta zona:</strong></p>
              <div class="modal-content">
                <div v-for="variable in selectedZoneAnalysis.variables_in_zone" :key="variable.id_variable" class="zone-variable-item">
                  <span class="zone-variable-code" :class="{ 'frontera': variable.frontera }">
                    {{ variable.id_variable }}
                  </span>
                  <span class="zone-variable-name">{{ variable.name_variable }}</span>
                  <span class="zone-variable-coords">
                    (D: {{ variable.dependencia }}, I: {{ variable.influencia }})
                  </span>
                  <span v-if="variable.frontera" class="frontera-indicator" title="Variable en frontera">⚡</span>
                </div>
              </div>
            </div>
            <div v-else>
              <p><strong>Variables en esta zona:</strong> <span class="has-text-grey-light">Sin variables</span></p>
            </div>
            
            <p><strong>Análisis:</strong></p>
            <div class="modal-content">
              {{ selectedZoneAnalysis?.description || 'Sin análisis disponible' }}
            </div>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button is-danger" @click="showZoneAnalysisModal = false">Cerrar</button>
        </footer>
      </div>
    </div>

    <!-- Modal funcional de Bulma para descripción de direccionador de futuro -->
    <div v-if="showFutureDriverModal" class="modal is-active">
      <div class="modal-background" @click="showFutureDriverModal = false"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <p class="modal-card-title">Detalles del Direccionador de Futuro</p>
          <button class="delete" aria-label="close" @click="showFutureDriverModal = false"></button>
        </header>
        <section class="modal-card-body">
          <div class="modal-info">
            <p><strong>Hipótesis:</strong> {{ selectedFutureDriver?.name_hypothesis }}</p>
            <p><strong>Variable:</strong> {{ selectedFutureDriver?.variable_name }}</p>
            <p><strong>Tipo:</strong> {{ selectedFutureDriver?.secondary_hypotheses }}</p>
            
            <p><strong>Descripción:</strong></p>
            <div class="modal-content">
              {{ selectedFutureDriver?.description || 'Sin descripción disponible' }}
            </div>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button is-danger" @click="showFutureDriverModal = false">Cerrar</button>
        </footer>
      </div>
    </div>

    <!-- Modal funcional de Bulma para descripción de condición inicial -->
    <div v-if="showInitialConditionModal" class="modal is-active">
      <div class="modal-background" @click="showInitialConditionModal = false"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <p class="modal-card-title">Detalles de la Condición Inicial</p>
          <button class="delete" aria-label="close" @click="showInitialConditionModal = false"></button>
        </header>
        <section class="modal-card-body">
          <div class="modal-info">
            <p><strong>Variable:</strong> {{ selectedInitialCondition?.variable_id }} - {{ selectedInitialCondition?.variable_name }}</p>
            
            <p><strong>Condición Actual:</strong></p>
            <div class="modal-content">
              {{ selectedInitialCondition?.now_condition || 'Sin condición disponible' }}
            </div>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button is-danger" @click="showInitialConditionModal = false">Cerrar</button>
        </footer>
      </div>
    </div>

    <!-- Modal funcional de Bulma para descripción de escenario -->
    <div v-if="showScenarioModal" class="modal is-active">
      <div class="modal-background" @click="showScenarioModal = false"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <p class="modal-card-title">Detalles del Escenario {{ selectedScenario?.num_scenario }}</p>
          <button class="delete" aria-label="close" @click="showScenarioModal = false"></button>
        </header>
        <section class="modal-card-body">
          <div class="modal-info">
            <p><strong>Número de Escenario:</strong> {{ selectedScenario?.num_scenario }}</p>
            <p><strong>Título:</strong> {{ selectedScenario?.titulo || 'Sin título' }}</p>
          </div>
          
          <div class="modal-section">
            <h4 class="modal-section-title">Hipótesis Asociadas</h4>
            <div v-if="selectedScenario?.hypotheses && selectedScenario.hypotheses.length > 0" class="modal-content">
              <div v-for="hypothesis in selectedScenario.hypotheses" :key="hypothesis.id" class="hypothesis-item">
                <div class="hypothesis-header">
                  <span class="hypothesis-name">{{ hypothesis.name_hypothesis }}</span>
                  <span class="hypothesis-variable">{{ hypothesis.variable_name }}</span>
                </div>
                <div class="hypothesis-description">
                  {{ hypothesis.description || 'Sin descripción disponible' }}
                </div>
              </div>
            </div>
            <div v-else class="no-content">
              <p class="has-text-grey-light">Sin hipótesis asociadas</p>
            </div>
          </div>
          
          <div class="modal-section">
            <h4 class="modal-section-title">Contenido por Años</h4>
            <div v-if="selectedScenario?.year1" class="year-section">
              <h5 class="year-title">Año 1</h5>
              <div class="modal-content">
                {{ selectedScenario.year1 }}
              </div>
            </div>
            
            <div v-if="selectedScenario?.year2" class="year-section">
              <h5 class="year-title">Año 2</h5>
              <div class="modal-content">
                {{ selectedScenario.year2 }}
              </div>
            </div>
            
            <div v-if="selectedScenario?.year3" class="year-section">
              <h5 class="year-title">Año 3</h5>
              <div class="modal-content">
                {{ selectedScenario.year3 }}
              </div>
            </div>
            
            <div v-if="!selectedScenario?.year1 && !selectedScenario?.year2 && !selectedScenario?.year3" class="no-content">
              <p class="has-text-grey-light">Sin contenido de años disponible</p>
            </div>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button is-danger" @click="showScenarioModal = false">Cerrar</button>
        </footer>
      </div>
    </div>

    <!-- Modal funcional de Bulma para descripción de conclusión -->
    <div v-if="showConclusionModal" class="modal is-active">
      <div class="modal-background" @click="showConclusionModal = false"></div>
      <div class="modal-card">
        <header class="modal-card-head">
          <p class="modal-card-title">Detalles de la Conclusión</p>
          <button class="delete" aria-label="close" @click="showConclusionModal = false"></button>
        </header>
        <section class="modal-card-body">
          <div class="modal-info">
            <p><strong>Título:</strong> {{ selectedConclusion?.title || 'Sin título' }}</p>
          </div>
          
          <div v-if="selectedConclusion?.component_practice" class="modal-section">
            <h4 class="modal-section-title">Componente Práctico</h4>
            <div class="modal-content">
              {{ selectedConclusion.component_practice }}
            </div>
          </div>
          
          <div v-if="selectedConclusion?.actuality" class="modal-section">
            <h4 class="modal-section-title">Actualidad</h4>
            <div class="modal-content">
              {{ selectedConclusion.actuality }}
            </div>
          </div>
          
          <div v-if="selectedConclusion?.aplication" class="modal-section">
            <h4 class="modal-section-title">Aplicación</h4>
            <div class="modal-content">
              {{ selectedConclusion.aplication }}
            </div>
          </div>
          
          <div v-if="!selectedConclusion?.component_practice && !selectedConclusion?.actuality && !selectedConclusion?.aplication" class="no-content">
            <p class="has-text-grey-light">Sin contenido disponible</p>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button is-danger" @click="showConclusionModal = false">Cerrar</button>
        </footer>
      </div>
    </div>
    </div>
</template>

<style scoped>
.main-content {
  padding: 20px;
}

.filters-bar {
  background-color: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filters-bar .field {
  margin-bottom: 0;
}

.filters-bar .field-label {
  font-weight: 600;
  color: #363636;
  margin-bottom: 8px;
}

.filters-bar .input {
  border-radius: 6px;
}

.filters-bar .input:focus {
  border-color: #00d1b2;
  box-shadow: 0 0 0 0.125em rgba(0, 209, 178, 0.25);
}

.table-container {
  overflow-x: auto; /* Habilita el scroll horizontal */
  border-radius: 6px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table-container table,
.table-container th,
.table-container td {
  table-layout: fixed !important;
  word-break: break-word;
}

.b-table .table {
  table-layout: fixed !important;
}

.variables-container,
.zone-analyses-container,
.future-drivers-container,
.initial-conditions-container,
.scenarios-container,
.conclusions-container {
  max-height: 200px;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 4px;
  padding-right: 16px; /* Espacio para la barra de scroll */
}

.variable-item {
  margin-bottom: 6px;
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #f8f9fa;
  border-left: 3px solid #3273dc;
  transition: all 0.2s ease;
}

.variable-item:hover {
  background-color: #e3f2fd;
  border-left-color: #1976d2;
}

.variable-item:last-child {
  margin-bottom: 0;
}

.variable-link {
  cursor: pointer;
  display: block;
  font-size: 0.9em;
  line-height: 1.4;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.variable-link:hover {
  background-color: #e3f2fd;
  color: #1976d2;
}

.variable-link strong {
  color: #3273dc;
  font-weight: 600;
}

.variable-link:hover strong {
  color: #1976d2;
}

.zone-analyses-container {
  padding: 4px;
}

.zone-analysis-item {
  margin-bottom: 6px;
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #f0f8ff;
  border-left: 3px solid #4caf50;
  transition: all 0.2s ease;
}

.zone-analysis-item:hover {
  background-color: #e8f5e8;
  border-left-color: #2e7d32;
}

.zone-analysis-item:last-child {
  margin-bottom: 0;
}

.zone-analysis-link {
  cursor: pointer;
  display: block;
  font-size: 0.9em;
  line-height: 1.4;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.zone-analysis-link:hover {
  background-color: #e8f5e8;
  color: #2e7d32;
}

.zone-analysis-link strong {
  color: #4caf50;
  font-weight: 600;
}

.zone-analysis-link:hover strong {
  color: #2e7d32;
}

.analysis-score {
  color: #666;
  font-size: 0.8em;
  margin-left: 8px;
}



.analysis-description {
  background-color: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  margin-top: 8px;
  white-space: pre-wrap;
  line-height: 1.5;
  max-height: 200px;
  overflow-y: auto;
}

.zone-variables-container {
  background-color: #f8f9fa;
  padding: 12px;
  border-radius: 4px;
  margin: 8px 0;
  max-height: 150px;
  overflow-y: auto;
}

.zone-variable-item {
  display: flex;
  align-items: center;
  padding: 6px 8px;
  margin-bottom: 4px;
  background-color: white;
  border-radius: 4px;
  border-left: 3px solid #4caf50;
  transition: all 0.2s ease;
}

.zone-variable-item:hover {
  background-color: #e8f5e8;
  transform: translateX(2px);
}

.zone-variable-item:last-child {
  margin-bottom: 0;
}

.zone-variable-code {
  font-weight: bold;
  color: #4caf50;
  margin-right: 8px;
  padding: 2px 6px;
  background-color: #e8f5e8;
  border-radius: 3px;
  font-size: 0.9em;
}

.zone-variable-code.frontera {
  background-color: #fff3cd;
  color: #856404;
  border: 1px solid #ffeaa7;
}

.zone-variable-name {
  flex: 1;
  font-size: 0.9em;
  color: #333;
}

.zone-variable-coords {
  font-size: 0.8em;
  color: #666;
  margin-left: 8px;
}

.frontera-indicator {
  color: #f39c12;
  margin-left: 4px;
  font-size: 0.9em;
}

.future-drivers-container {
  padding: 4px;
}

.future-driver-item {
  margin-bottom: 6px;
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #fff3e0;
  border-left: 3px solid #ff9800;
  transition: all 0.2s ease;
}

.future-driver-item:hover {
  background-color: #ffe0b2;
  border-left-color: #f57c00;
}

.future-driver-item:last-child {
  margin-bottom: 0;
}

.future-driver-link {
  cursor: pointer;
  display: block;
  font-size: 0.9em;
  line-height: 1.4;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.future-driver-link:hover {
  background-color: #ffe0b2;
  color: #f57c00;
}

.future-driver-link strong {
  color: #ff9800;
  font-weight: 600;
}

.future-driver-link:hover strong {
  color: #f57c00;
}

.driver-variable {
  color: #666;
  font-style: italic;
  margin-left: 4px;
}



.driver-description {
  color: #666;
  font-style: italic;
  margin-left: 4px;
}

.initial-conditions-container {
  padding: 4px;
}

.initial-condition-item {
  margin-bottom: 6px;
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #e8f5e9;
  border-left: 3px solid #4caf50;
  transition: all 0.2s ease;
}

.initial-condition-item:hover {
  background-color: #c8e6c9;
  border-left-color: #2e7d32;
}

.initial-condition-item:last-child {
  margin-bottom: 0;
}

.initial-condition-link {
  cursor: pointer;
  display: block;
  font-size: 0.9em;
  line-height: 1.4;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.initial-condition-link:hover {
  background-color: #c8e6c9;
  color: #2e7d32;
}

.initial-condition-link strong {
  color: #4caf50;
  font-weight: 600;
}

.initial-condition-link:hover strong {
  color: #2e7d32;
}

.condition-variable {
  color: #666;
  font-size: 0.8em;
  margin-left: 8px;
}



.condition-description {
  background-color: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  margin-top: 8px;
  white-space: pre-wrap;
  line-height: 1.5;
  max-height: 200px;
  overflow-y: auto;
}

.scenario-year-description {
  background-color: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  margin-top: 8px;
  white-space: pre-wrap;
  line-height: 1.5;
  max-height: 200px;
  overflow-y: auto;
}

.scenarios-container {
  padding: 4px;
}

.scenario-item {
  margin-bottom: 6px;
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #f8f9fa;
  border-left: 3px solid #ff6b35;
  transition: all 0.2s ease;
}

.scenario-item:hover {
  background-color: #fff3e0;
  border-left-color: #f57c00;
}

.scenario-link {
  cursor: pointer;
  color: #333;
  text-decoration: none;
  display: block;
  font-size: 0.9em;
  line-height: 1.4;
}

.scenario-link:hover {
  color: #ff6b35;
}

.scenario-title {
  color: #666;
  font-style: italic;
  margin-left: 4px;
}

.scenario-info {
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e0e0e0;
}

.scenario-info p {
  margin-bottom: 8px;
  font-size: 14px;
  line-height: 1.5;
}

.scenario-years {
  margin-top: 15px;
}

.year-section {
  margin-bottom: 20px;
}

.year-title {
  font-size: 16px;
  font-weight: 600;
  color: #4F46E5;
  margin-bottom: 8px;
  padding-bottom: 4px;
  border-bottom: 2px solid #E0E7FF;
}

.year-description {
  background-color: #f8f9fa;
  padding: 12px 16px;
  border-radius: 6px;
  border-left: 4px solid #4F46E5;
  white-space: pre-wrap;
  line-height: 1.6;
  font-size: 14px;
  color: #1F2937;
  max-height: 200px;
  overflow-y: auto;
}

.no-content {
  text-align: center;
  padding: 20px;
  color: #6b7280;
  font-style: italic;
}

.scenario-hypotheses {
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e0e0e0;
}

.hypotheses-title {
  font-size: 16px;
  font-weight: 600;
  color: #4F46E5;
  margin-bottom: 12px;
  padding-bottom: 4px;
  border-bottom: 2px solid #E0E7FF;
}

.years-title {
  font-size: 16px;
  font-weight: 600;
  color: #4F46E5;
  margin-bottom: 12px;
  padding-bottom: 4px;
  border-bottom: 2px solid #E0E7FF;
}

.hypotheses-list {
  margin-top: 10px;
}

.hypothesis-item {
  margin-bottom: 15px;
  padding: 12px;
  background-color: #f8f9fa;
  border-radius: 6px;
  border-left: 4px solid #ff6b35;
}

.hypothesis-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.hypothesis-name {
  font-weight: 600;
  color: #4F46E5;
  font-size: 14px;
}

.hypothesis-variable {
  font-size: 12px;
  color: #666;
  background-color: #e9ecef;
  padding: 2px 8px;
  border-radius: 12px;
}

.hypothesis-description {
  font-size: 13px;
  line-height: 1.5;
  color: #1F2937;
  white-space: pre-wrap;
}

.no-hypotheses {
  text-align: center;
  padding: 15px;
  color: #6b7280;
  font-style: italic;
}

.conclusions-container {
  padding: 4px;
}

.conclusion-item {
  margin-bottom: 6px;
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #f0f8ff;
  border-left: 3px solid #4caf50;
  transition: all 0.2s ease;
}

.conclusion-item:hover {
  background-color: #e8f5e8;
  border-left-color: #2e7d32;
}

.conclusion-item:last-child {
  margin-bottom: 0;
}

.conclusion-link {
  cursor: pointer;
  display: block;
  font-size: 0.9em;
  line-height: 1.4;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.conclusion-link:hover {
  background-color: #e8f5e8;
  color: #2e7d32;
}

.conclusion-link strong {
  color: #4caf50;
  font-weight: 600;
}

.conclusion-link:hover strong {
  color: #2e7d32;
}

.conclusion-title {
  color: #666;
  font-style: italic;
  margin-left: 4px;
}

.conclusion-info {
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e0e0e0;
}

.conclusion-description {
  background-color: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  margin-top: 8px;
  white-space: pre-wrap;
  line-height: 1.5;
  max-height: 200px;
  overflow-y: auto;
}

.conclusion-content {
  background-color: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  margin-top: 8px;
  line-height: 1.5;
  max-height: 300px;
  overflow-y: auto;
  white-space: pre-wrap;
}

.conclusion-section {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #e0e0e0;
}

.conclusion-section-title {
  font-size: 15px;
  font-weight: 600;
  color: #363636;
  margin-bottom: 8px;
  padding-bottom: 4px;
  border-bottom: 1px solid #e0e0e0;
}

.conclusion-section-content {
  font-size: 14px;
  line-height: 1.6;
  color: #1F2937;
  white-space: pre-wrap;
  max-height: 200px;
  overflow-y: auto;
}

.centered-cell {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  min-height: 40px;
}

/* Estilos específicos para centrar el encabezado de correo */
:deep(.b-table thead th.email-header-centered) {
  text-align: center !important;
  vertical-align: middle !important;
}

/* Centrar todos los encabezados de tabla */
:deep(.b-table thead th) {
  text-align: center !important;
  vertical-align: middle !important;
}

/* Asegurar que la columna de correo esté centrada */
:deep(.b-table thead th:nth-child(5)) {
  text-align: center !important;
  vertical-align: middle !important;
}

.modal-info {
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-section {
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.modal-section-title {
  font-size: 16px;
  font-weight: 600;
  color: #4F46E5;
  margin-bottom: 12px;
  padding-bottom: 4px;
  border-bottom: 2px solid #E0E7FF;
}

.modal-content {
  background-color: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  margin-top: 8px;
  white-space: pre-wrap;
  line-height: 1.5;
  max-height: 200px;
  overflow-y: auto;
}

.no-content {
  text-align: center;
  padding: 20px;
  color: #6b7280;
  font-style: italic;
}
</style>

<style>
/* CSS global para centrar todos los encabezados de tabla */
.b-table thead th {
  text-align: center !important;
  vertical-align: middle !important;
}

/* Específicamente para el encabezado de correo */
.b-table thead th.email-header-centered {
  text-align: center !important;
  vertical-align: middle !important;
}

/* Por si acaso, centrar también por contenido de texto */
.b-table thead th:contains("Correo"),
.b-table thead th:contains("Email"),
.b-table thead th:contains("E-mail") {
  text-align: center !important;
  vertical-align: middle !important;
}

/* Forzar fondo blanco y texto negro en el contenido de los modales de Buefy */
.modal-card-body {
  background: #fff !important;
  color: #222 !important;
}
.modal-card-body p,
.modal-card-body strong {
  color: #222 !important;
}
.modal-card-foot {
  background: #fff !important;
  border-top: 1px solid #eee !important;
}

:root .dialog .dialog-footer .button,
:root .dialog .dialog-footer .button.is-info,
:root .dialog .dialog-footer .button.is-danger,
:root .dialog .dialog-footer button,
:root .dialog .dialog-footer button.is-info,
:root .dialog .dialog-footer button.is-danger,
:root .dialog .dialog-footer .button[style],
:root .dialog .dialog-footer button[style],
:root .dialog .dialog-footer .button[style*="background"],
:root .dialog .dialog-footer button[style*="background"],
:root .dialog .dialog-footer .button[style*="color"],
:root .dialog .dialog-footer button[style*="color"] {
  background-color: #f14668 !important;
  color: #fff !important;
  border-color: #f14668 !important;
  box-shadow: none !important;
}
:root .dialog .dialog-footer .button:hover,
:root .dialog .dialog-footer .button.is-info:hover,
:root .dialog .dialog-footer .button.is-danger:hover,
:root .dialog .dialog-footer button:hover,
:root .dialog .dialog-footer button.is-info:hover,
:root .dialog .dialog-footer button.is-danger:hover,
:root .dialog .dialog-footer .button[style]:hover,
:root .dialog .dialog-footer button[style]:hover {
  background-color: #d12c4c !important;
  border-color: #d12c4c !important;
}

/* Para Vue 3 con scoped, usar ::v-deep */
::v-deep(.dialog .dialog-footer .button),
::v-deep(.dialog .dialog-footer .button.is-info),
::v-deep(.dialog .dialog-footer .button.is-danger),
::v-deep(.dialog .dialog-footer button),
::v-deep(.dialog .dialog-footer button.is-info),
::v-deep(.dialog .dialog-footer button.is-danger),
::v-deep(.dialog .dialog-footer .button[style]),
::v-deep(.dialog .dialog-footer button[style]) {
  background-color: #f14668 !important;
  color: #fff !important;
  border-color: #f14668 !important;
  box-shadow: none !important;
}
::v-deep(.dialog .dialog-footer .button:hover),
::v-deep(.dialog .dialog-footer .button.is-info:hover),
::v-deep(.dialog .dialog-footer .button.is-danger:hover),
::v-deep(.dialog .dialog-footer button:hover),
::v-deep(.dialog .dialog-footer button.is-info:hover),
::v-deep(.dialog .dialog-footer button.is-danger:hover),
::v-deep(.dialog .dialog-footer .button[style]:hover),
::v-deep(.dialog .dialog-footer button[style]:hover) {
  background-color: #d12c4c !important;
  border-color: #d12c4c !important;
}

/* CSS global para asegurar el color rojo del botón de cerrar */
.dialog .dialog-footer .button.is-danger {
  background-color: #f14668 !important;
  border-color: #f14668 !important;
  color: white !important;
  box-shadow: none !important;
}
.dialog .dialog-footer .button.is-danger:hover {
  background-color: #d12c4c !important;
  border-color: #d12c4c !important;
}
</style>