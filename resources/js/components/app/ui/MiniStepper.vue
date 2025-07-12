<template>
  <div class="mini-stepper">
    <div class="mini-stepper-inner" :style="miniStepperInnerStyle" style="position: relative;">
      <div
        v-if="visibleSteps.length > 1"
        class="mini-stepper-line"
        :style="miniLineStyle"
      ></div>
      <div
        v-for="(step, idx) in visibleSteps"
        :key="step.key"
        :class="['mini-step', { active: step.isCurrent }]"
      >
        <div class="mini-step-circle">
          <i :class="step.icon"></i>
        </div>
        <div class="mini-step-label">{{ step.label }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
const props = defineProps({
  steps: { type: Array, required: true },
  currentIndex: { type: Number, required: true }
});

const VISIBLE = 5; // debe ser impar
const HALF = Math.floor(VISIBLE / 2);

const visibleSteps = computed(() => {
  const arr = [];
  const total = props.steps.length;
  let start = props.currentIndex - HALF;
  let end = props.currentIndex + HALF;

  // Corrige si estamos al inicio
  if (start < 0) {
    end += -start;
    start = 0;
  }
  // Corrige si estamos al final
  if (end > total - 1) {
    start -= (end - (total - 1));
    end = total - 1;
  }
  start = Math.max(0, start);

  for (let i = start; i <= end; i++) {
    arr.push({ ...props.steps[i], isCurrent: i === props.currentIndex });
  }
  return arr;
});

// Cálculo para la línea entre burbujas
const bubbleSize = 72; // px, igual que .mini-step-circle
const gap = 64; // px, igual que .mini-stepper-inner gap
const miniLineStyle = computed(() => {
  if (visibleSteps.value.length < 2) return {};
  const lastIndex = visibleSteps.value.length - 1;
  return {
    left: bubbleSize / 2 + 'px',
    width: (lastIndex * (bubbleSize + gap)) + bubbleSize / 2 + 'px',
    top: '50%',
    transform: 'translateY(-50%)',
  };
});

const miniStepperInnerStyle = computed(() => {
  // Centra la burbuja activa en la página
  const containerWidth = window.innerWidth;
  const groupWidth = bubbleSize * visibleSteps.value.length + gap * (visibleSteps.value.length - 1);
  const activeIndex = visibleSteps.value.findIndex(s => s.isCurrent);
  const activeCenter = bubbleSize / 2 + activeIndex * (bubbleSize + gap);
  const offset = containerWidth / 2 - activeCenter - groupWidth / 2;
  return {
    transform: `translateX(${offset}px)`
  };
});
</script>

<style scoped>
.mini-stepper {
  display: flex;
  justify-content: center;
  margin: 16px 0;
}
.mini-stepper-inner {
  display: flex;
  align-items: center;
  position: relative;
  width: fit-content;
  gap: 64px;
}
.mini-stepper-line {
  position: absolute;
  height: 4px;
  background: #b5b5b5;
  z-index: 0;
  top: calc(24px - 2px);
  left: 16px;
  right: 0;
  transform: none;
}
.mini-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  opacity: 1 !important;
  transition: opacity 0.2s;
  z-index: 1;
}
.mini-step.active {
  opacity: 1;
}
.mini-step-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  border: 2px solid #b5b5b5;
  background: #fff !important;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  margin-bottom: 4px;
  transition: border 0.2s;
  margin-top: 20px;
}
.mini-step.active .mini-step-circle {
  border: 2.5px solid #3273dc;
  background: #f5faff;
  color: #3273dc;
}
.mini-step:not(.active) .mini-step-circle {
  border: 2px solid #b5b5b5 !important;
  color: #b5b5b5 !important;
  background: #fff !important;
  opacity: 1 !important;
}
.mini-step-label {
  font-size: 1.3rem;
  color: #444;
  text-align: center;
  max-width: 140px;
  word-break: break-word;
}
</style> 