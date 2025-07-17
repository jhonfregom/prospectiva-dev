<template>
  <div v-if="!isStoreInitialized || traceabilityStore.isLoading || !seccionesValidas" class="stepper-loading" style="min-height:160px;"></div>
  <div v-else class="stepper-principal stepper-multifila">
    <div
      v-for="(row, rowIdx) in gridRows"
      :key="'row-' + rowIdx"
      class="stepper-track-row"
      :style="{ gridTemplateColumns: `repeat(${maxPerRow}, 1fr)` }"
    >
      <!-- Línea solo entre la primera y última burbuja real -->
      <div
        class="stepper-row-line"
        :style="rowLineStyle(row, rowIdx)"
      ></div>
      <template v-for="(cell, idx) in row">
        <div
          v-if="cell"
          :key="cell.key"
          class="stepper-step"
          :class="{
            active: cell.globalIdx === currentActiveStep,
            completed: cell.globalIdx < currentActiveStep,
            clickable: isStepEnabled(cell.globalIdx),
            disabled: !isStepEnabled(cell.globalIdx)
          }"
          @click="goToStep(cell.globalIdx)"
        >
          <div class="stepper-circle">
            <span class="stepper-icon">
              <i :class="cell.icon"></i>
            </span>
          </div>
          <div class="stepper-label">{{ cell.label }}</div>
        </div>
        <div v-else :key="'empty-' + idx" class="stepper-step empty"></div>
      </template>
    </div>
    <!-- Agrego la última fila manualmente debajo del v-for -->
    <div class="stepper-last-row-flex">
      <div class="stepper-step"
           :class="{
             active: currentActiveStep === 8,
             completed: currentActiveStep > 8,
             clickable: isStepEnabled(8),
             disabled: !isStepEnabled(8)
           }"
           @click="goToStep(8)">
        <div class="stepper-circle">
          <span class="stepper-icon">
            <i :class="'fas fa-lightbulb'"></i>
          </span>
        </div>
        <div class="stepper-label">Conclusiones</div>
      </div>
      <div class="stepper-row-line last-flex"></div>
      <div class="stepper-step"
           :class="{
             active: currentActiveStep === 9,
             completed: currentActiveStep > 9,
             clickable: isStepEnabled(9),
             disabled: !isStepEnabled(9)
           }"
           @click="goToStep(9)">
        <div class="stepper-circle">
          <span class="stepper-icon">
            <i :class="'fas fa-trophy'"></i>
          </span>
        </div>
        <div class="stepper-label">Resultados</div>
      </div>
    </div>
  </div>
</template>

<script>
import { useSessionStore } from '../../../stores/session';
import { useTraceabilityStore } from '../../../stores/traceability';
import { computed, onMounted, ref } from 'vue';

export default {
  setup() {
    const storeSession = useSessionStore();
    const traceabilityStore = useTraceabilityStore();
    
    // Estado reactivo para controlar si el store está inicializado
    const isStoreInitialized = ref(false);
    
    // --- NUEVO: Validación extra de secciones válidas ---
    const seccionesValidas = computed(() => {
      const secciones = traceabilityStore.availableSections;
      if (!secciones) return false;
      const values = Object.values(secciones);
      const allTrue = values.every(v => v === true);
      // Mostrar si la primera está en true y (hay al menos una en false o todas son true)
      return values[0] === true && (values.some(v => v === false) || allTrue);
    });
    // --- FIN NUEVO ---
    
    // Inicializar el store de traceability al montar el componente
    onMounted(async () => {
      try {
        await traceabilityStore.initialize();
        isStoreInitialized.value = true;
      } catch (error) {
        await traceabilityStore.loadAvailableSections();
        isStoreInitialized.value = true;
      }
    });
    
    // Define los pasos/secciones principales
    const steps = [
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
    ];
    
    // Divide los pasos en 3 filas equilibradas y ajusta la última fila para centrar las burbujas
    const maxPerRow = 4;
    const gridRows = computed(() => {
      // Primeras dos filas normales
      const row1 = steps.slice(0, 4).map((s, i) => ({ ...s, globalIdx: i }));
      const row2 = steps.slice(4, 8).map((s, i) => ({ ...s, globalIdx: i + 4 }));
      // Última fila: se maneja manualmente para posicionamiento exacto
      return [row1, row2];
    });
    
    // Encuentra el índice del paso actual
    const currentStepIndex = computed(() => {
      const active = Object.keys(storeSession.contentActive).find(
        (k) => storeSession.contentActive[k]
      );
      return steps.findIndex((s) => s.key === active);
    });
    
    // Calcula el paso activo basado en las secciones disponibles para usuarios rol 0
    const currentActiveStep = computed(() => {
      if (!isStoreInitialized.value) {
        return currentStepIndex.value;
      }
      
      const userRole = traceabilityStore.getUserRole;
      const isAdmin = traceabilityStore.isAdmin;
      
      // Para administradores, usar el índice actual normal
      if (isAdmin) {
        return currentStepIndex.value;
      }
      
      // Para usuarios rol 0, calcular basado en secciones disponibles
      const sectionKeys = [
        'variables',
        'matrix', 
        'graphics',
        'analysis',
        'hypothesis',
        'schwartz',
        'initialconditions',
        'scenarios',
        'conclusions',
        'results'
      ];
      
      // Encontrar la última sección disponible
      let lastAvailableIndex = -1;
      for (let i = 0; i < sectionKeys.length; i++) {
        const sectionKey = sectionKeys[i];
        if (traceabilityStore.isSectionAvailable(sectionKey)) {
          lastAvailableIndex = i;
        } else {
          break; // Detener en la primera no disponible
        }
      }
      
      return Math.max(0, lastAvailableIndex);
    });
    
    // Verifica si un paso está habilitado basado en permisos
    function isStepEnabled(idx) {
      const step = steps[idx];
      if (!step) return false;
      
      // Si el store no está inicializado, permitir acceso temporal
      if (!isStoreInitialized.value) {
        return true;
      }
      
      // Debug: mostrar información del usuario y permisos
      const userRole = traceabilityStore.getUserRole;
      const isAdmin = traceabilityStore.isAdmin;
      const availableSections = traceabilityStore.availableSections;
      const isAvailable = traceabilityStore.isSectionAvailable(step.key);
      
      // Los administradores pueden acceder a todas las secciones
      if (isAdmin) {
        return true;
      }
      
      // Para usuarios normales, verificar si la sección está disponible
      return isAvailable;
    }
    
    // Función para navegar a un paso
    async function goToStep(idx) {
      const step = steps[idx];
      if (!step) return;
      
      // Si el store no está inicializado, permitir navegación temporal
      if (!isStoreInitialized.value) {
        storeSession.setActiveContent(step.key);
        return;
      }
      
      // Los administradores pueden navegar libremente
      if (traceabilityStore.isAdmin) {
        storeSession.setActiveContent(step.key);
        return;
      }
      
      // Para usuarios normales, verificar permisos
      if (isStepEnabled(idx)) {
        storeSession.setActiveContent(step.key);
      } else {
        // Mostrar mensaje de que la sección no está disponible
        alert(`La sección ${step.label} no está disponible aún. Debes completar las secciones anteriores primero.`);
        // Aquí podrías mostrar un toast o alert
      }
    }
    // Calcula el estilo de la línea para que solo conecte las burbujas reales
    function rowLineStyle(row, rowIdx) {
      const first = row.findIndex(cell => cell);
      const last = row.length - 1 - [...row].reverse().findIndex(cell => cell);
      const total = row.length;
      // Si es la última fila (Conclusiones y Resultados centrados)
      if (rowIdx === 2 && first === 1 && last === 2 && total === 4) {
        // Línea desde el extremo izquierdo hasta el borde izquierdo de la burbuja de Resultados
        return {
          left: '0',
          right: `calc(30% + 60px)`, // 25% es la celda vacía + 60px es la mitad de la burbuja (120px/2)
        };
      }
      const left = (first / total) * 100;
      const right = 100 - ((last + 1) / total) * 100;
      return {
        left: left + '%',
        right: right + '%',
      };
    }
    return { gridRows, currentStepIndex, currentActiveStep, goToStep, isStepEnabled, maxPerRow, rowLineStyle, isStoreInitialized, traceabilityStore, seccionesValidas };
  },
};
</script>

<style scoped>
.stepper-principal.stepper-multifila {
  width: 100%;
  margin: 30px 0 20px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.stepper-track-row {
  display: grid;
  align-items: center;
  width: 100%;
  max-width: 1400px;
  justify-content: center;
  margin-bottom: 32px;
  position: relative;
  min-height: 120px; /* igual al alto de la burbuja */
}
.stepper-track-row:last-child {
  margin-bottom: 0;
}
.stepper-track-row:nth-child(1),
.stepper-track-row:nth-child(2) {
  padding-top: 17px;
}
.stepper-row-line {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 6px;
  background: #b5b5b5;
  z-index: 1;
  pointer-events: none;
  transition: left 0.6s, right 0.6s;
  transform: translateY(-50%);
}
.stepper-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  z-index: 2;
  cursor: pointer;
}
.stepper-step.empty {
  cursor: default;
  background: none;
  box-shadow: none;
  pointer-events: none;
}
.stepper-step.clickable:hover .stepper-circle {
  border-color: #3273dc;
  box-shadow: 0 0 0 2px #3273dc33;
}
.stepper-step.disabled .stepper-circle {
  border-color: #ccc;
  background: #f5f5f5;
  color: #999;
  cursor: not-allowed;
}
.stepper-step.disabled .stepper-label {
  color: #999;
}
.stepper-step.completed .stepper-circle {
  border-color: #b5b5b5;
  background: #e0e0e0;
  color: #b5b5b5;
}
.stepper-step.active .stepper-circle {
  border: 3px solid #3273dc;
  color: #3273dc;
  background: #fff;
}
.stepper-step .stepper-circle {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 3px solid #b5b5b5;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 4.2rem;
  margin-bottom: 8px;
  transition: border 0.6s, box-shadow 0.6s;
}
.stepper-step .stepper-label {
  font-size: 2.2rem;
  color: #444;
  margin-bottom: 0;
  text-align: center;
  min-width: 160px;
  max-width: 260px;
  word-break: break-word;
}
@media (max-width: 1200px) {
  .stepper-step .stepper-circle {
    width: 80px;
    height: 80px;
    font-size: 2.2rem;
  }
  .stepper-step .stepper-label {
    font-size: 1.2rem;
    min-width: 90px;
    max-width: 140px;
  }
}
@media (max-width: 800px) {
  .stepper-step .stepper-circle {
    width: 50px;
    height: 50px;
    font-size: 1.1rem;
  }
  .stepper-step .stepper-label {
    font-size: 0.9rem;
    min-width: 60px;
    max-width: 90px;
  }
}
.stepper-last-row-flex {
  width: 100%;
  max-width: 1400px;
  margin: 0 auto 0 auto;
  display: flex;
  align-items: center;
  position: relative;
}
.stepper-last-row-flex .stepper-step {
  flex: 0 0 120px;
  min-width: 0;
  z-index: 2;
}
.stepper-row-line.last-flex {
    flex: 1 1 auto;
  height: 6px;
  background: #b5b5b5;
  z-index: 1;
  margin-left: 0;
  margin-right: 300px; /* 120px (burbuja) + 8px (espacio) */
  min-width: 20px;
}
</style> 