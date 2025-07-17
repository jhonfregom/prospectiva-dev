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
    boxColor: { type: String, default: '#ffffff' },
    boxBorder: { type: String, default: '#E0E7FF' },
    titleColor: { type: String, default: '#4F46E5' },
    textColor: { type: String, default: '#1F2937' },
    axisColor: { type: String, default: '#EF4444' },
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

      // Fondo con gradiente sutil
      const bgGradient = ctx.createLinearGradient(0, 0, W, H);
      bgGradient.addColorStop(0, '#ffffff');
      bgGradient.addColorStop(1, '#f8fafc');
      ctx.fillStyle = bgGradient;
      ctx.fillRect(0, 0, W, H);

      // Ejes y flechas con estilo moderno
      ctx.save();
      
      // Sombra para los ejes
      ctx.shadowColor = 'rgba(0, 0, 0, 0.2)';
      ctx.shadowBlur = 4;
      ctx.shadowOffsetX = 2;
      ctx.shadowOffsetY = 2;
      
      ctx.strokeStyle = axisColor;
      ctx.lineWidth = axisWidth;
      const arrowLength = 25; // largo de la flecha
      const arrowWidth = 12; // ancho de la flecha
      
      // Variables individuales para controlar la proximidad de cada flecha
      const arrowOutTop = 5;    // flecha arriba - aumentar para alejar, disminuir para acercar
      const arrowOutBottom = 5; // flecha abajo - aumentar para alejar, disminuir para acercar
      const arrowOutLeft = 5;   // flecha izquierda - aumentar para alejar, disminuir para acercar
      const arrowOutRight = 5;  // flecha derecha - aumentar para alejar, disminuir para acercar
      
      // Variables para mover las líneas de los ejes
      const axisOffsetTop = 17;    // mover línea eje Y arriba/abajo
      const axisOffsetBottom = -5; // mover línea eje Y abajo/arriba
      const axisOffsetLeft = 20;   // mover línea eje X izquierda/derecha
      const axisOffsetRight = -20;  // mover línea eje X derecha/izquierda
      
      // Eje Y (vertical) con gradiente
      const gradientY = ctx.createLinearGradient(centerX - 2, centerY - props.axisLength, centerX + 2, centerY + props.axisLength);
      gradientY.addColorStop(0, axisColor);
      gradientY.addColorStop(1, '#FCA5A5');
      ctx.strokeStyle = gradientY;
      
      ctx.beginPath();
      ctx.moveTo(centerX, centerY - props.axisLength + axisOffsetTop);
      ctx.lineTo(centerX, centerY + props.axisLength + axisOffsetBottom);
      ctx.stroke();
      
      // Eje X (horizontal) con gradiente
      const gradientX = ctx.createLinearGradient(centerX - props.axisLength, centerY - 2, centerX + props.axisLength, centerY + 2);
      gradientX.addColorStop(0, axisColor);
      gradientX.addColorStop(1, '#FCA5A5');
      ctx.strokeStyle = gradientX;
      
      ctx.beginPath();
      ctx.moveTo(centerX - props.axisLength + axisOffsetLeft, centerY);
      ctx.lineTo(centerX + props.axisLength + axisOffsetRight, centerY);
      ctx.stroke();
      
      // Resetear sombra para las flechas
      ctx.shadowColor = 'transparent';
      ctx.shadowBlur = 0;
      ctx.shadowOffsetX = 0;
      ctx.shadowOffsetY = 0;
      
      // Flecha arriba
      ctx.fillStyle = axisColor;
      ctx.beginPath();
      ctx.moveTo(centerX, centerY - props.axisLength + axisOffsetTop - arrowOutTop);
      ctx.lineTo(centerX - arrowWidth, centerY - props.axisLength + axisOffsetTop + arrowLength);
      ctx.lineTo(centerX + arrowWidth, centerY - props.axisLength + axisOffsetTop + arrowLength);
      ctx.closePath();
      ctx.fill();
      
      // Flecha abajo
      ctx.beginPath();
      ctx.moveTo(centerX, centerY + props.axisLength + axisOffsetBottom + arrowOutBottom);
      ctx.lineTo(centerX - arrowWidth, centerY + props.axisLength + axisOffsetBottom - arrowLength);
      ctx.lineTo(centerX + arrowWidth, centerY + props.axisLength + axisOffsetBottom - arrowLength);
      ctx.closePath();
      ctx.fill();
      
      // Flecha izquierda
      ctx.beginPath();
      ctx.moveTo(centerX - props.axisLength + axisOffsetLeft - arrowOutLeft, centerY);
      ctx.lineTo(centerX - props.axisLength + axisOffsetLeft + arrowLength, centerY - arrowWidth);
      ctx.lineTo(centerX - props.axisLength + axisOffsetLeft + arrowLength, centerY + arrowWidth);
      ctx.closePath();
      ctx.fill();
      
      // Flecha derecha
      ctx.beginPath();
      ctx.moveTo(centerX + props.axisLength + axisOffsetRight + arrowOutRight, centerY);
      ctx.lineTo(centerX + props.axisLength + axisOffsetRight - arrowLength, centerY - arrowWidth);
      ctx.lineTo(centerX + props.axisLength + axisOffsetRight - arrowLength, centerY + arrowWidth);
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
        const titleHeight = 30;
        const textHeight = textLines.length * 18;
        const padding = 20;
        const newHeight = titleHeight + textHeight + padding;
        
        // Usar la altura calculada en lugar de la fija
        const actualHeight = Math.max(h, newHeight);
        const radius = 8; // Radio para bordes redondeados
        
        // Dibujar sombra (simulada con múltiples rectángulos)
        ctx.fillStyle = 'rgba(0, 0, 0, 0.1)';
        ctx.fillRect(x + 3, y + 3, w, actualHeight);
        ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.fillRect(x + 2, y + 2, w, actualHeight);
        
        // Fondo principal con bordes redondeados
        ctx.fillStyle = boxColor;
        ctx.strokeStyle = boxBorder;
        ctx.lineWidth = 1.5;
        
        // Dibujar rectángulo con bordes redondeados
        roundRect(ctx, x, y, w, actualHeight, radius);
        ctx.fill();
        ctx.stroke();
        
        // Título con fondo de color
        ctx.fillStyle = '#EEF2FF';
        ctx.fillRect(x, y, w, titleHeight);
        ctx.strokeStyle = boxBorder;
        ctx.lineWidth = 1;
        ctx.strokeRect(x, y, w, titleHeight);
        
        // Texto del título
        ctx.font = titleFont;
        ctx.fillStyle = titleColor;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(title, x + w/2, y + titleHeight/2);
        
        // Texto del contenido
        ctx.font = font;
        ctx.fillStyle = textColor;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        wrapText(ctx, text, x + w/2, y + titleHeight + 10, w - 20, 18);
        
        ctx.restore();
      }
      
      // Función para dibujar rectángulos con bordes redondeados
      function roundRect(ctx, x, y, width, height, radius) {
        ctx.beginPath();
        ctx.moveTo(x + radius, y);
        ctx.lineTo(x + width - radius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
        ctx.lineTo(x + width, y + height - radius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
        ctx.lineTo(x + radius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
        ctx.lineTo(x, y + radius);
        ctx.quadraticCurveTo(x, y, x + radius, y);
        ctx.closePath();
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
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
canvas {
  width: 100%;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style> 
 