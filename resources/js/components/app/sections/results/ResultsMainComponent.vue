<template>
    <div class="main-content">
        <!-- Letrero informativo -->
        <info-banner-component
            :description="getResultsDescription"
        />
        
        <!-- MiniStepper eliminado -->
        <!-- Barra de filtros alineada con las columnas -->
    <div class="filters-bar" v-if="isAdmin">
    <div class="filters-container">
      <div class="filter-item">
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
      <div class="filter-item">
          <b-field label="Buscar por Nombre">
            <b-input
              v-model="filterFirstName"
              placeholder="Ingrese nombre..."
              type="text"
              icon="fas fa-search"
            />
          </b-field>
        </div>
      <div class="filter-item">
          <b-field label="Buscar por Apellido">
            <b-input
              v-model="filterLastName"
              placeholder="Ingrese apellido..."
              type="text"
              icon="fas fa-search"
            />
          </b-field>
        </div>
      <div class="filter-item filter-identificacion">
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
      <div class="filter-item" v-if="isAdmin">
          <b-field label="Filtrar por Estado">
            <b-select
              v-model="filterStatus"
              placeholder="Seleccionar estado..."
              icon="fas fa-filter"
            >
              <option value="">Todos</option>
              <option value="Completado">Completado</option>
              <option value="Sin terminar">Sin terminar</option>
            </b-select>
          </b-field>
        </div>
      <div class="filter-spacer">
          <!-- Espacio para la columna de email (sin filtro) -->
        </div>
      </div>
    </div>

    <!-- Tabla de resultados con columnas alineadas -->
  <div class="tabla-scroll-contenedor">
    <b-table :data="filteredUsers" :loading="isLoading" :striped="true" :hoverable="true" icon-pack="fas" table-class="tabla-grande">
        <b-table-column v-if="isAdmin" field="id" :label="textsStore.getText('results_section.table.id')" width="8%" centered v-slot="props">
          {{ props.row.id }}
        </b-table-column>
        <b-table-column field="first_name" :label="textsStore.getText('results_section.table.first_name')" width="10%" centered v-slot="props">
          {{ props.row.first_name }}
        </b-table-column>
        <b-table-column field="last_name" :label="textsStore.getText('results_section.table.last_name')" width="10%" centered v-slot="props">
          {{ props.row.last_name }}
        </b-table-column>
        <b-table-column field="document_id" :label="textsStore.getText('results_section.table.document_id')" width="10%" centered v-slot="props">
          {{ props.row.document_id }}
        </b-table-column>
        <b-table-column
          field="user"
          :label="textsStore.getText('results_section.table.email')"
          width="15%"
          centered
          class="email-column"
        >
          <template #default="props">
            {{ props.row.user }}
          </template>
        </b-table-column>
        <b-table-column field="route_name" label="Ruta" width="8%" centered v-slot="props">
          <div class="centered-cell">
            <b-tag type="is-success" size="is-medium">{{ props.row.route_name || 'N/A' }}</b-tag>
          </div>
        </b-table-column>
        <b-table-column field="variables_count" :label="textsStore.getText('results_section.table.variables_count') || 'Total Variables'" width="7%" centered v-slot="props">
          <div class="centered-cell">
            <b-tag class="custom-blue-tag" size="is-medium">{{ props.row.variables_count || 0 }}</b-tag>
          </div>
        </b-table-column>
        <b-table-column field="variables_list" :label="textsStore.getText('results_section.table.variables_list') || 'Variables Creadas'" width="18%" centered v-slot="props">
          <div v-if="props.row.variables_list && props.row.variables_list.length > 0" class="variables-container">
            <div v-for="(variable, index) in props.row.variables_list" :key="variable.id_variable" class="variable-item">
              <span class="variable-link" @click="showVariableDescription(variable)">
                <strong>{{ variable.id_variable }}:</strong> {{ variable.name_variable }}
              </span>
            </div>
          </div>
          <span v-else class="has-text-grey-light">Sin variables</span>
        </b-table-column>
      <b-table-column field="matriz" :label="textsStore.getText('results_section.table.matriz') || 'Matriz'" width="13%" centered v-slot="props">
        <div v-if="props.row.matriz && props.row.matriz.length > 0" class="matriz-container">
          <div class="matriz-summary">
            <span class="matriz-link" @click="showMatrizDescription(props.row.matriz, props.row.first_name, props.row.last_name, props.row.matriz_cruzada)">
              <strong>Matriz:</strong> 
              <span class="matriz-variables-count">{{ props.row.matriz.length }} variables</span>
            </span>
          </div>
        </div>
        <span v-else class="has-text-grey-light">Sin matriz</span>
      </b-table-column>
      <b-table-column field="grafica" label="Gráfica" width="10%" centered v-slot="props">
        <button class="button custom-blue-btn" @click="() => {showGraphicsDescription(props.row.matriz, props.row.first_name, props.row.last_name, props.row.matriz_cruzada); }">
          Ver Gráfica
        </button>
        </b-table-column>
        <b-table-column field="zone_analyses" :label="textsStore.getText('results_section.table.zone_analyses') || 'Análisis Mapa de Variables'" width="18%" centered v-slot="props">
          <div v-if="props.row.matriz && props.row.matriz.length > 0">
            <div v-for="(zone, zoneKey) in getVariablesByZone(props.row.matriz)" :key="zoneKey" class="zone-analyses-container">
              <div class="zone-analysis-item clickable-zone" @click="showZoneAnalysisDescription(getZoneAnalysis(props.row.zone_analyses, zoneKey), props.row.first_name, props.row.last_name, props.row.matriz, zoneKey)">
                <strong>{{ zoneKey }}:</strong>
                <span v-if="zone.length > 0">
                  <span v-for="variable in zone" :key="variable.id_variable" class="zone-variable-code">
                    {{ variable.id_variable }}
                  </span>
                </span>
                <span v-else class="has-text-grey-light">Sin variables</span>
              </div>
            </div>
          </div>
          <span v-else class="has-text-grey-light">Sin análisis</span>
        </b-table-column>
        <b-table-column field="future_drivers" :label="textsStore.getText('results_section.table.future_drivers') || 'Direccionadores de Futuro'" width="18%" centered v-slot="props">
          <div v-if="props.row.future_drivers && props.row.future_drivers.length > 0" class="future-drivers-container">
            <div v-for="(driver, index) in props.row.future_drivers" :key="driver.id" class="future-driver-item">
              <span class="future-driver-link" @click="showFutureDriverDescription(driver)">
                <strong>{{ driver.variable_name }}:</strong> 
                <span class="driver-variable">H0 y H1</span>
              </span>
            </div>
          </div>
          <span v-else class="has-text-grey-light">Sin direccionadores</span>
        </b-table-column>
      <b-table-column field="schwartz_graph" label="Ejes Schwartz" width="10%" centered v-slot="props">
        <button class="button custom-orange-btn" @click="showSchwartzModal(props.row.scenarios, props.row.first_name, props.row.last_name, props.row.future_drivers)">
          Ver Ejes Schwartz
        </button>
        </b-table-column>
        <b-table-column field="initial_conditions" :label="textsStore.getText('results_section.table.initial_conditions') || 'Condiciones Iniciales'" width="18%" centered v-slot="props">
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
        <b-table-column field="scenarios" :label="textsStore.getText('results_section.table.scenarios') || 'Escenarios'" width="18%" centered v-slot="props">
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
        <b-table-column field="conclusions" :label="textsStore.getText('results_section.table.conclusions') || 'Conclusiones'" width="18%" centered v-slot="props">
          <div v-if="props.row.conclusions && props.row.conclusions.length > 0" class="conclusions-container">
            <button class="button custom-blue-btn" @click="showConclusionDescription(null, props.row.conclusions, props.row.first_name, props.row.last_name)">
              Ver Conclusiones
            </button>
          </div>
          <span v-else class="has-text-grey-light">Sin conclusiones</span>
        </b-table-column>
        <b-table-column field="status" :label="textsStore.getText('results_section.table.status') || 'Estado'" width="10%" centered v-slot="props">
          <div class="centered-cell">
            <b-tag 
              :type="props.row.status === 'Completado' ? 'is-success' : 'is-warning'" 
              size="is-medium"
            >
              {{ props.row.status || 'Sin terminar' }}
            </b-tag>
          </div>
        </b-table-column>
      <b-table-column label="Imprimir" width="8%" centered v-slot="props">
        <div class="pdf-button-container">
          <button
            class="button is-primary is-light pdf-button"
            @click="imprimirUsuario(props.row)"
            :disabled="loadingPdfId === (props.row.id + '-' + (props.row.route_id || 'default'))"
          >
            <span class="button-content">
              <span v-if="loadingPdfId === (props.row.id + '-' + (props.row.route_id || 'default'))" class="custom-spinner"></span>
              <i v-else class="fas fa-file-pdf"></i>
            </span>
          </button>
        </div>
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
          <p class="modal-card-title">Detalles del Análisis de Zona - {{ selectedZoneAnalysisUser }}</p>
          <button class="delete" aria-label="close" @click="showZoneAnalysisModal = false"></button>
        </header>
        <section class="modal-card-body">
          <div class="modal-info">
            <p><strong>Usuario:</strong> {{ selectedZoneAnalysisUser }}</p>
            <p><strong>Zona:</strong> {{ selectedZoneAnalysis?.zone_name }}</p>
            <p><strong>Puntaje:</strong> {{ selectedZoneAnalysis?.score }}</p>
            
            <div v-if="getVariablesByZone(selectedZoneAnalysisMatriz)[selectedZoneAnalysisZoneKey] && getVariablesByZone(selectedZoneAnalysisMatriz)[selectedZoneAnalysisZoneKey].length > 0">
              <p><strong>Variables en esta zona:</strong></p>
              <div class="modal-content">
                <div v-for="variable in getVariablesByZone(selectedZoneAnalysisMatriz)[selectedZoneAnalysisZoneKey]" :key="variable.id_variable" class="zone-variable-item">
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
            <p><strong>Variable:</strong> {{ selectedFutureDriver?.variable_name }}</p>
          </div>
          
          <div class="modal-section">
            <h4 class="modal-section-title">Hipótesis H0</h4>
            <div class="modal-content">
              {{ selectedFutureDriver?.h0_description || 'Sin descripción disponible para H0' }}
            </div>
          </div>
          
          <div class="modal-section">
            <h4 class="modal-section-title">Hipótesis H1</h4>
            <div class="modal-content">
              {{ selectedFutureDriver?.h1_description || 'Sin descripción disponible para H1' }}
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
              <div v-for="(hypothesis, index) in selectedScenario.hypotheses" :key="index" class="hypothesis-item">
                <div class="hypothesis-description">
                  <strong>Hipótesis {{ index + 1 }}:</strong> {{ hypothesis }}
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
          <p class="modal-card-title">Conclusiones de {{ selectedConclusionUser }}</p>
          <button class="delete" aria-label="close" @click="showConclusionModal = false"></button>
        </header>
        <section class="modal-card-body">
          <div v-if="selectedConclusions && selectedConclusions.length > 0" class="conclusions-modal-content">
            <div v-for="(conclusion, index) in selectedConclusions" :key="conclusion.id" class="conclusion-section">
              <h4 class="conclusion-title">{{ conclusion.title }}</h4>
              
              <div v-if="conclusion.component_practice" class="modal-section">
                <h5 class="modal-section-title">Componente Práctico</h5>
                <div class="modal-content">
                  {{ conclusion.component_practice }}
                </div>
              </div>
              
              <div v-if="conclusion.actuality" class="modal-section">
                <h5 class="modal-section-title">Actualidad</h5>
                <div class="modal-content">
                  {{ conclusion.actuality }}
                </div>
              </div>
              
              <div v-if="conclusion.aplication" class="modal-section">
                <h5 class="modal-section-title">Aplicación</h5>
                <div class="modal-content">
                  {{ conclusion.aplication }}
                </div>
              </div>
              
              <div v-if="!conclusion.component_practice && !conclusion.actuality && !conclusion.aplication" class="no-content">
                <p class="has-text-grey-light">Sin contenido disponible</p>
              </div>
              
              <hr v-if="index < selectedConclusions.length - 1" class="conclusion-divider">
            </div>
          </div>
          <div v-else class="no-content">
            <p class="has-text-grey-light">No hay conclusiones disponibles</p>
          </div>
        </section>
        <footer class="modal-card-foot">
          <button class="button is-danger" @click="showConclusionModal = false">Cerrar</button>
        </footer>
      </div>
    </div>

  <!-- Modal funcional de Bulma para mostrar la matriz -->
  <div v-if="showMatrizModal" class="modal is-active">
    <div class="modal-background" @click="showMatrizModal = false"></div>
    <div class="modal-card matriz-modal-card">
      <header class="modal-card-head">
        <p class="modal-card-title">Matriz de {{ selectedMatrizUser }}</p>
        <button class="delete" aria-label="close" @click="showMatrizModal = false"></button>
      </header>
      <section class="modal-card-body">
        <div v-if="selectedMatriz && selectedMatriz.length > 0" class="matriz-modal-content">
          <div class="matriz-table-container">
            <table class="matriz-table">
              <thead>
                <tr>
                  <th class="matriz-header-cell matriz-header-bg matriz-codigo-cell">CÓDIGO</th>
                  <th class="matriz-header-cell matriz-header-bg matriz-nombre-cell">NOMBRE</th>
                  <th v-for="variable in matrizOrderedVariables" :key="'header-' + variable.id_variable" class="matriz-header-cell matriz-header-bg matriz-data-cell matriz-col-align">
                    {{ variable.id_variable }}
                  </th>
                  <th class="matriz-header-cell matriz-header-bg matriz-total-cell">TOTAL DEPENDENCIA</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(varOrigen, rowIdx) in matrizOrderedVariables" :key="'row-' + varOrigen.id_variable" :class="{'matriz-row-alt': rowIdx % 2 === 1}">
                  <td class="matriz-cell-header matriz-header-bg matriz-codigo-cell matriz-col-align">{{ varOrigen.id_variable }}</td>
                  <td class="matriz-cell-header matriz-header-bg matriz-nombre-cell">{{ varOrigen.name_variable }}</td>
                  <td v-for="(varDestino, colIdx) in matrizOrderedVariables" :key="'cell-' + varOrigen.id_variable + '-' + varDestino.id_variable" :class="['matriz-cell-center', 'matriz-col-align', 'matriz-data-cell', getCellClassMatriz(varOrigen.id_variable, varDestino.id_variable)]">
                    <div :class="getColorClaseMatriz(getCellValueMatriz(varOrigen.id_variable, varDestino.id_variable)) + '-modern'">
                      {{ getCellValueMatriz(varOrigen.id_variable, varDestino.id_variable) }}
                    </div>
                  </td>
                  <td class="matriz-total-cell matriz-total-bg matriz-col-align">{{ getTotalDependencia(varOrigen.id_variable) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td class="matriz-total-header-cell matriz-total-bg matriz-col-align matriz-codigo-cell" colspan="2">TOTAL INFLUENCIA</td>
                  <td v-for="variable in matrizOrderedVariables" :key="'footer-' + variable.id_variable" class="matriz-total-cell matriz-total-bg matriz-col-align matriz-data-cell">
                    {{ getTotalInfluencia(variable.id_variable) }}
                  </td>
                  <td class="matriz-total-cell matriz-total-bg matriz-col-align">{{ getSumaTotalDependencia }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
          <!-- Tabla de resumen de dependencia e influencia -->
          <div class="matriz-resumen-modal">
            <table class="resumen-table-modal">
              <thead>
                <tr>
                  <th>RESUMEN</th>
                  <th v-for="variable in matrizOrderedVariables" :key="'resumen-' + variable.id_variable">{{ variable.id_variable }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>DEPENDENCIA</td>
                  <td v-for="variable in matrizOrderedVariables" :key="'dep-' + variable.id_variable">{{ getTotalDependencia(variable.id_variable) }}</td>
                </tr>
                <tr>
                  <td>INFLUENCIA</td>
                  <td v-for="variable in matrizOrderedVariables" :key="'inf-' + variable.id_variable">{{ getTotalInfluencia(variable.id_variable) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div v-else class="no-content">
          <p class="has-text-grey-light">No hay datos de matriz disponibles</p>
        </div>
      </section>
      <footer class="modal-card-foot">
        <button class="button is-danger" @click="showMatrizModal = false">Cerrar</button>
      </footer>
    </div>
  </div>
  <!-- Modal para la gráfica -->
  <div v-if="showGraphicsModal" class="modal is-active">
    <div class="modal-background" @click="showGraphicsModal = false"></div>
    <div class="modal-card" style="width: 900px; max-width: 98vw;">
      <header class="modal-card-head">
        <p class="modal-card-title">Gráfica de {{ selectedGraphicsUser }}</p>
        <button class="delete" aria-label="close" @click="showGraphicsModal = false"></button>
      </header>
      <section class="modal-card-body">
        <GraphicsMainComponent :external-data="selectedGraphicsData" :readonly="true" />
      </section>
      <footer class="modal-card-foot">
        <button class="button is-danger" @click="showGraphicsModal = false">Cerrar</button>
      </footer>
    </div>
  </div>
  <!-- Modal funcional de Bulma para mostrar los Ejes de Schwartz -->
  <div v-if="showSchwartzModalRef" class="modal is-active">
    <div class="modal-background" @click="showSchwartzModalRef = false"></div>
    <div class="modal-card" style="width: 900px; max-width: 98vw;">
      <header class="modal-card-head">
        <p class="modal-card-title">Ejes de Peter Schwartz de {{ selectedSchwartzUser }}</p>
        <button class="delete" aria-label="close" @click="showSchwartzModalRef = false"></button>
      </header>
      <section class="modal-card-body">
        <SchwartzMainComponent :external-scenarios="selectedSchwartzScenarios" :external-hypotheses="selectedSchwartzHypotheses" :readonly="true" size="medium" />
      </section>
      <footer class="modal-card-foot">
        <button class="button is-danger" @click="showSchwartzModalRef = false">Cerrar</button>
      </footer>
    </div>
  </div>
  </div>

    <!-- Gráficas ocultas para impresión PDF -->
  <div
    v-for="user in filteredUsers"
    :key="'grafica-variables-' + user.id + '-' + (user.route_id || 'default')"
    :id="'grafica-variables-' + user.id + '-' + (user.route_id || 'default')"
    style="position: absolute; left: -9999px; top: 0; width: 800px; height: 560px; background: white;"
    class="pdf-graphic-container"
  >
    <GraphicsMainComponent :external-data="convertMatrizToGraphicsData(user.matriz, user.matriz_cruzada)" :readonly="true" />
  </div>

  <!-- Canvas oculto para la gráfica de Schwartz (para PDF) -->
  <div
    v-for="user in filteredUsers"
    :key="'grafica-schwartz-' + user.id + '-' + (user.route_id || 'default')"
    :id="'grafica-schwartz-' + user.id + '-' + (user.route_id || 'default')"
    style="position: absolute; left: -9999px; top: 0; width: 800px; height: 640px; background: white;"
    class="pdf-graphic-container"
  >
    <SchwartzChartComponent :scenarios="user.scenarios" :hypotheses="convertFutureDriversToHypotheses(user.future_drivers)" :readonly="true" />
  </div>

  <!-- Diagrama de Schwartz oculto solo para PDF -->
  <div
    v-for="user in filteredUsers"
    :key="'schwartz-diagram-' + user.id + '-' + (user.route_id || 'default')"
    :id="'schwartz-diagram-' + user.id + '-' + (user.route_id || 'default')"
    style="position: absolute; left: -9999px; top: 0; width: 900px; height: 700px; background: white;"
  >
    <SchwartzMainComponent
      :scenarios="user.scenarios"
      :hypotheses="convertFutureDriversToHypotheses(user.future_drivers)"
      :readonly="true"
      :pdfMode="true"
      style="width: 900px; height: 700px;"
    />
  </div>



</template>
<script>
import { jsPDF } from 'jspdf';
import html2canvas from 'html2canvas';
import autoTable from 'jspdf-autotable';
import { createApp, nextTick } from 'vue';
import SchwartzPDFEditableCanvas from '../Schwartz/SchwartzPDFEditableCanvas.vue';

import { useSectionStore } from '../../../../stores/section';
import { useTextsStore } from '../../../../stores/texts';
import { useResultsStore } from '../../../../stores/results';
import { useTraceabilityStore } from '../../../../stores/traceability';
import { storeToRefs } from 'pinia';
import { ref, computed } from 'vue';
import GraphicsMainComponent from '../graphics/GraphicsMainComponent.vue';
import SchwartzMainComponent from '../Schwartz/SchwartzMainComponent.vue';
import SchwartzChartComponent from '../Schwartz/SchwartzChartComponent.vue';
import SchwartzPDFComponent from '../Schwartz/SchwartzPDFComponent.vue';
import InfoBannerComponent from '../../ui/InfoBannerComponent.vue';

export default {
    components: {
        GraphicsMainComponent,
        SchwartzMainComponent,
        SchwartzChartComponent,
        SchwartzPDFComponent,
        SchwartzPDFEditableCanvas,
        InfoBannerComponent,
    },
    setup() {
        const sectionStore = useSectionStore();
        const textsStore = useTextsStore();
        const resultsStore = useResultsStore();
        const traceabilityStore = useTraceabilityStore();
        const { users, isLoading } = storeToRefs(resultsStore);

        const user = JSON.parse(localStorage.getItem('user')) || {};
        const isAdmin = user.role === 1;

        const filterId = ref('');
        const filterFirstName = ref('');
        const filterLastName = ref('');
        const filterDocumentId = ref('');
        const filterStatus = ref('');

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
                const statusMatch = !filterStatus.value || user.status === filterStatus.value;
                
                return idMatch && firstNameMatch && lastNameMatch && documentIdMatch && statusMatch;
            });
        });

        const showVariableModal = ref(false);
        const showScenarioModal = ref(false);
        const showConclusionModal = ref(false);
        const showZoneAnalysisModal = ref(false);
        const showFutureDriverModal = ref(false);
        const showInitialConditionModal = ref(false);
        const showMatrizModal = ref(false);
        const selectedVariable = ref(null);
        const selectedScenario = ref(null);
        const selectedConclusion = ref(null);
        const selectedConclusions = ref([]);
        const selectedConclusionUser = ref('');
        const selectedZoneAnalysis = ref(null);
        const selectedZoneAnalysisUser = ref('');
        const selectedZoneAnalysisMatriz = ref(null);
        const selectedZoneAnalysisZoneKey = ref('');
        const selectedFutureDriver = ref(null);
        const selectedInitialCondition = ref(null);
        const selectedMatriz = ref(null);
        const selectedMatrizUser = ref('');
        const selectedMatrizCruzada = ref([]);

        const loadingPdfId = ref(null);



        function showVariableDescription(variable) {
            selectedVariable.value = variable;
            showVariableModal.value = true;
        }

        function showZoneAnalysisDescription(analysis, firstName, lastName, matriz, zoneKey) {
            selectedZoneAnalysis.value = analysis;
            selectedZoneAnalysisUser.value = `${firstName} ${lastName}`;
            selectedZoneAnalysisMatriz.value = matriz;
            selectedZoneAnalysisZoneKey.value = zoneKey;
            showZoneAnalysisModal.value = true;
        }

        function getZoneAnalysis(zoneAnalyses, zoneKey) {
            if (!zoneAnalyses || !Array.isArray(zoneAnalyses)) return null;

            return zoneAnalyses.find(analysis => analysis.zone_name === zoneKey) || null;
        }

        function showConclusionDescription(conclusion, conclusions, firstName, lastName) {
            selectedConclusions.value = conclusions || [];
            selectedConclusionUser.value = `${firstName} ${lastName}`;
            showConclusionModal.value = true;
        }

        function showFutureDriverDescription(driver) {
            selectedFutureDriver.value = driver;
            showFutureDriverModal.value = true;
        }

        function showInitialConditionDescription(condition) {
            selectedInitialCondition.value = condition;
            showInitialConditionModal.value = true;
        }

        function showScenarioDescription(scenario) {
            selectedScenario.value = scenario;
            showScenarioModal.value = true;
        }

        function showMatrizDescription(matriz, firstName, lastName, matrizCruzada = []) {
            selectedMatriz.value = matriz;
            selectedMatrizUser.value = `${firstName} ${lastName}`;
            selectedMatrizCruzada.value = matrizCruzada;
            showMatrizModal.value = true;
        }

        function getScenarioHypotheses(scenario, futureDrivers) {
            if (!scenario.hypotheses || !scenario.hypotheses.length) return 'Sin hipótesis relacionadas';

            return scenario.hypotheses.map(hypothesis => {
                const hypothesisName = hypothesis.name_hypothesis || '';
                const secondaryType = hypothesis.secondary_hypotheses || '';
                const variableName = hypothesis.variable_name || '';

                let formattedHypothesis = '';
                if (hypothesisName === 'H1') {
                    if (secondaryType === 'H1') formattedHypothesis = 'Hipótesis 1+';
                    else if (secondaryType === 'H0') formattedHypothesis = 'Hipótesis 1-';
                } else if (hypothesisName === 'H2') {
                    if (secondaryType === 'H1') formattedHypothesis = 'Hipótesis 2+';
                    else if (secondaryType === 'H0') formattedHypothesis = 'Hipótesis 2-';
                } else {
                    
                    formattedHypothesis = hypothesisName;
                }

                return formattedHypothesis;
            }).join(', ');
        }

        const matrizOrderedVariables = computed(() => {
          if (!selectedMatriz.value) return [];
          
          return [...selectedMatriz.value].sort((a, b) => {
            const numA = parseInt(a.id_variable.replace('V', ''));
            const numB = parseInt(b.id_variable.replace('V', ''));
            return numA - numB;
          });
        });

        const matrizMap = computed(() => {
          if (!selectedMatriz.value || !selectedMatrizCruzada.value) return {};
          const map = {};
          selectedMatrizCruzada.value.forEach(item => {
            map[`${item.origen}-${item.destino}`] = item.valor;
          });
          return map;
        });

        function getCellValueMatriz(origen, destino) {
          if (origen === destino) return 'X';
          const key = `${origen}-${destino}`;
          return matrizMap.value[key] !== undefined ? matrizMap.value[key] : 0;
        }

        function getCellClassMatriz(origen, destino) {
          if (origen === destino) {
            return 'matriz-cell-diagonal';
          }
          return 'matriz-cell-editable';
        }

        function getColorClaseMatriz(valor) {
          if (typeof valor === 'string') return '';
          switch(valor) {
            case 3: return 'matriz-value-strong';
            case 2: return 'matriz-value-medium';
            case 1: return 'matriz-value-weak';
            default: return 'matriz-value-none';
          }
        }

        function getTotalDependencia(origen) {
          let total = 0;
          matrizOrderedVariables.value.forEach(destino => {
            if (origen !== destino.id_variable) {
              total += Number(getCellValueMatriz(origen, destino.id_variable)) || 0;
            }
          });
          return total;
        }

        function getTotalInfluencia(destino) {
          let total = 0;
          matrizOrderedVariables.value.forEach(origen => {
            if (origen.id_variable !== destino) {
              total += Number(getCellValueMatriz(origen.id_variable, destino)) || 0;
            }
          });
          return total;
        }

        const getSumaTotalDependencia = computed(() => {
          let suma = 0;
          matrizOrderedVariables.value.forEach(origen => {
            suma += getTotalDependencia(origen.id_variable);
          });
          return suma;
        });

        const showGraphicsModal = ref(false);
        const selectedGraphicsData = ref([]);
        const selectedGraphicsUser = ref('');
        function showGraphicsDescription(matriz, firstName, lastName, matrizCruzada = []) {
          
          let graphicsData = [];
          if (matriz && matriz.length > 0) {
            if (matrizCruzada && matrizCruzada.length > 0) {
              
              const variables = [...new Set(matrizCruzada.map(m => m.origen))].sort();
              const matrizMap = {};
              matrizCruzada.forEach(item => {
                matrizMap[`${item.origen}-${item.destino}`] = item.valor;
              });
              graphicsData = variables.map(variable => {
                const dependencia = variables.reduce((sum, destino) => {
                  if (variable !== destino) {
                    return sum + (matrizMap[`${variable}-${destino}`] || 0);
                  }
                  return sum;
                }, 0);
                const influencia = variables.reduce((sum, origen) => {
                  if (origen !== variable) {
                    return sum + (matrizMap[`${origen}-${variable}`] || 0);
                  }
                  return sum;
                }, 0);
                return {
                  id_variable: variable,
                  dependencia: dependencia,
                  influencia: influencia
                };
              });
            } else {
              
              graphicsData = matriz.map(item => ({
                id_variable: item.id_variable,
                dependencia: item.dependencia || 0,
                influencia: item.influencia || 0
              }));
            }
          }
          selectedGraphicsData.value = graphicsData;
          selectedGraphicsUser.value = `${firstName} ${lastName}`;
          showGraphicsModal.value = true;
        }

        const showSchwartzModalRef = ref(false);
        const selectedSchwartzScenarios = ref([]);
        const selectedSchwartzUser = ref('');
        const selectedSchwartzHypotheses = ref([]);
        function showSchwartzModal(scenarios, firstName, lastName, futureDrivers = []) {
          
          let normScenarios = Array.isArray(scenarios) ? scenarios.slice(0, 4) : [];
          normScenarios = [0, 1, 2, 3].map(i => {
            const s = normScenarios[i] || {};
            return {
              ...s,
              texto: s.texto || s.titulo || ''
            };
          });
          selectedSchwartzScenarios.value = normScenarios;
          selectedSchwartzUser.value = `${firstName} ${lastName}`;

          // Mapear los future_drivers a la estructura esperada por SchwartzMainComponent
          const mappedHypotheses = futureDrivers.slice(0, 2).map(driver => ({
            descriptionH0: driver.h0_description || '',
            descriptionH1: driver.h1_description || ''
          }));
          
          selectedSchwartzHypotheses.value = mappedHypotheses;
          showSchwartzModalRef.value = true;
        }

        function convertMatrizToGraphicsData(matriz, matrizCruzada) {
          
          if (matriz && matriz.length > 0) {
            if (matrizCruzada && matrizCruzada.length > 0) {
              
              const variables = [...new Set(matrizCruzada.map(m => m.origen))].sort();
              const matrizMap = {};
              matrizCruzada.forEach(item => {
                matrizMap[`${item.origen}-${item.destino}`] = item.valor;
              });
              return variables.map(variable => {
                const dependencia = variables.reduce((sum, destino) => {
                  if (variable !== destino) {
                    return sum + (matrizMap[`${variable}-${destino}`] || 0);
                  }
                  return sum;
                }, 0);
                const influencia = variables.reduce((sum, origen) => {
                  if (origen !== variable) {
                    return sum + (matrizMap[`${origen}-${variable}`] || 0);
                  }
                  return sum;
                }, 0);
                return {
                  id_variable: variable,
                  dependencia: dependencia,
                  influencia: influencia
                };
              });
            } else {
              
              return matriz.map(item => ({
                id_variable: item.id_variable,
                dependencia: item.dependencia || 0,
                influencia: item.influencia || 0
              }));
            }
          }
          return [];
        }

        function convertFutureDriversToHypotheses(futureDrivers) {
          // Convertir future_drivers al formato esperado por los componentes de Schwartz estándar
          if (!futureDrivers || !Array.isArray(futureDrivers) || futureDrivers.length === 0) {
            return [];
          }
          
          // Tomar los primeros 2 future_drivers (las 2 variables principales)
          const mainDrivers = futureDrivers.slice(0, 2);
          
          return mainDrivers.map(driver => ({
            descriptionH0: driver.h0_description || '',
            descriptionH1: driver.h1_description || ''
          }));
        }

        function convertFutureDriversToPDFFormat(futureDrivers) {
          // Convertir future_drivers al formato esperado por SchwartzPDFEditableCanvas
          if (!futureDrivers || !Array.isArray(futureDrivers) || futureDrivers.length === 0) {
            return [];
          }
          
          // Tomar los primeros 2 future_drivers (las 2 variables principales)
          const mainDrivers = futureDrivers.slice(0, 2);
          
          const pdfFormat = [];
          mainDrivers.forEach((driver, index) => {
            if (driver.h0_description) {
              pdfFormat.push({
                name_hypothesis: `H${index + 1}`,
                secondary_hypotheses: 'H0',
                description: driver.h0_description,
                variable_name: driver.variable_name || ''
              });
            }
            if (driver.h1_description) {
              pdfFormat.push({
                name_hypothesis: `H${index + 1}`,
                secondary_hypotheses: 'H1',
                description: driver.h1_description,
                variable_name: driver.variable_name || ''
              });
            }
          });
          
          return pdfFormat;
        }

        async function imprimirUsuario(usuario) {
          
          const uniqueId = `${usuario.id}-${usuario.route_id || 'default'}`;
          loadingPdfId.value = uniqueId;
          await nextTick(); 

          let hiddenContainer = document.getElementById('pdf-graphics-container');
          if (!hiddenContainer) {
            hiddenContainer = document.createElement('div');
            hiddenContainer.id = 'pdf-graphics-container';
            hiddenContainer.style.cssText = `
              position: fixed !important;
              top: -9999px !important;
              left: -9999px !important;
              width: 0 !important;
              height: 0 !important;
              overflow: hidden !important;
              visibility: hidden !important;
              opacity: 0 !important;
              pointer-events: none !important;
              z-index: -9999 !important;
              transform: translate3d(-9999px, -9999px, 0) !important;
            `;
            document.body.appendChild(hiddenContainer);
          }

          const graphicsElementIds = [
            `grafica-variables-${uniqueId}`,
            `grafica-matriz-${uniqueId}`,
            `grafica-schwartz-${uniqueId}`
          ];
          const originalParents = {};
          const originalNextSiblings = {};

          function moveGraphicsToHiddenContainer() {
            graphicsElementIds.forEach(id => {
              const el = document.getElementById(id);
              if (el && el.parentNode !== hiddenContainer) {
                originalParents[id] = el.parentNode;
                originalNextSiblings[id] = el.nextSibling;
                hiddenContainer.appendChild(el);
              }
            });
          }

          function restoreGraphicsToOriginal() {
            graphicsElementIds.forEach(id => {
              const el = document.getElementById(id);
              if (el && originalParents[id]) {
                if (
                  originalNextSiblings[id] &&
                  originalNextSiblings[id].parentNode === originalParents[id]
                ) {
                  originalParents[id].insertBefore(el, originalNextSiblings[id]);
                } else {
                  originalParents[id].appendChild(el);
                }
              }
            });
          }

          const restoreGraphics = () => {
            graphicsElementIds.forEach(id => {
              const element = document.getElementById(id);
              if (element && element._originalStyles) {
                Object.keys(element._originalStyles).forEach(property => {
                  element.style[property] = element._originalStyles[property];
                });
                delete element._originalStyles;
              }
            });
          };

          try {
            
            await new Promise(resolve => requestAnimationFrame(resolve));
            
            moveGraphicsToHiddenContainer();

            await new Promise(resolve => requestAnimationFrame(resolve));
            
            const doc = new jsPDF({ unit: 'pt', format: 'a4' });
            
            const margin = 50;
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const contentWidth = pageWidth - (2 * margin);
            const contentHeight = pageHeight - (2 * margin);
            
            let y = margin + 40;

            const checkPageBreak = (requiredSpace = 50, title = '') => {
              const currentY = y;
              const pageHeight = doc.internal.pageSize.getHeight();
              const bottomMargin = margin;
              const availableSpace = pageHeight - bottomMargin - currentY;

              // Solo crear nueva página si realmente no hay espacio suficiente
              // Para tablas pequeñas, ser más permisivo
              const safetyMargin = requiredSpace > 200 ? 20 : 50;
              if (requiredSpace > availableSpace - safetyMargin) {
                doc.addPage();
                y = margin + 20; // Reducir el margen superior
                return true;
              }
              return false;
            };

            const checkTablePageBreak = (tableData, title = '') => {
              if (!tableData || tableData.length === 0) return false;
              
              const titleHeight = 20; // Reducir altura del título
              const tableHeaderHeight = 20; // Reducir altura del header
              const rowHeight = Math.min(16, Math.max(10, 500 / tableData.length)); // Altura más compacta
              const tableFooterHeight = 15; // Reducir altura del footer
              const spacing = 10; // Reducir espaciado
              
              const estimatedTableHeight = titleHeight + tableHeaderHeight + (tableData.length * rowHeight) + tableFooterHeight + spacing;
              
              // Solo crear nueva página si la tabla es muy grande
              if (estimatedTableHeight > 300) {
                return checkPageBreak(estimatedTableHeight, title);
              }
              return false;
            };

            const checkGraphicPageBreak = (graphicHeight, title = '') => {
              const titleHeight = 20; // Reducir altura del título
              const graphicSpacing = 15; // Reducir espaciado
              
              const totalHeight = titleHeight + graphicHeight + graphicSpacing;
              
              // Solo crear nueva página si la gráfica es muy grande
              if (totalHeight > 400) {
                return checkPageBreak(totalHeight, title);
              }
              return false;
            };

            const optimizeTableLayout = (tableData, maxRowsPerPage = 15) => {
              if (!tableData || tableData.length <= maxRowsPerPage) {
                return [tableData];
              }
              
              const chunks = [];
              for (let i = 0; i < tableData.length; i += maxRowsPerPage) {
                chunks.push(tableData.slice(i, i + maxRowsPerPage));
              }
              return chunks;
            };

            const prepareGraphics = () => {
              
              const graphicsElements = [
                `grafica-variables-${uniqueId}`,
                `grafica-matriz-${uniqueId}`,
                `grafica-schwartz-${uniqueId}`
              ];
              
              graphicsElements.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                  
                  element._originalStyles = {
                    display: element.style.display,
                    position: element.style.position,
                    left: element.style.left,
                    top: element.style.top,
                    opacity: element.style.opacity,
                    visibility: element.style.visibility,
                    background: element.style.background,
                    border: element.style.border,
                    zIndex: element.style.zIndex,
                    transform: element.style.transform,
                    pointerEvents: element.style.pointerEvents
                  };

                  element.style.display = 'block';
                  element.style.position = 'absolute';
                  element.style.left = '0';
                  element.style.top = '0';
                  element.style.opacity = '1';
                  element.style.visibility = 'visible';
                  element.style.background = '#ffffff';
                  element.style.border = '1px solid #ccc';
                  element.style.zIndex = '-9999'; 
                  element.style.pointerEvents = 'none'; 
                  element.style.transform = 'translate3d(-9999px, -9999px, 0)'; 
                }
              });
            };

            prepareGraphics();

            const waitForGraphics = async () => {
              const graphicsElements = [
                `grafica-variables-${uniqueId}`,
                `grafica-matriz-${uniqueId}`,
                `grafica-schwartz-${uniqueId}`
              ];
              
              for (const id of graphicsElements) {
                const element = document.getElementById(id);
                if (element) {
                  
                  await new Promise(resolve => setTimeout(resolve, 1000));

                  const canvas = element.querySelector('canvas');
                  if (canvas) {
                  }
                }
              }
            };

            await waitForGraphics();

          const captureGraphic = async (elementId, title, y, createNewPage = false, options = {}) => {
            const element = document.getElementById(elementId);
            if (!element) {
              return false;
            }

            element.style.display = 'block';
            element.style.position = 'absolute';
            element.style.left = '-9999px';
            element.style.top = '0';
            element.style.opacity = '1';
            element.style.visibility = 'visible';
            element.style.background = '#ffffff';
            element.style.zIndex = '9999';

            // Configuración dinámica basada en el tipo de gráfica
            let captureWidth = 800;
            let captureHeight = 560;
            let maxWidth = 600;
            let maxHeight = 400;
            
            if (elementId.includes('schwartz')) {
              captureWidth = 900;
              captureHeight = 700;
              maxWidth = 800; 
              maxHeight = 600;
            } else if (elementId.includes('matriz')) {
              captureWidth = 900;
              captureHeight = 600;
              maxWidth = 700;
              maxHeight = 500;
            } else {
              // Gráfica de variables
              captureWidth = 800;
              captureHeight = 560;
              maxWidth = 600;
              maxHeight = 400;
            }

            // Tiempos de espera optimizados
            if (elementId.includes('schwartz')) {
              await new Promise(resolve => setTimeout(resolve, 3000)); 
            } else if (elementId.includes('matriz')) {
              await new Promise(resolve => setTimeout(resolve, 2000));
            } else {
              await new Promise(resolve => setTimeout(resolve, 1500)); 
            }
            
            try {
              const canvas = await html2canvas(element, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
                width: captureWidth,
                height: captureHeight,
                scrollX: 0,
                scrollY: 0,
                windowWidth: captureWidth,
                windowHeight: captureHeight,
                onclone: (clonedDoc) => {
                  
                  const clonedElement = clonedDoc.getElementById(elementId);
                  if (clonedElement) {
                    clonedElement.style.background = '#ffffff';
                    clonedElement.style.display = 'block';
                    clonedElement.style.opacity = '1';
                    clonedElement.style.visibility = 'visible';
                    clonedElement.style.position = 'relative';
                    clonedElement.style.left = '0';
                    clonedElement.style.top = '0';
                    clonedElement.style.width = `${captureWidth}px`;
                    clonedElement.style.height = `${captureHeight}px`;
                    clonedElement.style.overflow = 'hidden';
                    clonedElement.style.transform = 'none';
                    clonedElement.style.padding = '0';
                    clonedElement.style.margin = '0';

                    const graphicsContainer = clonedElement.querySelector('.graphics-container');
                    if (graphicsContainer) {
                      graphicsContainer.style.padding = '0';
                      graphicsContainer.style.margin = '0';
                      graphicsContainer.style.borderRadius = '0';
                      graphicsContainer.style.background = '#ffffff';
                    }

                    const canvasElement = clonedElement.querySelector('canvas');
                    if (canvasElement) {
                      canvasElement.style.width = '100%';
                      canvasElement.style.height = '100%';
                      canvasElement.style.margin = '0';
                      canvasElement.style.padding = '0';
                      canvasElement.style.borderRadius = '0';
                      canvasElement.style.background = '#ffffff';
                    }

                    const schwartzContainer = clonedElement.querySelector('.schwartz-container');
                    if (schwartzContainer) {
                      schwartzContainer.style.width = '100%';
                      schwartzContainer.style.height = '100%';
                      schwartzContainer.style.margin = '0';
                      schwartzContainer.style.padding = '0';
                      schwartzContainer.style.background = '#ffffff';
                      schwartzContainer.style.display = 'flex';
                      schwartzContainer.style.justifyContent = 'center';
                      schwartzContainer.style.alignItems = 'center';
                    }

                    const schwartzMatrix = clonedElement.querySelector('.schwartz-matrix');
                    if (schwartzMatrix) {
                      schwartzMatrix.style.width = '100%';
                      schwartzMatrix.style.height = '100%';
                      schwartzMatrix.style.margin = '0';
                      schwartzMatrix.style.padding = '0';
                      schwartzMatrix.style.background = '#ffffff';
                    }
                  }
                }
              });
              
              const imgData = canvas.toDataURL('image/png');

              if (createNewPage) {
                doc.addPage();
                y = margin + 20; 
              }

              doc.setFontSize(14);
              doc.setFont(undefined, 'bold');
              doc.text(title, margin, y); y += 20;

              const pageWidth = doc.internal.pageSize.getWidth();
              const availableWidth = pageWidth - (2 * margin);
              const maxWidth = Math.min(availableWidth * 0.8, 600); 
              const maxHeight = 400; 

              const aspectRatio = captureWidth / captureHeight;
              let finalWidth = maxWidth;
              let finalHeight = maxWidth / aspectRatio;

              if (finalHeight > maxHeight) {
                finalHeight = maxHeight;
                finalWidth = maxHeight * aspectRatio;
              }

              const xOffset = (pageWidth - finalWidth) / 2;
              
              // Verificar si hay espacio suficiente en la página actual
              const pageHeight = doc.internal.pageSize.getHeight();
              const bottomMargin = margin;
              const availableSpace = pageHeight - bottomMargin - y;
              
              if (finalHeight > availableSpace - 30) {
                // Crear nueva página si no hay espacio suficiente
                doc.addPage();
                y = margin + 20;
              }
              
              doc.addImage(imgData, 'PNG', xOffset, y, finalWidth, finalHeight);
              y += finalHeight + 10; // Reducir espacio entre elementos
              
              return y;
            } catch (error) {
              return false;
            }
          };

          checkPageBreak(40);
          doc.setFontSize(16);
          doc.setFont(undefined, 'bold');
          
          const titleWidth = doc.getTextWidth('REPORTE COMPLETO DE PROSPECTIVA');
          const titleX = (pageWidth - titleWidth) / 2;
          doc.text('REPORTE COMPLETO DE PROSPECTIVA', titleX, y);
          y += 25;
          
          doc.setFontSize(14);
          doc.setFont(undefined, 'normal');
          doc.text('Análisis Prospectivo de Variables', margin, y);
          y += 20;

          if (usuario.route_name) {
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text(`Ruta: ${usuario.route_name}`, margin, y);
            y += 15;
          }

          checkPageBreak(25);
          doc.setFontSize(12);
          doc.setFont(undefined, 'bold');
          doc.text('INFORMACIÓN PERSONAL', margin, y); y += 15;
            doc.setFontSize(10);
            autoTable(doc, {
              startY: y,
              head: [['Campo', 'Valor']],
              body: [
                ['Nombre Completo', `${usuario.first_name || ''} ${usuario.last_name || ''}`],
                ['Correo Electrónico', usuario.user || ''],
                ['Identificación', usuario.document_id || ''],
                ['Fecha de Reporte', new Date().toLocaleDateString('es-ES')],
              ],
              theme: 'grid',
              styles: { 
                fontSize: 9, 
                cellPadding: 3,
                lineColor: [0, 0, 0],
                lineWidth: 0.5
              },
              headStyles: {
                fillColor: [52, 152, 219],
                textColor: [255, 255, 255],
                fontSize: 10,
                fontStyle: 'bold'
              },
              alternateRowStyles: {
                fillColor: [248, 249, 250]
              },
              margin: { left: margin, right: margin },
            });
            y = doc.lastAutoTable.finalY + 15;

            // Solo verificar salto de página si hay muchas variables
            if (usuario.variables_list && usuario.variables_list.length > 10) {
              checkTablePageBreak(usuario.variables_list || [], 'VARIABLES DEL SISTEMA');
            }
            
            // Agregar espacio antes del título
            y += 10;
            
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text('VARIABLES DEL SISTEMA', margin, y); y += 15;
            if (usuario.variables_list && usuario.variables_list.length > 0) {
              autoTable(doc, {
                startY: y,
                head: [['Código', 'Nombre de Variable', 'Descripción']],
                body: (usuario.variables_list || []).map(v => [
                  v.id_variable || 'N/A',
                  v.name_variable || 'Sin nombre',
                  v.description || 'Sin descripción'
                ]),
                theme: 'grid',
                styles: { 
                  fontSize: 8, 
                  cellPadding: 2,
                  lineColor: [0, 0, 0],
                  lineWidth: 0.5
                },
                headStyles: {
                  fillColor: [75, 0, 130],
                  textColor: [255, 255, 255],
                  fontSize: 9,
                  fontStyle: 'bold'
                },
                alternateRowStyles: {
                  fillColor: [248, 249, 250]
                },
                margin: { left: margin, right: margin },
              });
              y = doc.lastAutoTable.finalY + 15;
            } else {
              doc.setFontSize(10);
              doc.text('No hay variables registradas', margin, y);
              y += 15;
            }

            // Verificar si hay espacio suficiente para la matriz completa
            const variables = usuario.matriz_cruzada ? [...new Set(usuario.matriz_cruzada.map(m => m.origen))].sort() : [];
            const matrizEstimatedHeight = variables.length > 0 ? (variables.length + 2) * 15 + 80 : 0;
            
            if (matrizEstimatedHeight > 0) {
              checkPageBreak(matrizEstimatedHeight + 30, 'MATRIZ DE ANÁLISIS ESTRUCTURAL');
            } else {
              checkPageBreak(25);
            }
            
            // Agregar espacio antes del título
            y += 10;
            
            // Siempre agregar el título
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text('MATRIZ DE ANÁLISIS ESTRUCTURAL', margin, y); 
            y += 15;
            
            if (usuario.matriz && usuario.matriz.length > 0 && usuario.matriz_cruzada && variables.length > 0) {

              const matrizHeaders = ['CÓDIGO', 'NOMBRE', ...variables.map(v => v), 'TOTAL INFLUENCIA'];
              const matrizBody = variables.map(varOrigen => {

                const variableInfo = usuario.variables_list ? usuario.variables_list.find(v => v.id_variable === varOrigen) : null;
                const nombreVariable = variableInfo ? variableInfo.name_variable : varOrigen;
                
                const row = [varOrigen, nombreVariable]; 

                variables.forEach(varDestino => {
                  if (varOrigen === varDestino) {
                    row.push('X');
                  } else {
                    const item = usuario.matriz_cruzada.find(m => m.origen === varOrigen && m.destino === varDestino);
                    row.push(item ? item.valor : 0);
                  }
                });

                const totalInfluencia = variables.reduce((sum, varDestino) => {
                  if (varOrigen !== varDestino) {
                    const item = usuario.matriz_cruzada.find(m => m.origen === varOrigen && m.destino === varDestino);
                    return sum + (item ? Number(item.valor) : 0);
                  }
                  return sum;
                }, 0);
                row.push(totalInfluencia);
                
                return row;
              });

              const totalDependenciaRow = ['TOTAL DEPENDENCIA', '', ...variables.map(varDestino => {
                return variables.reduce((sum, varOrigen) => {
                  if (varOrigen !== varDestino) {
                    const item = usuario.matriz_cruzada.find(m => m.origen === varOrigen && m.destino === varDestino);
                    return sum + (item ? Number(item.valor) : 0);
                  }
                  return sum;
                }, 0);
              }), ''];
              matrizBody.push(totalDependenciaRow);

              const totalGeneral = variables.reduce((sum, varOrigen) => {
                return sum + variables.reduce((sumDestino, varDestino) => {
                  if (varOrigen !== varDestino) {
                    const item = usuario.matriz_cruzada.find(m => m.origen === varOrigen && m.destino === varDestino);
                    return sumDestino + (item ? Number(item.valor) : 0);
                  }
                  return sumDestino;
                }, 0);
              }, 0);

              matrizBody[matrizBody.length - 1][matrizBody[matrizBody.length - 1].length - 1] = totalGeneral;

              // Calcular tamaño de fuente dinámico basado en cantidad de variables
              const dynamicFontSize = variables.length > 10 ? 6 : variables.length > 8 ? 7 : 8;
              const dynamicCellPadding = variables.length > 10 ? 1 : variables.length > 8 ? 2 : 3;
              
              autoTable(doc, {
                startY: y,
                head: [matrizHeaders],
                body: matrizBody,
                theme: 'grid',
                styles: { 
                  fontSize: dynamicFontSize, 
                  cellPadding: dynamicCellPadding,
                  lineColor: [0, 0, 0],
                  lineWidth: 0.5,
                  halign: 'center',
                  valign: 'middle'
                },
                headStyles: {
                  fillColor: [238, 242, 255], 
                  textColor: [79, 70, 229], 
                  fontSize: dynamicFontSize + 1,
                  fontStyle: 'bold',
                  halign: 'center',
                  valign: 'middle'
                },
                alternateRowStyles: {
                  fillColor: [249, 250, 251] 
                },
                margin: { left: margin, right: margin },
                pageBreak: 'auto',
                showFoot: 'lastPage',
                didParseCell: function(data) {
                  
                  if (data.column.index === 0 || data.column.index === 1) {
                    data.cell.styles.fillColor = [238, 242, 255]; 
                    data.cell.styles.textColor = [79, 70, 229]; 
                    data.cell.styles.fontStyle = 'bold';
                    data.cell.styles.halign = 'center';
                  }

                  if (data.row.index > 0 && data.column.index > 1 && data.column.index < matrizHeaders.length - 1) {
                    const value = parseInt(data.cell.text[0]);

                    if (data.cell.text[0] === 'X') {
                      data.cell.styles.fillColor = [245, 243, 255]; 
                      data.cell.styles.textColor = [109, 40, 217]; 
                      data.cell.styles.fontStyle = 'bold';
                    }
                    
                    else if (value === 0) {
                      data.cell.styles.fillColor = [245, 246, 250]; 
                      data.cell.styles.textColor = [136, 136, 136]; 
                    } else if (value === 1) {
                      data.cell.styles.fillColor = [224, 247, 250]; 
                      data.cell.styles.textColor = [2, 136, 209]; 
                    } else if (value === 2) {
                      data.cell.styles.fillColor = [255, 249, 196]; 
                      data.cell.styles.textColor = [251, 192, 45]; 
                    } else if (value === 3) {
                      data.cell.styles.fillColor = [248, 187, 208]; 
                      data.cell.styles.textColor = [194, 24, 91]; 
                    }
                  }

                  if (data.column.index === matrizHeaders.length - 1 || data.row.index === matrizBody.length - 1) {
                    data.cell.styles.fillColor = [230, 249, 240]; 
                    data.cell.styles.textColor = [27, 94, 32]; 
                    data.cell.styles.fontStyle = 'bold';
                  }

                  if (data.row.index === matrizBody.length - 1 && data.column.index === 0) {
                    data.cell.colSpan = 2;
                    data.cell.styles.halign = 'center';
                  }

                  data.cell.styles.halign = 'center';
                  data.cell.styles.valign = 'middle';
                }
              });
              y = doc.lastAutoTable.finalY + 15;

              // Verificar espacio para el resumen
              checkPageBreak(50, 'RESUMEN DE DEPENDENCIA E INFLUENCIA');
              
              // Agregar espacio antes del título
              y += 10;
              
              // Agregar título del resumen
              doc.setFontSize(12);
              doc.setFont(undefined, 'bold');
              doc.text('RESUMEN DE DEPENDENCIA E INFLUENCIA', margin, y); y += 15;
              
              const resumenHeaders = ['RESUMEN', ...variables];
              const resumenBody = [
                ['DEPENDENCIA', ...variables.map(varOrigen => {
                  return variables.reduce((sum, varDestino) => {
                    if (varOrigen !== varDestino) {
                      const item = usuario.matriz_cruzada.find(m => m.origen === varOrigen && m.destino === varDestino);
                      return sum + (item ? Number(item.valor) : 0);
                    }
                    return sum;
                  }, 0);
                })],
                ['INFLUENCIA', ...variables.map(varDestino => {
                  return variables.reduce((sum, varOrigen) => {
                    if (varOrigen !== varDestino) {
                      const item = usuario.matriz_cruzada.find(m => m.origen === varOrigen && m.destino === varDestino);
                      return sum + (item ? Number(item.valor) : 0);
                    }
                    return sum;
                  }, 0);
                })]
              ];
              
              autoTable(doc, {
                startY: y,
                head: [resumenHeaders],
                body: resumenBody,
                theme: 'grid',
                styles: { 
                  fontSize: dynamicFontSize, 
                  cellPadding: dynamicCellPadding,
                  lineColor: [0, 0, 0],
                  lineWidth: 0.5,
                  halign: 'center',
                  valign: 'middle'
                },
                headStyles: {
                  fillColor: [238, 242, 255], 
                  textColor: [79, 70, 229], 
                  fontSize: dynamicFontSize + 1,
                  fontStyle: 'bold',
                  halign: 'center',
                  valign: 'middle'
                },
                margin: { left: margin, right: margin },
                pageBreak: 'auto',
                didParseCell: function(data) {
                  
                  if (data.column.index === 0) {
                    data.cell.styles.fillColor = [238, 242, 255]; 
                    data.cell.styles.textColor = [79, 70, 229]; 
                    data.cell.styles.fontStyle = 'bold';
                    data.cell.styles.halign = 'center';
                  }

                  if (data.column.index > 0) {
                    data.cell.styles.fillColor = [250, 250, 250]; 
                    data.cell.styles.textColor = [31, 41, 55]; 
                    data.cell.styles.fontWeight = '500';
                  }

                  data.cell.styles.halign = 'center';
                  data.cell.styles.valign = 'middle';
                }
              });
              y = doc.lastAutoTable.finalY + 15;
            } else {
              doc.setFontSize(10);
              doc.text('No hay datos de matriz disponibles', margin, y);
              y += 15;
            }

            // Verificar espacio para la gráfica de variables
            checkGraphicPageBreak(350, 'GRÁFICA DE VARIABLES - MAPA DE ANÁLISIS');
            y = await captureGraphic(`grafica-variables-${uniqueId}`, 'GRÁFICA DE VARIABLES - MAPA DE ANÁLISIS', y, false);
            if (y) {
              y += 10; // Espacio adicional después de la gráfica
            } else {
              y += 30; 
            }

            // Solo verificar salto de página si hay muchos análisis de zonas
            if (usuario.zone_analyses && usuario.zone_analyses.length > 5) {
              checkTablePageBreak(usuario.zone_analyses || [], 'ANÁLISIS MAPA DE VARIABLES');
            }
            
            // Agregar espacio antes del título
            y += 10;
            
            // Agregar título
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text('ANÁLISIS MAPA DE VARIABLES', margin, y); y += 15;
            
            if (usuario.zone_analyses && usuario.zone_analyses.length > 0) {
              autoTable(doc, {
                startY: y,
                head: [['Zona', 'Puntaje', 'Variables en la Zona', 'Descripción']],
                body: (usuario.zone_analyses || []).map(z => {
                  
                  let variablesEnZona = 'Sin variables';
                  if (z.variables_in_zone && z.variables_in_zone.length > 0) {
                    variablesEnZona = z.variables_in_zone.map(v => {
                      let variableText = v.id_variable;
                      if (v.name_variable) {
                        variableText += ` (${v.name_variable})`;
                      }
                      if (v.frontera) {
                        variableText += ' ⚡'; 
                      }
                      return variableText;
                    }).join('\n'); 
                  }
                  
                  return [
                    z.zone_name || 'Sin nombre',
                    z.score || 0,
                    variablesEnZona,
                    z.description || 'Sin descripción'
                  ];
                }),
                theme: 'grid',
                styles: { 
                  fontSize: 8, 
                  cellPadding: 2,
                  lineColor: [0, 0, 0],
                  lineWidth: 0.5
                },
                headStyles: {
                  fillColor: [26, 188, 156],
                  textColor: [255, 255, 255],
                  fontSize: 9,
                  fontStyle: 'bold'
                },
                alternateRowStyles: {
                  fillColor: [245, 245, 245]
                },
                margin: { left: margin, right: margin },
                didParseCell: function(data) {
                  
                  if (data.column.index === 2 && data.row.index > 0) {
                    data.cell.styles.fontSize = 8;
                  }
                }
              });
              y = doc.lastAutoTable.finalY + 15;
            } else {
              doc.setFontSize(10);
              doc.text('No hay análisis de zonas disponibles', margin, y);
              y += 15;
            }

            // Solo verificar salto de página si hay muchos direccionadores
            if (usuario.future_drivers && usuario.future_drivers.length > 8) {
              checkTablePageBreak(usuario.future_drivers || [], 'DIRECCIONADORES DE FUTURO');
            }
            
            // Agregar espacio antes del título
            y += 10;
            
            // Agregar título
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text('DIRECCIONADORES DE FUTURO', margin, y); y += 15;
            
            if (usuario.future_drivers && usuario.future_drivers.length > 0) {
              autoTable(doc, {
                startY: y,
                head: [['Hipótesis', 'Variable', 'Tipo', 'Descripción']],
                body: (usuario.future_drivers || []).map(d => [
                  d.name_hypothesis || 'N/A',
                  d.variable_name || 'N/A',
                  d.secondary_hypotheses || 'N/A',
                  d.description || 'Sin descripción'
                ]),
                theme: 'grid',
                styles: { 
                  fontSize: 8, 
                  cellPadding: 2,
                  lineColor: [0, 0, 0],
                  lineWidth: 0.5
                },
                headStyles: {
                  fillColor: [52, 73, 94],
                  textColor: [255, 255, 255],
                  fontSize: 9,
                  fontStyle: 'bold'
                },
                alternateRowStyles: {
                  fillColor: [248, 249, 250]
                },
                margin: { left: margin, right: margin },
              });
              y = doc.lastAutoTable.finalY + 15;
            } else {
              doc.setFontSize(10);
              doc.text('No hay direccionadores de futuro registrados', margin, y);
              y += 15;
            }

            // Solo verificar salto de página si la gráfica es muy grande
            const schwartzHeight = 380;
            if (schwartzHeight > 400) {
              checkGraphicPageBreak(schwartzHeight, 'EJES DE PETER SCHWARTZ');
            }
            const tempDivSchwartz = document.createElement('div');
            tempDivSchwartz.style.position = 'fixed';
            tempDivSchwartz.style.left = '-9999px';
            tempDivSchwartz.style.top = '0';
            tempDivSchwartz.style.width = '900px';
            tempDivSchwartz.style.height = '700px';
            document.body.appendChild(tempDivSchwartz);
            const appSchwartz = createApp(SchwartzPDFEditableCanvas, {
              scenarios: usuario.scenarios || [],
              hypotheses: convertFutureDriversToPDFFormat(usuario.future_drivers || []),
              width: 900,
              height: 700,
              boxWidth: 210,
              boxHeight: 90,
              scenarioBoxWidth: 210,
              scenarioBoxHeight: 90,
              font: '14px Arial',
              titleFont: 'bold 16px Arial',
              offset: 150,
              hypoOffset: 300,
              axisLength: 230,
              margin: 30
            });
            appSchwartz.mount(tempDivSchwartz);
            await nextTick();
            const canvasSchwartz = tempDivSchwartz.querySelector('canvas');
            if (canvasSchwartz) {
              const imgData = canvasSchwartz.toDataURL('image/png');

              doc.setFontSize(16);
              doc.setFont(undefined, 'bold');
              const titleText = 'EJES DE PETER SCHWARTZ';
              doc.text(titleText, margin, y);
              y += 25; 

              const originalWidth = 560;
              const originalHeight = 420;
              const scaleFactor = 0.7; 
              const finalWidth = originalWidth * scaleFactor;
              const finalHeight = originalHeight * scaleFactor;

              const pageWidth = doc.internal.pageSize.getWidth();
              const x = (pageWidth - finalWidth) / 2;

              const pageHeight = doc.internal.pageSize.getHeight();
              const bottomMargin = margin;
              const availableSpace = pageHeight - bottomMargin - y;
              
              if (finalHeight > availableSpace - 30) {
                // Crear nueva página si no hay espacio suficiente
                doc.addPage();
                y = margin + 20;
              }
              
              doc.addImage(imgData, 'PNG', x, y, finalWidth, finalHeight);
              y = y + finalHeight + 15; 
            }
            appSchwartz.unmount();
            document.body.removeChild(tempDivSchwartz);

            // Solo verificar salto de página si hay muchas condiciones iniciales
            if (usuario.initial_conditions && usuario.initial_conditions.length > 10) {
              checkTablePageBreak(usuario.initial_conditions || [], 'CONDICIONES INICIALES');
            }
            
            // Agregar espacio antes del título
            y += 10;
            
            // Agregar título
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text('CONDICIONES INICIALES', margin, y); y += 15;
            
            if (usuario.initial_conditions && usuario.initial_conditions.length > 0) {
              autoTable(doc, {
                startY: y,
                head: [['Código', 'Variable', 'Condición Actual']],
                body: (usuario.initial_conditions || []).map(c => [
                  c.variable_id || 'N/A',
                  c.variable_name || 'N/A',
                  c.now_condition || 'Sin condición registrada'
                ]),
                theme: 'grid',
                styles: { 
                  fontSize: 9, 
                  cellPadding: 3,
                  lineColor: [0, 0, 0],
                  lineWidth: 0.5
                },
                headStyles: {
                  fillColor: [142, 68, 173],
                  textColor: [255, 255, 255],
                  fontSize: 10,
                  fontStyle: 'bold'
                },
                alternateRowStyles: {
                  fillColor: [248, 249, 250]
                },
                margin: { left: margin, right: margin },
              });
              y = doc.lastAutoTable.finalY + 15;
            } else {
              doc.setFontSize(10);
              doc.text('No hay condiciones iniciales registradas', margin, y);
              y += 15;
            }

            // Solo verificar salto de página si hay muchos escenarios
            if (usuario.scenarios && usuario.scenarios.length > 6) {
              checkTablePageBreak(usuario.scenarios || [], 'ESCENARIOS');
            }
            
            // Agregar espacio antes del título
            y += 10;
            
            // Agregar título
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text('ESCENARIOS', margin, y); y += 15;
            
            if (usuario.scenarios && usuario.scenarios.length > 0) {
              autoTable(doc, {
                startY: y,
                head: [['Escenario', 'Título', 'Hipótesis Relacionadas', 'Año 1', 'Año 2', 'Año 3']],
                body: (usuario.scenarios || []).map(s => [
                  s.num_scenario || 'N/A',
                  s.titulo || 'Sin título',
                  getScenarioHypotheses(s, usuario.future_drivers),
                  s.year1 || 'N/A',
                  s.year2 || 'N/A',
                  s.year3 || 'N/A'
                ]),
                theme: 'grid',
                styles: { 
                  fontSize: 7, 
                  cellPadding: 1,
                  lineColor: [0, 0, 0],
                  lineWidth: 0.5
                },
                headStyles: {
                  fillColor: [155, 89, 182],
                  textColor: [255, 255, 255],
                  fontSize: 8,
                  fontStyle: 'bold'
                },
                margin: { left: margin, right: margin },
              });
              y = doc.lastAutoTable.finalY + 15;
            } else {
              doc.setFontSize(10);
              doc.text('No hay escenarios registrados', margin, y);
              y += 15;
            }

            // Solo verificar salto de página si hay muchas conclusiones
            if (usuario.conclusions && usuario.conclusions.length > 8) {
              checkTablePageBreak(usuario.conclusions || [], 'CONCLUSIONES DE APRENDIZAJE');
            }
            
            // Agregar espacio antes del título
            y += 10;
            
            // Agregar título
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text('CONCLUSIONES DE APRENDIZAJE', margin, y); y += 15;
            
            if (usuario.conclusions && usuario.conclusions.length > 0) {
              
              const conclusionesData = [];
              usuario.conclusions.forEach((c, index) => {
                if (c.component_practice) {
                  conclusionesData.push(['Componente Práctico', c.component_practice]);
                }
                if (c.actuality) {
                  conclusionesData.push(['Actualidad', c.actuality]);
                }
                if (c.aplication) {
                  conclusionesData.push(['Aplicación', c.aplication]);
                }
              });
              
              if (conclusionesData.length > 0) {
                autoTable(doc, {
                  startY: y,
                  head: [['Tipo de Conclusión', 'Descripción']],
                  body: conclusionesData,
                                  theme: 'grid',
                styles: { 
                  fontSize: 9, 
                  cellPadding: 3,
                  lineColor: [0, 0, 0],
                  lineWidth: 0.5
                },
                headStyles: {
                  fillColor: [241, 196, 15],
                  textColor: [0, 0, 0],
                  fontSize: 10,
                  fontStyle: 'bold'
                },
                alternateRowStyles: {
                  fillColor: [255, 255, 224]
                },
                margin: { left: margin, right: margin },
                });
                y = doc.lastAutoTable.finalY + 15;
                          } else {
              doc.setFontSize(10);
              doc.text('No hay conclusiones detalladas disponibles', margin, y);
              y += 15;
            }
            } else {
              doc.setFontSize(10);
              doc.text('No hay conclusiones registradas', margin, y);
              y += 15;
            }

            // Verificar si hay espacio para el resumen ejecutivo
            const resumenHeight = 100; // Altura estimada del resumen
            const currentPageHeight = doc.internal.pageSize.getHeight();
            const bottomMargin = margin;
            const availableSpace = currentPageHeight - bottomMargin - y;
            
            if (resumenHeight > availableSpace - 30) {
              doc.addPage();
              y = margin + 20;
            }
            
            // Agregar espacio antes del título
            y += 10;
            
            doc.setFontSize(12);
            doc.setFont(undefined, 'bold');
            doc.text('RESUMEN EJECUTIVO', 40, y); y += 15;
            
            const resumenData = [
              ['Total Variables Analizadas', usuario.variables_list ? usuario.variables_list.length : 0],
              ['Análisis de Zonas Completados', usuario.zone_analyses ? usuario.zone_analyses.length : 0],
              ['Direccionadores de Futuro', usuario.future_drivers ? usuario.future_drivers.length : 0],
              ['Condiciones Iniciales', usuario.initial_conditions ? usuario.initial_conditions.length : 0],
              ['Escenarios Estratégicos', usuario.scenarios ? usuario.scenarios.length : 0],
              ['Conclusiones Registradas', usuario.conclusions ? usuario.conclusions.length : 0],
            ];
            
            autoTable(doc, {
              startY: y,
              head: [['Métrica', 'Cantidad']],
              body: resumenData,
              theme: 'grid',
              styles: { fontSize: 9, cellPadding: 3 },
              margin: { left: margin, right: margin },
            });
            y = doc.lastAutoTable.finalY + 15;

            doc.setFontSize(8);
            doc.text(`Reporte generado el ${new Date().toLocaleString('es-ES')}`, 40, y);
            y += 8;
            doc.text('Sistema de Prospectiva - Análisis Estructural de Variables', 40, y);

            doc.save(`Reporte_Prospectiva_${usuario.first_name || ''}_${usuario.last_name || ''}_${new Date().toISOString().split('T')[0]}.pdf`);
          } catch (error) {
            restoreGraphics();
            restoreGraphicsToOriginal();
          } finally {
            restoreGraphics();
            restoreGraphicsToOriginal();
            loadingPdfId.value = null;
          }
        }

        function getVariablesByZone(matriz) {
          if (!matriz || matriz.length === 0) return {};
          
          const dependencias = matriz.map(v => v.dependencia);
          const influencias = matriz.map(v => v.influencia);
          const maxX = Math.max(...dependencias, 10);
          const maxY = Math.max(...influencias, 12);
          const centroX = maxX / 2;
          const centroY = maxY / 2;
          
          const zonas = {
            'ZONA DE PODER': [],
            'ZONA DE CONFLICTO': [],
            'ZONA DE SALIDA': [],
            'ZONA DE INDIFERENCIA': []
          };
          matriz.forEach(variable => {
            let zona = '';
            if (variable.dependencia <= centroX && variable.influencia > centroY) {
              zona = 'ZONA DE PODER';
            } else if (variable.dependencia > centroX && variable.influencia >= centroY) {
              zona = 'ZONA DE CONFLICTO';
            } else if (variable.dependencia > centroX && variable.influencia < centroY) {
              zona = 'ZONA DE SALIDA';
            } else {
              zona = 'ZONA DE INDIFERENCIA';
            }
            zonas[zona].push(variable);
          });
          return zonas;
        }

        const getResultsDescription = computed(() => {
            const description = textsStore.getText('results_section.description');
            return description || 'Descripción de resultados no encontrada';
        });

        return { 
            sectionStore,
            textsStore, 
            resultsStore, 
            traceabilityStore, 
            users, 
            isLoading, 
            isAdmin,
            getResultsDescription,
            filterId,
            filterFirstName,
            filterLastName,
            filterDocumentId,
            filterStatus,
            filteredUsers,
            showVariableModal,
            showScenarioModal,
            showConclusionModal,
            showZoneAnalysisModal,
            showFutureDriverModal,
            showInitialConditionModal,
            showMatrizModal,
            selectedVariable,
            selectedScenario,
            selectedConclusion,
            selectedConclusions,
            selectedConclusionUser,
            selectedZoneAnalysis,
            selectedZoneAnalysisUser,
            selectedZoneAnalysisMatriz,
            selectedZoneAnalysisZoneKey,
            selectedFutureDriver,
            selectedInitialCondition,
            selectedMatriz,
            selectedMatrizUser,
            selectedMatrizCruzada,
            showVariableDescription,
            showZoneAnalysisDescription,
            getZoneAnalysis,
            showScenarioDescription,
            showConclusionDescription,
            showFutureDriverDescription,
            showInitialConditionDescription,
            showMatrizDescription,
            matrizOrderedVariables,
            getCellValueMatriz,
            getCellClassMatriz,
            getColorClaseMatriz,
            getTotalDependencia,
            getTotalInfluencia,
            getSumaTotalDependencia,
            showGraphicsModal,
            selectedGraphicsData,
            selectedGraphicsUser,
            showGraphicsDescription,
            GraphicsMainComponent,
            showSchwartzModalRef,
            selectedSchwartzScenarios,
            selectedSchwartzUser,
            showSchwartzModal,
            SchwartzMainComponent,
            selectedSchwartzHypotheses,
            convertMatrizToGraphicsData,
            convertFutureDriversToHypotheses,
            convertFutureDriversToPDFFormat,
            loadingPdfId,
            imprimirUsuario,
            getVariablesByZone,
            

        };
    },
    data() {
        return {
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
    async mounted() {
        this.sectionStore.setTitleSection(this.textsStore.getText('results_section.title'));

        await this.traceabilityStore.loadCurrentRoute();

        this.loadResultsByCurrentRoute();

        

        window.addEventListener('route-created', this.handleRouteCreated);
    },
    methods: {
        async loadResultsByCurrentRoute() {
            try {
                
                if (this.isAdmin) {
                    await this.resultsStore.fetchUsers();
                } else {
                    
                    await this.resultsStore.fetchUsers();
                }
            } catch (error) {
                console.error('Error al cargar resultados por ruta:', error);
                
                await this.resultsStore.fetchUsers();
            }
        },
        
        onIdInput(event) {
            const value = event.target.value;
            if (value === '' || /^\d+$/.test(value)) {
                this.filterId = value;
            } else {
                event.target.value = this.filterId;
            }
        },
        
        onDocumentIdInput(event) {
            const value = event.target.value;
            if (value === '' || /^\d+$/.test(value)) {
                this.filterDocumentId = value;
            } else {
                event.target.value = this.filterDocumentId;
            }
        },

        handleRouteCreated() {
            
            this.$forceUpdate();

            this.loadResultsByCurrentRoute();
        },
    },
    
    beforeUnmount() {
        
        window.removeEventListener('route-created', this.handleRouteCreated);
    }
}

</script>

<style scoped>
.main-content {
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  margin: 0 auto;
}

.filters-bar {
  background-color: #f5f5f5;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  width: 1260px;
  min-width: 1260px;
  max-width: 1260px;
  margin: 0 auto 20px auto;
  box-sizing: border-box;
}

.filters-container {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  gap: 1rem;
  width: 100%;
}

.filter-item {
  display: flex;
  flex-direction: column;
  min-width: 200px;
  flex: 1 1 200px;
  max-width: 280px;
}

.filter-identificacion {
  min-width: 240px;
  flex: 1 1 240px;
  max-width: 320px;
}

.filter-spacer {
  flex: 1 1 200px;
  min-width: 200px;
}

.filters-bar .field {
  margin-bottom: 0;
  width: 100%;
}

.filters-bar .field-label {
  white-space: nowrap;
  font-size: 1em;
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #363636;
}

.filters-bar .input {
  border-radius: 6px;
  width: 100%;
}

.filters-bar .input:focus {
  border-color: #00d1b2;
  box-shadow: 0 0 0 0.125em rgba(0, 209, 178, 0.25);
}

@media (max-width: 768px) {
  .filters-container {
    flex-direction: column;
    gap: 1rem;
  }
  
  .filter-item,
  .filter-identificacion {
    min-width: 100%;
    max-width: 100%;
    flex: 1 1 100%;
  }
  
  .filter-spacer {
    display: none;
  }
}

.tabla-scroll-contenedor {
  width: 1260px;
  min-width: 1260px;
  max-width: 1260px;
  margin: 4px auto 0 auto;
  overflow-x: auto;
  overflow-y: auto;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  background-color: white;
  padding: 16px 0;
  box-sizing: border-box;
}

:deep(.tabla-grande table) {
  min-width: 1800px !important;
  width: 1800px !important;
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
  padding-right: 16px; 
}

.variable-item {
  margin-bottom: 6px;
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #f8f9fa;
  border-left: 3px solid #005883;
  transition: all 0.2s ease;
}

.variable-item:hover {
  background-color: #e3f2fd;
  border-left-color: #004466;
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
  color: #005883;
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
  font-size: 1.2rem;
  font-weight: bold;
  color: #3273dc;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #3273dc;
  text-align: center;
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

:deep(.b-table thead th) {
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

.tabla-scroll-contenedor {
  width: 100%;
  max-width: 1200px;
  height: 500px;
  overflow-x: auto;
  overflow-y: auto;
  border: 1px solid #ccc;
  background: #fff;
  margin-bottom: 32px;
}

.matriz-container {
  max-height: 200px;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 4px;
  padding-right: 16px;
}

.matriz-summary {
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #f0f8ff;
  border-left: 3px solid #4caf50;
  transition: all 0.2s ease;
}

.matriz-summary:hover {
  background-color: #e8f5e8;
  border-left-color: #2e7d32;
}

.matriz-link {
  cursor: pointer;
  display: block;
  font-size: 0.9em;
  line-height: 1.4;
  padding: 4px 8px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.matriz-link:hover {
  background-color: #e8f5e8;
  color: #2e7d32;
}

.matriz-link strong {
  color: #4caf50;
  font-weight: 600;
}

.matriz-link:hover strong {
  color: #2e7d32;
}

.matriz-variables-count {
  color: #666;
  font-size: 0.8em;
  margin-left: 8px;
}

.matriz-modal-card {
  width: 95vw;
  max-width: 1200px;
  min-width: 900px;
  min-height: 420px;
  max-height: 90vh;
  margin: 0 auto;
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  border-radius: 12px;
}

.matriz-modal-content {
  padding: 24px 12px 12px 12px;
  background: #f8f9fa;
  border-radius: 12px;
  min-height: 350px;
  max-height: none;
  overflow: hidden;
}

.matriz-table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  margin-bottom: 2rem;
  overflow-x: auto;
  overflow-y: visible;
  width: 100%;
}

.matriz-table {
  width: 100%;
  min-width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 14px;
  table-layout: fixed;
}

.matriz-header-cell {
  background-color: #EEF2FF;
  color: #4F46E5;
  padding: 0.75rem 0.5rem;
  text-align: center;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
  border: none;
  border-bottom: 2px solid #E0E7FF;
  min-width: 50px;
  max-width: 60px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.matriz-codigo-cell {
  min-width: 60px;
  max-width: 70px;
}

.matriz-nombre-cell {
  min-width: 150px;
  max-width: 180px;
  text-align: left;
  white-space: normal;
  word-wrap: break-word;
}

.matriz-data-cell {
  min-width: 50px;
  max-width: 60px;
}

.matriz-total-cell, .matriz-total-header-cell {
  background-color: #F0FDF4;
  color: #166534;
  padding: 0.75rem 0.5rem;
  text-align: center;
  font-weight: 600;
  border: none;
  font-size: 12px;
  letter-spacing: 0.5px;
  min-width: 80px;
  max-width: 100px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.matriz-total-header-cell {
  text-align: left;
}

.matriz-cell-header {
  background-color: #EEF2FF;
  color: #4F46E5;
  padding: 0.75rem 0.5rem;
  text-align: center;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 12px;
  border: none;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.matriz-cell-center {
  text-align: center;
  padding: 0.75rem 0.5rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.matriz-cell-diagonal {
  background-color: #F5F3FF;
  cursor: not-allowed;
  font-weight: 600;
  color: #6D28D9;
  border-right: 1px solid #EDE9FE;
  border-bottom: 1px solid #EDE9FE;
}

.matriz-cell-editable {
  cursor: pointer;
  border-right: 1px solid #EDE9FE;
  border-bottom: 1px solid #EDE9FE;
  background-color: #FAFAFA;
}

tr:nth-child(even) .matriz-cell-editable {
  background-color: #FAFAFA;
}
tr:nth-child(odd) .matriz-cell-editable {
  background-color: #FFFFFF;
}

.matriz-value-strong-modern {
  color: #DC2626;
  font-weight: 600;
  font-size: 16px;
}
.matriz-value-medium-modern {
  color: #EA580C;
  font-weight: 600;
  font-size: 16px;
}
.matriz-value-weak-modern {
  color: #CA8A04;
  font-weight: 600;
  font-size: 16px;
}
.matriz-value-none-modern {
  color: #6B7280;
  font-size: 14px;
  font-weight: 500;
}

.matriz-resumen-modal {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
  margin-top: 2rem;
}
.resumen-table-modal {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 14px;
}
.resumen-table-modal th, .resumen-table-modal td {
  border: none;
  padding: 12px 8px;
  text-align: center;
}
.resumen-table-modal th {
  background-color: #EEF2FF;
  color: #4F46E5;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 13px;
  border-bottom: 2px solid #E0E7FF;
}
.resumen-table-modal td:first-child {
  background-color: #EEF2FF;
  color: #4F46E5;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 13px;
  border-right: 2px solid #E0E7FF;
}
.resumen-table-modal td {
  background-color: #FAFAFA;
  color: #1F2937;
  font-weight: 500;
}
.resumen-table-modal tr:last-child td {
  border-top: 1px solid #E0E7FF;
}

.tabla-scroll-contenedor table th,
.tabla-scroll-contenedor table td {
  min-width: 200px !important;
}

canvas {
  display: block;
  margin: 0 auto;
  background: #333;
  width: 800px !important;
  height: 560px !important;
  max-width: 800px;
  max-height: 560px;
}

.pdf-graphic-container canvas {
  background: #fff !important;
  border-radius: 0 !important;
  border: none !important;
  box-shadow: none !important;
}

.pdf-graphic-container .graphics-container {
  background: #fff !important;
  border-radius: 0 !important;
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;
}

.custom-spinner {
  display: inline-block;
  width: 22px;
  height: 22px;
  border: 3px solid #a78bfa;
  border-top: 3px solid #7c3aed;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  vertical-align: middle;
  margin: 0 2px;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>

<style>

.b-table thead th {
  text-align: center !important;
  vertical-align: middle !important;
}

.pdf-button-container {
  width: 80px !important;
  height: 32px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  position: relative !important;
}

.pdf-button {
  width: 80px !important;
  height: 32px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  min-width: 80px !important;
  max-width: 80px !important;
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
}

.pdf-button .button-content {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 100% !important;
  height: 100% !important;
}

.pdf-button .button-content i {
  font-size: 18px !important;
  width: 18px !important;
  height: 18px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
}

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

:deep(.dialog .dialog-footer .button),
:deep(.dialog .dialog-footer .button.is-info),
:deep(.dialog .dialog-footer .button.is-danger),
:deep(.dialog .dialog-footer button),
:deep(.dialog .dialog-footer button.is-info),
:deep(.dialog .dialog-footer button.is-danger),
:deep(.dialog .dialog-footer .button[style]),
:deep(.dialog .dialog-footer button[style]) {
  background-color: #f14668 !important;
  color: #fff !important;
  border-color: #f14668 !important;
  box-shadow: none !important;
}
:deep(.dialog .dialog-footer .button:hover),
:deep(.dialog .dialog-footer .button.is-info:hover),
:deep(.dialog .dialog-footer .button.is-danger:hover),
:deep(.dialog .dialog-footer button:hover),
:deep(.dialog .dialog-footer button.is-info:hover),
:deep(.dialog .dialog-footer button.is-danger:hover),
:deep(.dialog .dialog-footer .button[style]:hover),
:deep(.dialog .dialog-footer button[style]:hover) {
  background-color: #d12c4c !important;
  border-color: #d12c4c !important;
}

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

.clickable-zone {
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: all 0.2s ease;
  border: 1px solid transparent;
  color: #363636;
  font-family: inherit;
  font-size: 0.9em;
  line-height: 1.4;
}

.clickable-zone:hover {
  background-color: #f0f8ff;
  border-color: #005883;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 88, 131, 0.1);
  color: #005883;
}

.clickable-zone:active {
  transform: translateY(0);
  box-shadow: 0 1px 2px rgba(0, 88, 131, 0.2);
}

.clickable-zone strong {
  color: #4caf50;
  font-weight: 600;
}

.clickable-zone:hover strong {
  color: #2e7d32;
}

.clickable-zone .zone-variable-code {
  color: #005883;
  font-weight: 500;
}

.clickable-zone:hover .zone-variable-code {
  color: #004466;
}
</style>

.conclusions-modal-content {
  max-height: 70vh;
  overflow-y: auto;
}

.conclusion-section {
  margin-bottom: 2rem;
  padding: 1rem;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #fafafa;
}

.conclusion-section:last-child {
  margin-bottom: 0;
}

.conclusion-title {
  font-size: 1.2rem;
  font-weight: bold;
  color: #3273dc;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #3273dc;
}

.conclusion-divider {
  margin: 2rem 0;
  border: none;
  height: 1px;
  background-color: #e0e0e0;
}

.modal-section {
  margin-bottom: 1rem;
}

.modal-section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #363636;
  margin-bottom: 0.5rem;
}

.modal-content {
  padding: 0.75rem;
  background-color: white;
  border-radius: 4px;
  border-left: 3px solid #3273dc;
  line-height: 1.5;
  white-space: pre-wrap;
}

.no-content {
  text-align: center;
  padding: 1rem;
  color: #7a7a7a;
  font-style: italic;
}

/* Botones personalizados con colores clave */
.custom-blue-btn {
  background-color: #005883 !important;
  border-color: #005883 !important;
  color: white !important;
  transition: all 0.2s ease;
}

.custom-blue-btn:hover {
  background-color: #004466 !important;
  border-color: #004466 !important;
  color: white !important;
}

.custom-orange-btn {
  background-color: #F47920 !important;
  border-color: #F47920 !important;
  color: white !important;
  transition: all 0.2s ease;
}

.custom-orange-btn:hover {
  background-color: #E0651A !important;
  border-color: #E0651A !important;
  color: white !important;
}

/* Etiquetas personalizadas con colores clave */
.custom-blue-tag {
  background-color: #005883 !important;
  color: white !important;
  border-color: #005883 !important;
}