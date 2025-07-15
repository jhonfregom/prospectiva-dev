<template>
  <div class="schwartz-pdf-container">
    <canvas ref="schwartzCanvas" width="800" height="640"></canvas>
  </div>
</template>

<script>
import { onMounted, ref, watch } from 'vue';

export default {
  props: {
    externalScenarios: {
      type: Array,
      default: null
    },
    externalHypotheses: {
      type: Array,
      default: null
    }
  },
  setup(props) {
    const schwartzCanvas = ref(null);

    function drawSchwartzMatrix() {
      if (!schwartzCanvas.value) return;
      
      const canvas = schwartzCanvas.value;
      const ctx = canvas.getContext('2d');
      const width = canvas.width;
      const height = canvas.height;
      
      // Limpiar canvas
      ctx.clearRect(0, 0, width, height);
      
      // Fondo blanco
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, width, height);
      
      // Dimensiones de la matriz
      const cellWidth = width / 5;
      const cellHeight = height / 5;
      const padding = 10;
      
      // Colores
      const hypothesisColor = '#3498db';
      const scenarioColor = '#2ecc71';
      const borderColor = '#34495e';
      const textColor = '#2c3e50';
      const emptyColor = '#ecf0f1';
      const axisColor = '#e74c3c';
      
      // Función para dibujar una celda
      function drawCell(x, y, w, h, text, type) {
        // Fondo de la celda
        ctx.fillStyle = type === 'hypothesis' ? hypothesisColor : 
                       type === 'scenario' ? scenarioColor : emptyColor;
        ctx.fillRect(x + padding, y + padding, w - 2*padding, h - 2*padding);
        
        // Borde
        ctx.strokeStyle = borderColor;
        ctx.lineWidth = 2;
        ctx.strokeRect(x + padding, y + padding, w - 2*padding, h - 2*padding);
        
        // Texto
        if (text) {
          ctx.fillStyle = textColor;
          ctx.font = 'bold 12px Arial';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';
          
          // Dividir texto en líneas
          const maxWidth = w - 2*padding - 20;
          const words = text.split(' ');
          const lines = [];
          let currentLine = '';
          
          for (let word of words) {
            const testLine = currentLine + word + ' ';
            const metrics = ctx.measureText(testLine);
            if (metrics.width > maxWidth && currentLine !== '') {
              lines.push(currentLine);
              currentLine = word + ' ';
            } else {
              currentLine = testLine;
            }
          }
          if (currentLine) {
            lines.push(currentLine.trim());
          }
          
          // Dibujar líneas de texto
          const lineHeight = 16;
          const startY = y + h/2 - (lines.length - 1) * lineHeight/2;
          
          for (let i = 0; i < lines.length; i++) {
            ctx.fillText(lines[i], x + w/2, startY + i * lineHeight);
          }
        }
      }
      
      // Función para obtener texto de hipótesis
      function getHypothesisText(hypotheses, hypothesis, type) {
        if (!hypotheses) return '';
        const found = hypotheses.find(h => 
          h.name_hypothesis === hypothesis && 
          h.secondary_hypotheses === type
        );
        return found ? (found.description || found.variable_name || '') : '';
      }
      
      // Función para obtener texto de escenario
      function getScenarioText(scenarios, index) {
        if (!scenarios) return '';
        const scenario = scenarios[index];
        return scenario ? (scenario.texto || scenario.titulo || '') : '';
      }
      
      const scenarios = props.externalScenarios || [];
      const hypotheses = props.externalHypotheses || [];
      
      // Dibujar matriz 5x5
      for (let row = 0; row < 5; row++) {
        for (let col = 0; col < 5; col++) {
          const x = col * cellWidth;
          const y = row * cellHeight;
          let text = '';
          let type = 'empty';
          
          // Definir contenido de cada celda según la posición
          if (row === 0 && col === 2) {
            // H1+ (arriba)
            text = getHypothesisText(hypotheses, 'H1', 'H1');
            type = 'hypothesis';
          } else if (row === 1 && col === 1) {
            // Scenario 4
            text = getScenarioText(scenarios, 3);
            type = 'scenario';
          } else if (row === 1 && col === 3) {
            // Scenario 1
            text = getScenarioText(scenarios, 0);
            type = 'scenario';
          } else if (row === 2 && col === 0) {
            // H2- (izquierda)
            text = getHypothesisText(hypotheses, 'H2', 'H0');
            type = 'hypothesis';
          } else if (row === 2 && col === 4) {
            // H2+ (derecha)
            text = getHypothesisText(hypotheses, 'H2', 'H1');
            type = 'hypothesis';
          } else if (row === 3 && col === 1) {
            // Scenario 3
            text = getScenarioText(scenarios, 2);
            type = 'scenario';
          } else if (row === 3 && col === 3) {
            // Scenario 2
            text = getScenarioText(scenarios, 1);
            type = 'scenario';
          } else if (row === 4 && col === 2) {
            // H1- (abajo)
            text = getHypothesisText(hypotheses, 'H1', 'H0');
            type = 'hypothesis';
          }
          
          drawCell(x, y, cellWidth, cellHeight, text, type);
        }
      }
      
      // Dibujar líneas de ejes
      ctx.strokeStyle = axisColor;
      ctx.lineWidth = 3;
      ctx.setLineDash([10, 5]);
      
      // Línea vertical central
      ctx.beginPath();
      ctx.moveTo(width/2, 0);
      ctx.lineTo(width/2, height);
      ctx.stroke();
      
      // Línea horizontal central
      ctx.beginPath();
      ctx.moveTo(0, height/2);
      ctx.lineTo(width, height/2);
      ctx.stroke();
      
      // Restaurar línea sólida
      ctx.setLineDash([]);
      
      // Dibujar títulos de ejes
      ctx.fillStyle = '#2c3e50';
      ctx.font = 'bold 18px Arial';
      ctx.textAlign = 'center';
      
      // Título eje X (H2)
      ctx.fillText('H2', width/2, height - 20);
      
      // Título eje Y (H1)
      ctx.save();
      ctx.translate(25, height/2);
      ctx.rotate(-Math.PI/2);
      ctx.fillText('H1', 0, 0);
      ctx.restore();
      
      // Leyenda
      ctx.font = 'bold 14px Arial';
      ctx.textAlign = 'left';
      
      // Leyenda hipótesis
      ctx.fillStyle = hypothesisColor;
      ctx.fillRect(width - 180, 20, 15, 15);
      ctx.fillStyle = textColor;
      ctx.fillText('Hipótesis', width - 155, 32);
      
      // Leyenda escenarios
      ctx.fillStyle = scenarioColor;
      ctx.fillRect(width - 180, 45, 15, 15);
      ctx.fillStyle = textColor;
      ctx.fillText('Escenarios', width - 155, 57);
    }

    // Observar cambios en los datos
    watch(() => [props.externalScenarios, props.externalHypotheses], () => {
      drawSchwartzMatrix();
    }, { deep: true });

    onMounted(() => {
      drawSchwartzMatrix();
    });

    return {
      schwartzCanvas
    };
  }
};
</script>

<style scoped>
.schwartz-pdf-container {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: white;
}

canvas {
  max-width: 100%;
  max-height: 100%;
  border: 2px solid #ddd;
}
</style> 
 