<template>
  <div class="graphics-container">
    <canvas ref="chartCanvas" width="800" height="560"></canvas>
  </div>
</template>

<script>
import { onMounted, ref, watch } from 'vue';
import { useGraphicsStore } from '../../../../stores/graphics';
import { useTextsStore } from '../../../../stores/texts';
import { useSectionStore } from '../../../../stores/section';
import { storeToRefs } from 'pinia';
import Chart from 'chart.js/auto';
import annotationPlugin from 'chartjs-plugin-annotation';

Chart.register(annotationPlugin);

export default {
  setup() {
    const graphicsStore = useGraphicsStore();
    const textsStore = useTextsStore();
    const sectionStore = useSectionStore();
    const { data, isLoading } = storeToRefs(graphicsStore);
    const chartCanvas = ref(null);
    let chartInstance = null;

    // Función para calcular los límites de los ejes
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

    // Función para calcular el centro de la cruz
    function getCrossCenter(points, maxX, maxY) {
      // Puedes usar la media de los valores o el punto medio del rango
      // Aquí usamos el punto medio del rango
      return {
        x: maxX / 2,
        y: maxY / 2
      };
    }

    function renderChart() {
      if (chartInstance) chartInstance.destroy();
      const points = data.value.map(item => ({
        x: item.dependencia,
        y: item.influencia,
        label: item.codigo
      }));
      const { maxX, maxY, minX, minY } = getAxisLimits(points);
      const cross = getCrossCenter(points, maxX, maxY);

      chartInstance = new Chart(chartCanvas.value, {
        type: 'scatter',
        data: {
          datasets: [
            {
              label: 'Variables',
              data: points,
              backgroundColor: '#FF0000',
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
                // Línea diagonal negra
                diagonal: {
                  type: 'line',
                  borderColor: 'black',
                  borderWidth: 3,
                  xMin: minX,
                  xMax: maxX,
                  yMin: minY,
                  yMax: maxY,
                  label: {
                    display: false
                  }
                },
                // Línea roja vertical (cruz)
                crossVertical: {
                  type: 'line',
                  borderColor: 'red',
                  borderWidth: 2,
                  xMin: cross.x,
                  xMax: cross.x,
                  yMin: minY,
                  yMax: maxY,
                  borderDash: [6, 6]
                },
                // Línea roja horizontal (cruz)
                crossHorizontal: {
                  type: 'line',
                  borderColor: 'red',
                  borderWidth: 2,
                  yMin: cross.y,
                  yMax: cross.y,
                  xMin: minX,
                  xMax: maxX,
                  borderDash: [6, 6]
                },
                // Zona de Poder
                zonaPoder: {
                  type: 'label',
                  xValue: cross.x * 0.3,
                  yValue: cross.y * 1.7,
                  backgroundColor: 'rgba(0,0,0,0)',
                  color: '#FAB800',
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
                // Zona de Indiferencia
                zonaIndiferencia: {
                  type: 'label',
                  xValue: cross.x * 0.3,
                  yValue: cross.y * 0.3,
                  backgroundColor: 'rgba(0,0,0,0)',
                  color: '#FAB800',
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
                // Zona de Conflicto
                zonaConflicto: {
                  type: 'label',
                  xValue: cross.x * 1.7,
                  yValue: cross.y * 1.7,
                  backgroundColor: 'rgba(0,0,0,0)',
                  color: '#FAB800',
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
                // Zona de Salida
                zonaSalida: {
                  type: 'label',
                  xValue: cross.x * 1.7,
                  yValue: cross.y * 0.3,
                  backgroundColor: 'rgba(0,0,0,0)',
                  color: '#FAB800',
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
    }

    onMounted(async () => {
      sectionStore.setTitleSection(textsStore.graphics.title);
      await graphicsStore.fetchGraphicsData();
      renderChart();
    });

    // Si los datos cambian, vuelve a renderizar la gráfica
    watch(data, () => {
      renderChart();
    });

    return { chartCanvas, isLoading, textsStore };
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
</style>

<!--
IMPORTANTE: Instala el plugin de anotaciones de Chart.js si no lo tienes:
npm install chartjs-plugin-annotation
--> 