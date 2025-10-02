<template>
  <nav class="custom-stepper">
    <div class="stepper-bar-container">
      <div class="stepper-bar-bg"></div>
      <div class="stepper-bar-progress" :style="progressStyle"></div>
    </div>
    <ul>
      <li v-for="(step, idx) in steps" :key="step.label"
          :class="[
            'step', 
            { 
              'is-active': idx === currentActiveStep, 
              'is-completed': idx < currentActiveStep,
              'is-enabled': isStepEnabled(idx),
              'is-disabled': !isStepEnabled(idx)
            }
          ]"
          @click="onBubbleClick(idx)"
          :style="bubbleStyle(idx)"
          :title="getTooltipText(step.label)">
        <span class="step-circle">
          <i :class="['fas', step.icon]"></i>
        </span>
        <span class="step-label">{{ step.label }}</span>
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue';
import { useTraceabilityStore } from '../../../stores/traceability';
import { useSessionStore } from '../../../stores/session';

const props = defineProps({
  steps: { type: Array, required: true },
  modelValue: { type: Number, required: true }
});
const emit = defineEmits(['update:modelValue']);

const traceabilityStore = useTraceabilityStore();
const sessionStore = useSessionStore();

const STORAGE_KEY = 'custom_stepper_last_step';

function getPersistedStep() {
  const val = localStorage.getItem(STORAGE_KEY);
  return val !== null ? parseInt(val, 10) : props.modelValue;
}

const lastStep = ref(getPersistedStep());
const animating = ref(false);
const isStoreInitialized = ref(false);

onMounted(async () => {
  try {
    await traceabilityStore.initialize();
    isStoreInitialized.value = true;
  } catch (error) {
    await traceabilityStore.loadAvailableSections();
    isStoreInitialized.value = true;
  }
});

const currentActiveStep = computed(() => {
  if (!isStoreInitialized.value) {
    return props.modelValue;
  }
  const userRole = traceabilityStore.getUserRole;
  const isAdmin = traceabilityStore.isAdmin;
  if (isAdmin) {
    return props.modelValue;
  }
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
  let lastAvailableIndex = -1;
  for (let i = 0; i < sectionKeys.length; i++) {
    const sectionKey = sectionKeys[i];
    if (traceabilityStore.isSectionAvailable(sectionKey)) {
      lastAvailableIndex = i;
    } else {
      break;
    }
  }
  return Math.max(0, lastAvailableIndex);
});

function persistStep(idx) {
  localStorage.setItem(STORAGE_KEY, idx);
}

function isStepEnabled(idx) {
  const step = props.steps[idx];
  if (!step) return false;
  if (!isStoreInitialized.value) return true;
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
  const sectionKey = sectionKeys[idx];
  if (!sectionKey) return false;
  const userRole = traceabilityStore.getUserRole;
  const isAdmin = traceabilityStore.isAdmin;
  if (isAdmin) return true;
  return traceabilityStore.isSectionAvailable(sectionKey);
}

async function onBubbleClick(idx) {
  await traceabilityStore.forceReloadSections();
  if (idx === props.modelValue || animating.value) return;
  const userRole = traceabilityStore.getUserRole;
  const isAdmin = traceabilityStore.isAdmin;
  if (!isAdmin) {
    const currentIndex = props.modelValue;
    const targetIndex = idx;
    if (targetIndex > currentIndex + 1) {
      alert('Debes completar las secciones en orden. Solo puedes avanzar al siguiente módulo disponible.');
      return;
    }
  }
  if (!isStepEnabled(idx)) {
    alert(`La sección ${props.steps[idx].label} no está disponible aún. Debes completar las secciones anteriores primero.`);
    return;
  }
  animating.value = true;
  lastStep.value = idx;
  persistStep(idx);
  setTimeout(() => {
    emit('update:modelValue', idx);
    animating.value = false;
  }, 400);
}

watch(() => props.modelValue, (val) => {
  if (val > lastStep.value) {
    lastStep.value = val;
    persistStep(val);
  }
});

onMounted(() => {
  lastStep.value = getPersistedStep();
});

const progressStyle = computed(() => {
  const total = props.steps.length - 1;
  const percent = total > 0 ? (currentActiveStep.value / total) * 100 : 0;
  return {
    width: percent + '%',
    transition: 'width 0.4s cubic-bezier(.4,1.3,.5,1)',
  };
});

function bubbleStyle(idx) {
  return {
    left: `calc(${(idx / (props.steps.length - 1)) * 100}% - 24px)`
  };
}

function getTooltipText(stepLabel) {
  const tooltips = {
    'Variables': 'Define y describe las variables clave del sistema que analizarás',
    'Matriz': 'Evalúa las relaciones de influencia y dependencia entre variables',
    'Gráfica': 'Visualiza las variables en un mapa de influencia vs dependencia',
    'Mapa': 'Analiza las variables en un mapa de análisis detallado',
    'Direccionador': 'Identifica los factores que dirigen el futuro del sistema',
    'Schwartz': 'Aplica los ejes de Peter Schwartz para el análisis prospectivo',
    'Condiciones': 'Establece las condiciones iniciales del análisis',
    'Escenarios': 'Desarrolla escenarios estratégicos futuros',
    'Conclusiones': 'Extrae conclusiones clave del análisis prospectivo',
    'Resultados': 'Revisa y consolida todos los resultados del análisis'
  };
  
  return tooltips[stepLabel] || stepLabel;
}
</script>

<style scoped>
.custom-stepper {
  width: 90%;
  position: relative;
  margin: 120px auto 32px auto;
}
.stepper-bar-container {
  position: relative;
  height: 11.2px;
  width: 100%;
  margin-bottom: 32px;
}
.stepper-bar-bg {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: #e5e7eb;
  border-radius: 4px;
  height: 11.2px;
  width: 100%;
  z-index: 1;
}
.stepper-bar-progress {
  position: absolute;
  top: 0; left: 0; bottom: 0;
  background: #005883;
  border-radius: 4px;
  height: 11.2px;
  z-index: 2;
  transition: width 0.4s cubic-bezier(.4,1.3,.5,1);
}
.custom-stepper ul {
  position: absolute;
  top: -28px;
  left: 0;
  width: 100%;
  display: flex;
  justify-content: space-between;
  padding: 0;
  margin: 0;
  list-style: none;
  pointer-events: none;
}
.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  position: absolute;
  pointer-events: auto;
  transition: color 0.2s;
  color: #b0b0b0;
  width: 67.2px;
  z-index: 3;
}
.step.is-active {
  color: #005883;
}
.step.is-completed {
  color: #005883;
}
.step.is-enabled {
  color: #005883; 
}
.step.is-disabled {
  color: #d1d5db; 
  cursor: not-allowed;
}
.step.is-disabled .step-circle {
  background: #f3f4f6 !important;
  background-color: #f3f4f6 !important;
  border-color: #d1d5db;
  color: #9ca3af;
  position: relative;
  z-index: 10;
  opacity: 1;
}
.step.is-disabled .step-label {
  color: #9ca3af;
}
.step-circle {
  width: 67.2px;
  height: 67.2px;
  border-radius: 50%;
  background: #ffffff !important;
  background-color: #ffffff !important;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.38rem;
  margin-bottom: 8px;
  box-shadow: 0 2px 8px rgba(50,115,220,0.08);
  border: 2px solid transparent;
  transition: border 0.2s, background 0.2s;
  position: relative;
  z-index: 10;
  opacity: 1;
}
.step.is-active .step-circle {
  border: 2px solid #005883;
  background: #ffffff !important;
  background-color: #ffffff !important;
  position: relative;
  z-index: 10;
  opacity: 1;
}
.step.is-completed .step-circle {
  border: 2px solid #005883;
  background: #ffffff !important;
  background-color: #ffffff !important;
  position: relative;
  z-index: 10;
  opacity: 1;
}
.step-label {
  font-size: 1.4rem;
  font-weight: 500;
  text-align: center;
  margin-bottom: 2px;
  white-space: nowrap;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .custom-stepper {
    width: 95%;
    margin: 80px auto 24px auto;
  }
  
  .step {
    width: 60px;
  }
  
  .step-circle {
    width: 60px;
    height: 60px;
    font-size: 2rem;
  }
  
  .step-label {
    font-size: 1.2rem;
  }
}

@media (max-width: 768px) {
  .custom-stepper {
    width: 98%;
    margin: 60px auto 20px auto;
  }
  
  .stepper-bar-container {
    height: 8px;
    margin-bottom: 24px;
  }
  
  .stepper-bar-bg,
  .stepper-bar-progress {
    height: 8px;
  }
  
  .custom-stepper ul {
    top: -20px;
  }
  
  .step {
    width: 50px;
  }
  
  .step-circle {
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    margin-bottom: 6px;
  }
  
  .step-label {
    font-size: 1rem;
    display: none; /* Ocultar labels en tablet */
  }
}

@media (max-width: 480px) {
  .custom-stepper {
    margin: 40px auto 16px auto;
  }
  
  .stepper-bar-container {
    height: 6px;
    margin-bottom: 20px;
  }
  
  .stepper-bar-bg,
  .stepper-bar-progress {
    height: 6px;
  }
  
  .custom-stepper ul {
    top: -16px;
  }
  
  .step {
    width: 40px;
  }
  
  .step-circle {
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
    margin-bottom: 4px;
  }
  
  .step-label {
    display: none; /* Ocultar labels en móvil */
  }
}

@media (max-width: 320px) {
  .custom-stepper {
    margin: 30px auto 12px auto;
  }
  
  .step {
    width: 35px;
  }
  
  .step-circle {
    width: 35px;
    height: 35px;
    font-size: 1rem;
  }
}
</style>