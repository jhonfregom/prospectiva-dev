<template>
  <div class="schwartz-chart-container">
    <canvas ref="schwartzCanvas" width="800" height="640"></canvas>
  </div>
</template>

<script>
import { onMounted, ref, watch } from 'vue';
import { useTextsStore } from '../../../../stores/texts';

export default {
  props: {
    externalScenarios: {
      type: Array,
      default: null
    },
    externalHypotheses: {
      type: Array,
      default: null
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const textsStore = useTextsStore();
    const schwartzCanvas = ref(null);
    let ctx = null;

    function drawSchwartzMatrix() {
      if (!schwartzCanvas.value) return;
      
      ctx = schwartzCanvas.value.getContext('2d');
      const canvas = schwartzCanvas.value;
      const width = canvas.width;
      const height = canvas.height;

      ctx.clearRect(0, 0, width, height);

      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, width, height);

      const cellWidth = width / 5;
      const cellHeight = height / 5;
      const padding = 10;

      const hypothesisColor = '#3498db';
      const scenarioColor = '#2ecc71';
      const borderColor = '#34495e';
      const textColor = '#2c3e50';
      const emptyColor = '#ecf0f1';

      function drawCell(x, y, w, h, text, type, position = '') {
        
        ctx.fillStyle = type === 'hypothesis' ? hypothesisColor : 
                       type === 'scenario' ? scenarioColor : emptyColor;
        ctx.fillRect(x + padding, y + padding, w - 2*padding, h - 2*padding);

        ctx.strokeStyle = borderColor;
        ctx.lineWidth = 2;
        ctx.strokeRect(x + padding, y + padding, w - 2*padding, h - 2*padding);

        if (text) {
          ctx.fillStyle = textColor;
          ctx.font = '12px Arial';
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';

          const maxWidth = w - 2*padding - 10;
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

          const lineHeight = 16;
          const startY = y + h/2 - (lines.length - 1) * lineHeight/2;
          
          for (let i = 0; i < lines.length; i++) {
            ctx.fillText(lines[i], x + w/2, startY + i * lineHeight);
          }
        }
      }

      function getHypothesisText(hypotheses, hypothesis, type) {
        const found = hypotheses.find(h => 
          h.name_hypothesis === hypothesis && 
          h.secondary_hypotheses === type
        );
        return found ? (found.description || found.variable_name || '') : '';
      }

      function getScenarioText(scenarios, index) {
        const scenario = scenarios[index];
        return scenario ? (scenario.texto || scenario.titulo || '') : '';
      }
      
      const scenarios = props.externalScenarios || [];
      const hypotheses = props.externalHypotheses || [];

      for (let row = 0; row < 5; row++) {
        for (let col = 0; col < 5; col++) {
          const x = col * cellWidth;
          const y = row * cellHeight;
          let text = '';
          let type = 'empty';
          let position = '';

          if (row === 0 && col === 2) {
            
            text = getHypothesisText(hypotheses, 'H1', 'H1');
            type = 'hypothesis';
            position = 'top';
          } else if (row === 1 && col === 1) {
            
            text = getScenarioText(scenarios, 3);
            type = 'scenario';
            position = 'scenario4';
          } else if (row === 1 && col === 3) {
            
            text = getScenarioText(scenarios, 0);
            type = 'scenario';
            position = 'scenario1';
          } else if (row === 2 && col === 0) {
            
            text = getHypothesisText(hypotheses, 'H2', 'H0');
            type = 'hypothesis';
            position = 'left';
          } else if (row === 2 && col === 4) {
            
            text = getHypothesisText(hypotheses, 'H2', 'H1');
            type = 'hypothesis';
            position = 'right';
          } else if (row === 3 && col === 1) {
            
            text = getScenarioText(scenarios, 2);
            type = 'scenario';
            position = 'scenario3';
          } else if (row === 3 && col === 3) {
            
            text = getScenarioText(scenarios, 1);
            type = 'scenario';
            position = 'scenario2';
          } else if (row === 4 && col === 2) {
            
            text = getHypothesisText(hypotheses, 'H1', 'H0');
            type = 'hypothesis';
            position = 'bottom';
          }
          
          drawCell(x, y, cellWidth, cellHeight, text, type, position);
        }
      }

      ctx.strokeStyle = '#e74c3c';
      ctx.lineWidth = 3;
      ctx.setLineDash([10, 5]);

      ctx.beginPath();
      ctx.moveTo(width/2, 0);
      ctx.lineTo(width/2, height);
      ctx.stroke();

      ctx.beginPath();
      ctx.moveTo(0, height/2);
      ctx.lineTo(width, height/2);
      ctx.stroke();

      ctx.setLineDash([]);

      ctx.fillStyle = '#2c3e50';
      ctx.font = 'bold 16px Arial';
      ctx.textAlign = 'center';

      ctx.fillText('H2', width/2, height - 20);

      ctx.save();
      ctx.translate(20, height/2);
      ctx.rotate(-Math.PI/2);
      ctx.fillText('H1', 0, 0);
      ctx.restore();
    }

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
.schwartz-chart-container {
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
  border: 1px solid #ddd;
}

/* Responsive styles for Schwartz chart */
@media (max-width: 1024px) {
  .schwartz-chart-container {
    padding: 1rem;
  }
  
  canvas {
    max-width: 700px;
    max-height: 500px;
  }
}

@media (max-width: 768px) {
  .schwartz-chart-container {
    padding: 0.75rem;
  }
  
  canvas {
    max-width: 600px;
    max-height: 400px;
  }
}

@media (max-width: 640px) {
  .schwartz-chart-container {
    padding: 0.5rem;
  }
  
  canvas {
    max-width: 500px;
    max-height: 350px;
  }
}

@media (max-width: 480px) {
  .schwartz-chart-container {
    padding: 0.25rem;
  }
  
  canvas {
    max-width: 400px;
    max-height: 300px;
  }
}

@media (max-width: 320px) {
  .schwartz-chart-container {
    padding: 0.1rem;
  }
  
  canvas {
    max-width: 300px;
    max-height: 250px;
  }
}
</style>