<template>
  <div class="schwartz-editable-canvas-container">
    <canvas ref="schwartzCanvas" :width="width" :height="height"></canvas>
  </div>
</template>

<script>
import { onMounted, ref, watch, nextTick } from 'vue';

export default {
  name: 'SchwartzPDFEditableCanvas',
  props: {
    scenarios: { type: Array, required: true },
    hypotheses: { type: Array, required: true },
    width: { type: Number, default: 900 },
    height: { type: Number, default: 700 },
    boxWidth: { type: Number, default: 210 },
    boxHeight: { type: Number, default: 90 },
    scenarioBoxWidth: { type: Number, default: 210 },
    scenarioBoxHeight: { type: Number, default: 90 },
    font: { type: String, default: '14px Arial' },
    titleFont: { type: String, default: 'bold 16px Arial' },
    boxColor: { type: String, default: '#EEF2FF' },
    boxBorder: { type: String, default: '#E0E7FF' },
    titleColor: { type: String, default: '#4F46E5' },
    textColor: { type: String, default: '#222' },
    axisColor: { type: String, default: 'red' },
    axisWidth: { type: Number, default: 7 },
    margin: { type: Number, default: 40 },
    offset: { type: Number, default: 150 }, // distancia de escenarios al centro
    hypoOffset: { type: Number, default: 210 }, // distancia de hipótesis al centro
    axisLength: { type: Number, default: 100 } // NUEVO: largo de los ejes desde el centro
  },
  setup(props) {
    const schwartzCanvas = ref(null);

    function drawSchwartz() {
      const canvas = schwartzCanvas.value;
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Dimensiones
      const W = props.width;
      const H = props.height;
      const centerX = W / 2;
      const centerY = H / 2;
      const boxW = props.boxWidth;
      const boxH = props.boxHeight;
      const scenBoxW = props.scenarioBoxWidth;
      const scenBoxH = props.scenarioBoxHeight;
      const font = props.font;
      const titleFont = props.titleFont;
      const boxColor = props.boxColor;
      const boxBorder = props.boxBorder;
      const titleColor = props.titleColor;
      const textColor = props.textColor;
      const axisColor = props.axisColor;
      const axisWidth = props.axisWidth;
      const margin = props.margin;
      const offset = props.offset;
      const hypoOffset = props.hypoOffset;

      // Fondo
      ctx.fillStyle = '#fff';
      ctx.fillRect(0, 0, W, H);

      // Ejes y flechas
      ctx.save();
      ctx.strokeStyle = axisColor;
      ctx.lineWidth = axisWidth;
      const arrowLength = 30; // largo de la flecha
      // Valores individuales para cada flecha
      const arrowOutTop = 5;    // flecha arriba
      const arrowOutBottom = 5; // flecha abajo
      const arrowOutLeft = -15;   // flecha izquierda
      const arrowOutRight = -15;  // flecha derecha
      // Eje Y (vertical)
      ctx.beginPath();
      ctx.moveTo(centerX, centerY - props.axisLength + arrowOutTop);
      ctx.lineTo(centerX, centerY + props.axisLength - arrowOutBottom);
      ctx.stroke();
      // Eje X (horizontal)
      ctx.beginPath();
      ctx.moveTo(centerX - props.axisLength + arrowOutLeft, centerY);
      ctx.lineTo(centerX + props.axisLength - arrowOutRight, centerY);
      ctx.stroke();
      // Flecha arriba
      ctx.beginPath();
      ctx.moveTo(centerX, centerY - props.axisLength - arrowOutTop);
      ctx.lineTo(centerX - 15, centerY - props.axisLength - arrowOutTop + arrowLength);
      ctx.lineTo(centerX + 15, centerY - props.axisLength - arrowOutTop + arrowLength);
      ctx.closePath();
      ctx.fillStyle = axisColor;
      ctx.fill();
      // Flecha abajo
      ctx.beginPath();
      ctx.moveTo(centerX, centerY + props.axisLength + arrowOutBottom);
      ctx.lineTo(centerX - 15, centerY + props.axisLength + arrowOutBottom - arrowLength);
      ctx.lineTo(centerX + 15, centerY + props.axisLength + arrowOutBottom - arrowLength);
      ctx.closePath();
      ctx.fill();
      // Flecha izquierda
      ctx.beginPath();
      ctx.moveTo(centerX - props.axisLength - arrowOutLeft, centerY);
      ctx.lineTo(centerX - props.axisLength - arrowOutLeft + arrowLength, centerY - 15);
      ctx.lineTo(centerX - props.axisLength - arrowOutLeft + arrowLength, centerY + 15);
      ctx.closePath();
      ctx.fill();
      // Flecha derecha
      ctx.beginPath();
      ctx.moveTo(centerX + props.axisLength + arrowOutRight, centerY);
      ctx.lineTo(centerX + props.axisLength + arrowOutRight - arrowLength, centerY - 15);
      ctx.lineTo(centerX + props.axisLength + arrowOutRight - arrowLength, centerY + 15);
      ctx.closePath();
      ctx.fill();
      ctx.restore();

      // Cuadros de hipótesis
      ctx.save();
      ctx.font = titleFont;
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      ctx.fillStyle = boxColor;
      ctx.strokeStyle = '#000'; // borde negro temporal para depuración
      ctx.lineWidth = 2;
      // H1+
      drawBox(centerX - boxW/2, margin, boxW, boxH, 'HIPÓTESIS 1+', getHypothesisText('H1', 'H1') || 'Ejemplo H1+');
      // H1-
      drawBox(centerX - boxW/2, H - margin - boxH, boxW, boxH, 'HIPÓTESIS 1-', getHypothesisText('H1', 'H0') || 'Ejemplo H1-');
      // H2-
      drawBox(margin, centerY - boxH/2, boxW, boxH, 'HIPÓTESIS 2-', getHypothesisText('H2', 'H0') || 'Ejemplo H2-');
      // H2+
      drawBox(W - margin - boxW, centerY - boxH/2, boxW, boxH, 'HIPÓTESIS 2+', getHypothesisText('H2', 'H1') || 'Ejemplo H2+');
      ctx.restore();

      // Cuadros de escenarios
      ctx.save();
      ctx.font = titleFont;
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      ctx.fillStyle = boxColor;
      ctx.strokeStyle = '#000'; // borde negro temporal para depuración
      ctx.lineWidth = 2;
      // Escenario 1 (arriba derecha)
      drawBox(centerX + offset - scenBoxW/2, centerY - offset - scenBoxH/2, scenBoxW, scenBoxH, 'ESCENARIO 1', getScenarioText(0) || 'Ejemplo Escenario 1');
      // Escenario 2 (abajo derecha)
      drawBox(centerX + offset - scenBoxW/2, centerY + offset - scenBoxH/2, scenBoxW, scenBoxH, 'ESCENARIO 2', getScenarioText(1) || 'Ejemplo Escenario 2');
      // Escenario 3 (abajo izquierda)
      drawBox(centerX - offset - scenBoxW/2, centerY + offset - scenBoxH/2, scenBoxW, scenBoxH, 'ESCENARIO 3', getScenarioText(2) || 'Ejemplo Escenario 3');
      // Escenario 4 (arriba izquierda)
      drawBox(centerX - offset - scenBoxW/2, centerY - offset - scenBoxH/2, scenBoxW, scenBoxH, 'ESCENARIO 4', getScenarioText(3) || 'Ejemplo Escenario 4');
      ctx.restore();

      // Función para dibujar un cuadro con título y texto
      function drawBox(x, y, w, h, title, text) {
        ctx.save();
        
        // Calcular altura necesaria para el texto
        const textLines = calculateTextLines(text, w - 20);
        const titleHeight = 25;
        const textHeight = textLines.length * 18;
        const padding = 16;
        const newHeight = titleHeight + textHeight + padding;
        
        // Usar la altura calculada en lugar de la fija
        const actualHeight = Math.max(h, newHeight);
        
        ctx.fillStyle = boxColor;
        ctx.strokeStyle = '#000'; // borde negro temporal para depuración
        ctx.lineWidth = 2;
        ctx.fillRect(x, y, w, actualHeight);
        ctx.strokeRect(x, y, w, actualHeight);
        ctx.font = titleFont;
        ctx.fillStyle = titleColor;
        ctx.fillText(title, x + w/2, y + 8);
        ctx.font = font;
        ctx.fillStyle = textColor;
        wrapText(ctx, text, x + w/2, y + 34, w - 20, 18);
        ctx.restore();
        // Log para depuración
        console.log('drawBox', { x, y, w, h: actualHeight, title, text, textLines: textLines.length });
      }

      // Función para calcular las líneas de texto
      function calculateTextLines(text, maxWidth) {
        if (!text) return [];
        const words = text.split(' ');
        let lines = [];
        let currentLine = '';
        
        for (let word of words) {
          const testLine = currentLine + word + ' ';
          const metrics = ctx.measureText(testLine);
          if (metrics.width > maxWidth && currentLine !== '') {
            lines.push(currentLine.trim());
            currentLine = word + ' ';
          } else {
            currentLine = testLine;
          }
        }
        if (currentLine.trim()) {
          lines.push(currentLine.trim());
        }
        return lines;
      }

      // Función para obtener texto de hipótesis
      function getHypothesisText(name, type) {
        const found = props.hypotheses.find(h => h.name_hypothesis === name && h.secondary_hypotheses === type);
        return found ? (found.description || found.variable_name || '') : '';
      }
      // Función para obtener texto de escenario
      function getScenarioText(idx) {
        const s = props.scenarios[idx];
        return s ? (s.texto || s.titulo || '') : '';
      }
      // Función para hacer saltos de línea automáticos
      function wrapText(ctx, text, x, y, maxWidth, lineHeight) {
        if (!text) return;
        const lines = calculateTextLines(text, maxWidth);
        for (let i = 0; i < lines.length; i++) {
          ctx.fillText(lines[i], x, y + i * lineHeight);
        }
      }
    }

    onMounted(() => { nextTick(drawSchwartz); });
    watch(() => props.scenarios, drawSchwartz, { deep: true });
    watch(() => props.hypotheses, drawSchwartz, { deep: true });
    watch(() => [props.width, props.height, props.boxWidth, props.boxHeight, props.offset, props.hypoOffset, props.font, props.titleFont], drawSchwartz);

    return { schwartzCanvas };
  }
};
</script>

<style scoped>
.schwartz-editable-canvas-container {
  width: 100%;
  max-width: 1000px;
  height: auto;
  display: flex;
  justify-content: center;
  align-items: center;
  background: white;
}
canvas {
  width: 100%;
  height: auto;
  border: 1px solid #ddd;
}
</style> 
 