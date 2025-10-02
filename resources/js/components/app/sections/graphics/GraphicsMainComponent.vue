<template>
  <div class="graphics-container">
    <!-- Letrero informativo -->
    <info-banner-component
      v-if="!readonly"
      :description="textsStore.graphics.description"
    />
    
    <canvas ref="chartCanvas" width="800" height="560"></canvas>
  </div>
</template>

<script>
import { onMounted, ref, watch, nextTick } from 'vue';
import { useGraphicsStore } from '../../../../stores/graphics';
import { useTextsStore } from '../../../../stores/texts';
import { useSectionStore } from '../../../../stores/section';
import { storeToRefs } from 'pinia';
import Chart from 'chart.js/auto';
import annotationPlugin from 'chartjs-plugin-annotation';
import InfoBannerComponent from '../../ui/InfoBannerComponent.vue';

Chart.register(annotationPlugin);

export default {
  props: {
    externalData: {
      type: Array,
      default: null
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  components: {
    InfoBannerComponent
  },
  setup(props) {
    const graphicsStore = useGraphicsStore();
    const textsStore = useTextsStore();
    const sectionStore = useSectionStore();
    const { data, isLoading } = storeToRefs(graphicsStore);
    const chartCanvas = ref(null);
    let chartInstance = null;

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
      { key: 'nueva', label: 'Nueva', icon: 'fas fa-star' },
    ];

    function getAxisLimits(points, minX = 10, minY = 12) {
      let maxX = minX, maxY = minY;
      points.forEach(p => {
        if (p.x > maxX) maxX = p.x;
        if (p.y > maxY) maxY = p.y;
      });
      return {
        maxX: Math.ceil(maxX),
        maxY: Math.ceil(maxY),
        minX: 0,
        minY: 0
      };
    }

    function getCrossCenter(points, maxX, maxY) {

      return {
        x: maxX / 2,
        y: maxY / 2
      };
    }

    function renderChart() {
      if (chartInstance) chartInstance.destroy();
      
      const usedData = props.externalData && props.externalData.length > 0 ? props.externalData : data.value;
      if (!usedData || usedData.length === 0) return;

      if (!chartCanvas.value) {
        console.error('Canvas no encontrado');
        return;
      }
      
      const points = usedData.map(item => ({
        x: item.dependencia, 
        y: item.influencia,  
        label: item.id_variable || item.codigo || ''
      }));
      const { maxX, maxY, minX, minY } = getAxisLimits(points);
      const cross = getCrossCenter(points, maxX, maxY);

      try {
        chartInstance = new Chart(chartCanvas.value, {
        type: 'scatter',
        data: {
          datasets: [
            {
              label: 'Variables',
              data: points,
              backgroundColor: '#F47920',
              pointRadius: 8,
              pointHoverRadius: 10
            }
          ]
        },
        options: {
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.raw.label || '';
                  return `${label}: (${textsStore.graphics.x_axis}: ${context.parsed.x}, ${textsStore.graphics.y_axis}: ${context.parsed.y})`;
                }
              }
            },
            annotation: {
              annotations: {
                
                diagonal: {
                  type: 'line',
                  borderColor: '#005883',
                  borderWidth: 3,
                  xMin: minX,
                  xMax: maxX,
                  yMin: minY,
                  yMax: maxY,
                  label: {
                    display: false
                  }
                },
                
                crossVertical: {
                  type: 'line',
                  borderColor: '#F47920',
                  borderWidth: 2,
                  xMin: cross.x,
                  xMax: cross.x,
                  yMin: minY,
                  yMax: maxY,
                  borderDash: [6, 6]
                },
                
                crossHorizontal: {
                  type: 'line',
                  borderColor: '#F47920',
                  borderWidth: 2,
                  yMin: cross.y,
                  yMax: cross.y,
                  xMin: minX,
                  xMax: maxX,
                  borderDash: [6, 6]
                },
                
                zonaPoder: {
                  type: 'label',
                  xValue: cross.x * 0.3,
                  yValue: cross.y * 1.7,
                  backgroundColor: 'rgba(0,0,0,0)',
                  color: '#F0B429',
                  font: {
                    size: 18,
                    weight: 'bold',
                  },
                  content: [textsStore.graphics.zone_power],
                  textAlign: 'center',
                  position: {
                    x: 'center',
                    y: 'center'
                  },
                  rotation: 0
                },
                
                zonaIndiferencia: {
                  type: 'label',
                  xValue: cross.x * 0.3,
                  yValue: cross.y * 0.3,
                  backgroundColor: 'rgba(0,0,0,0)',
                  color: '#F0B429',
                  font: {
                    size: 18,
                    weight: 'bold',
                  },
                  content: [textsStore.graphics.zone_indifference],
                  textAlign: 'center',
                  position: {
                    x: 'center',
                    y: 'center'
                  },
                  rotation: 0
                },
                
                zonaConflicto: {
                  type: 'label',
                  xValue: cross.x * 1.7,
                  yValue: cross.y * 1.7,
                  backgroundColor: 'rgba(0,0,0,0)',
                  color: '#F0B429',
                  font: {
                    size: 18,
                    weight: 'bold',
                  },
                  content: [textsStore.graphics.zone_conflict],
                  textAlign: 'center',
                  position: {
                    x: 'center',
                    y: 'center'
                  },
                  rotation: 0
                },
                
                zonaSalida: {
                  type: 'label',
                  xValue: cross.x * 1.7,
                  yValue: cross.y * 0.3,
                  backgroundColor: 'rgba(0,0,0,0)',
                  color: '#F0B429',
                  font: {
                    size: 18,
                    weight: 'bold',
                  },
                  content: [textsStore.graphics.zone_exit],
                  textAlign: 'center',
                  position: {
                    x: 'center',
                    y: 'center'
                  },
                  rotation: 0
                }
              }
            }
          },
          scales: {
            x: {
              title: {
                display: true,
                text: textsStore.graphics.x_axis
              },
              min: minX,
              max: maxX,
              beginAtZero: true
            },
            y: {
              title: {
                display: true,
                text: textsStore.graphics.y_axis
              },
              min: minY,
              max: maxY,
              beginAtZero: true
            }
          }
        }
      });
    } catch (error) {
      console.error('Error al crear la gráfica:', error);
    }
  }

      onMounted(async () => {
      
      if (!props.readonly) {
        sectionStore.setTitleSection(textsStore.graphics.title);
      }
      if (!props.externalData) {
        await graphicsStore.fetchGraphicsData();
      }
      
      await nextTick();
      renderChart();
    });

    watch(() => props.externalData, async () => {
      await nextTick();
      renderChart();
    }, { immediate: true });
    watch(data, async () => {
      if (!props.externalData) {
        await nextTick();
        renderChart();
      }
    });

    return { chartCanvas, isLoading, textsStore, steps };
  }
};
</script>

<style scoped>
.graphics-container {
  background: #222;
  border-radius: 12px;
  padding: 2rem;
  color: #fff;
  min-height: 560px;
  min-width: 800px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
h2 {
  text-align: center;
  margin-bottom: 2rem;
  color: #ffa600;
}
canvas {
  display: block;
  margin: 0 auto;
  background: #333;
  border-radius: 8px;
  width: 800px !important;
  height: 560px !important;
  max-width: 800px;
  max-height: 560px;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .graphics-container {
    padding: 1.5rem;
    min-width: 600px;
  }
  
  canvas {
    width: 600px !important;
    height: 420px !important;
    max-width: 600px;
    max-height: 420px;
  }
  
  /* Chart.js text scaling for tablet */
  :deep(.chartjs-render-monitor) {
    font-size: 0.9rem !important;
  }
}

@media (max-width: 768px) {
  .graphics-container {
    padding: 1rem;
    min-width: 400px;
    min-height: 400px;
  }
  
  h2 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  canvas {
    width: 400px !important;
    height: 280px !important;
    max-width: 400px;
    max-height: 280px;
  }
  
  /* Chart.js text scaling for mobile large */
  :deep(.chartjs-render-monitor) {
    font-size: 0.8rem !important;
  }
}

@media (max-width: 480px) {
  .graphics-container {
    padding: 0.5rem;
    min-width: 300px;
    min-height: 300px;
    border-radius: 8px;
  }
  
  h2 {
    font-size: 1.2rem;
    margin-bottom: 1rem;
  }
  
  canvas {
    width: 300px !important;
    height: 210px !important;
    max-width: 300px;
    max-height: 210px;
  }
  
  /* Chart.js text scaling for mobile medium */
  :deep(.chartjs-render-monitor) {
    font-size: 0.7rem !important;
  }
}

@media (max-width: 320px) {
  .graphics-container {
    min-width: 250px;
    min-height: 250px;
  }
  
  canvas {
    width: 250px !important;
    height: 175px !important;
    max-width: 250px;
    max-height: 175px;
  }
  
  /* Chart.js text scaling for mobile micro */
  :deep(.chartjs-render-monitor) {
    font-size: 0.6rem !important;
  }
}
</style>

<!--
IMPORTANTE: Instala el plugin de anotaciones de Chart.js si no lo tienes:
npm install chartjs-plugin-annotation
-->