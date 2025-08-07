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
    offset: { type: Number, default: 150 }, 
    hypoOffset: { type: Number, default: 210 }, 
    axisLength: { type: Number, default: 100 } 
  },
  setup(props) {
    const schwartzCanvas = ref(null);

    function drawSchwartz() {
      const canvas = schwartzCanvas.value;
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);

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

      const bgGradient = ctx.createLinearGradient(0, 0, W, H);
      bgGradient.addColorStop(0, '#ffffff');
      bgGradient.addColorStop(1, '#f8fafc');
      ctx.fillStyle = bgGradient;
      ctx.fillRect(0, 0, W, H);

      ctx.save();

      ctx.shadowColor = 'rgba(0, 0, 0, 0.2)';
      ctx.shadowBlur = 4;
      ctx.shadowOffsetX = 2;
      ctx.shadowOffsetY = 2;
      
      ctx.strokeStyle = axisColor;
      ctx.lineWidth = axisWidth;
      const arrowLength = 25; 
      const arrowWidth = 12; 

      const arrowOutTop = 5;    
      const arrowOutBottom = 5; 
      const arrowOutLeft = 5;   
      const arrowOutRight = 5;  

      const axisOffsetTop = 17;    
      const axisOffsetBottom = -5; 
      const axisOffsetLeft = 20;   
      const axisOffsetRight = -20;  

      const gradientY = ctx.createLinearGradient(centerX - 2, centerY - props.axisLength, centerX + 2, centerY + props.axisLength);
      gradientY.addColorStop(0, axisColor);
      gradientY.addColorStop(1, '#FCA5A5');
      ctx.strokeStyle = gradientY;
      
      ctx.beginPath();
      ctx.moveTo(centerX, centerY - props.axisLength + axisOffsetTop);
      ctx.lineTo(centerX, centerY + props.axisLength + axisOffsetBottom);
      ctx.stroke();

      const gradientX = ctx.createLinearGradient(centerX - props.axisLength, centerY - 2, centerX + props.axisLength, centerY + 2);
      gradientX.addColorStop(0, axisColor);
      gradientX.addColorStop(1, '#FCA5A5');
      ctx.strokeStyle = gradientX;
      
      ctx.beginPath();
      ctx.moveTo(centerX - props.axisLength + axisOffsetLeft, centerY);
      ctx.lineTo(centerX + props.axisLength + axisOffsetRight, centerY);
      ctx.stroke();

      ctx.shadowColor = 'transparent';
      ctx.shadowBlur = 0;
      ctx.shadowOffsetX = 0;
      ctx.shadowOffsetY = 0;

      ctx.fillStyle = axisColor;
      ctx.beginPath();
      ctx.moveTo(centerX, centerY - props.axisLength + axisOffsetTop - arrowOutTop);
      ctx.lineTo(centerX - arrowWidth, centerY - props.axisLength + axisOffsetTop + arrowLength);
      ctx.lineTo(centerX + arrowWidth, centerY - props.axisLength + axisOffsetTop + arrowLength);
      ctx.closePath();
      ctx.fill();

      ctx.beginPath();
      ctx.moveTo(centerX, centerY + props.axisLength + axisOffsetBottom + arrowOutBottom);
      ctx.lineTo(centerX - arrowWidth, centerY + props.axisLength + axisOffsetBottom - arrowLength);
      ctx.lineTo(centerX + arrowWidth, centerY + props.axisLength + axisOffsetBottom - arrowLength);
      ctx.closePath();
      ctx.fill();

      ctx.beginPath();
      ctx.moveTo(centerX - props.axisLength + axisOffsetLeft - arrowOutLeft, centerY);
      ctx.lineTo(centerX - props.axisLength + axisOffsetLeft + arrowLength, centerY - arrowWidth);
      ctx.lineTo(centerX - props.axisLength + axisOffsetLeft + arrowLength, centerY + arrowWidth);
      ctx.closePath();
      ctx.fill();

      ctx.beginPath();
      ctx.moveTo(centerX + props.axisLength + axisOffsetRight + arrowOutRight, centerY);
      ctx.lineTo(centerX + props.axisLength + axisOffsetRight - arrowLength, centerY - arrowWidth);
      ctx.lineTo(centerX + props.axisLength + axisOffsetRight - arrowLength, centerY + arrowWidth);
      ctx.closePath();
      ctx.fill();
      
      ctx.restore();

      ctx.save();
      ctx.font = titleFont;
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      ctx.fillStyle = boxColor;
      ctx.strokeStyle = '#000'; 
      ctx.lineWidth = 2;
      
      drawBox(centerX - boxW/2, margin, boxW, boxH, 'HIPÓTESIS 1+', getHypothesisText('H1', 'H1') || 'Ejemplo H1+');
      
      drawBox(centerX - boxW/2, H - margin - boxH, boxW, boxH, 'HIPÓTESIS 1-', getHypothesisText('H1', 'H0') || 'Ejemplo H1-');
      
      drawBox(margin, centerY - boxH/2, boxW, boxH, 'HIPÓTESIS 2-', getHypothesisText('H2', 'H0') || 'Ejemplo H2-');
      
      drawBox(W - margin - boxW, centerY - boxH/2, boxW, boxH, 'HIPÓTESIS 2+', getHypothesisText('H2', 'H1') || 'Ejemplo H2+');
      ctx.restore();

      ctx.save();
      ctx.font = titleFont;
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      ctx.fillStyle = boxColor;
      ctx.strokeStyle = '#000'; 
      ctx.lineWidth = 2;
      
      drawBox(centerX + offset - scenBoxW/2, centerY - offset - scenBoxH/2, scenBoxW, scenBoxH, 'ESCENARIO 1', getScenarioText(0) || 'Ejemplo Escenario 1');
      
      drawBox(centerX + offset - scenBoxW/2, centerY + offset - scenBoxH/2, scenBoxW, scenBoxH, 'ESCENARIO 2', getScenarioText(1) || 'Ejemplo Escenario 2');
      
      drawBox(centerX - offset - scenBoxW/2, centerY + offset - scenBoxH/2, scenBoxW, scenBoxH, 'ESCENARIO 3', getScenarioText(2) || 'Ejemplo Escenario 3');
      
      drawBox(centerX - offset - scenBoxW/2, centerY - offset - scenBoxH/2, scenBoxW, scenBoxH, 'ESCENARIO 4', getScenarioText(3) || 'Ejemplo Escenario 4');
      ctx.restore();

      function drawBox(x, y, w, h, title, text) {
        ctx.save();

        const textLines = calculateTextLines(text, w - 20);
        const titleHeight = 30;
        const textHeight = textLines.length * 18;
        const padding = 20;
        const newHeight = titleHeight + textHeight + padding;

        const actualHeight = Math.max(h, newHeight);
        const radius = 8; 

        ctx.fillStyle = 'rgba(0, 0, 0, 0.1)';
        ctx.fillRect(x + 3, y + 3, w, actualHeight);
        ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.fillRect(x + 2, y + 2, w, actualHeight);

        ctx.fillStyle = boxColor;
        ctx.strokeStyle = boxBorder;
        ctx.lineWidth = 1.5;

        roundRect(ctx, x, y, w, actualHeight, radius);
        ctx.fill();
        ctx.stroke();

        ctx.fillStyle = '#EEF2FF';
        ctx.fillRect(x, y, w, titleHeight);
        ctx.strokeStyle = boxBorder;
        ctx.lineWidth = 1;
        ctx.strokeRect(x, y, w, titleHeight);

        ctx.font = titleFont;
        ctx.fillStyle = titleColor;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(title, x + w/2, y + titleHeight/2);

        ctx.font = font;
        ctx.fillStyle = textColor;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'top';
        wrapText(ctx, text, x + w/2, y + titleHeight + 10, w - 20, 18);
        
        ctx.restore();
      }

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

      function getHypothesisText(name, type) {
        const found = props.hypotheses.find(h => h.name_hypothesis === name && h.secondary_hypotheses === type);
        return found ? (found.description || found.variable_name || '') : '';
      }
      
      function getScenarioText(idx) {
        const s = props.scenarios[idx];
        return s ? (s.texto || s.titulo || '') : '';
      }
      
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