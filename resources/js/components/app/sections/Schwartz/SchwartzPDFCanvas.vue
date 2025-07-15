<template>
  <div class="schwartz-pdf-canvas-container">
    <canvas ref="schwartzCanvas" :width="width" :height="height"></canvas>
  </div>
</template>

<script>
import { onMounted, ref, watch, nextTick } from 'vue';

export default {
  name: 'SchwartzPDFCanvas',
  props: {
    scenarios: { type: Array, required: true },
    hypotheses: { type: Array, required: true },
    width: { type: Number, default: 900 },
    height: { type: Number, default: 700 }
  },
  setup(props) {
    const schwartzCanvas = ref(null);

    function drawSchwartz() {
      const canvas = schwartzCanvas.value;
      if (!canvas) return;
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      // Dimensiones base
      const W = props.width;
      const H = props.height;
      const centerX = W / 2;
      const centerY = H / 2;
      const minBoxW = W * 0.23; // 210 para 900px
      const minBoxH = H * 0.13; // 90 para 700px
      const font = '70px Arial';
      const titleFont = 'bold 80px Arial';
      ctx.font = font;

      // Función para calcular alto de texto
      function getTextHeight(text, maxWidth, lineHeight) {
        if (!text) return lineHeight;
        const words = text.split(' ');
        let line = '';
        let lines = [];
        for (let n = 0; n < words.length; n++) {
          const testLine = line + words[n] + ' ';
          const metrics = ctx.measureText(testLine);
          if (metrics.width > maxWidth && n > 0) {
            lines.push(line);
            line = words[n] + ' ';
          } else {
            line = testLine;
          }
        }
        lines.push(line);
        return lines.length * lineHeight;
      }

      // Calcular tamaños de cuadros de hipótesis
      ctx.font = titleFont;
      const hypoTitles = ['HIPÓTESIS 1+', 'HIPÓTESIS 1-', 'HIPÓTESIS 2-', 'HIPÓTESIS 2+'];
      const hypoTexts = [
        getHypothesisText('H1', 'H1'),
        getHypothesisText('H1', 'H0'),
        getHypothesisText('H2', 'H0'),
        getHypothesisText('H2', 'H1')
      ];
      let boxW = minBoxW;
      let boxH = minBoxH;
      ctx.font = font;
      for (let i = 0; i < 4; i++) {
        const titleWidth = ctx.measureText(hypoTitles[i]).width + 40;
        const textWidth = Math.max(...hypoTexts[i].split('\n').map(line => ctx.measureText(line).width)) + 40;
        boxW = Math.max(boxW, titleWidth, textWidth);
        const textHeight = getTextHeight(hypoTexts[i], boxW - 20, 100);
        boxH = Math.max(boxH, 38 + textHeight);
      }

      // Calcular tamaños de cuadros de escenarios
      ctx.font = titleFont;
      const scenarioTitles = ['ESCENARIO 1', 'ESCENARIO 2', 'ESCENARIO 3', 'ESCENARIO 4'];
      const scenarioTexts = [getScenarioText(0), getScenarioText(1), getScenarioText(2), getScenarioText(3)];
      let scenBoxW = minBoxW;
      let scenBoxH = minBoxH;
      ctx.font = font;
      for (let i = 0; i < 4; i++) {
        const titleWidth = ctx.measureText(scenarioTitles[i]).width + 40;
        const textWidth = Math.max(...scenarioTexts[i].split('\n').map(line => ctx.measureText(line).width)) + 40;
        scenBoxW = Math.max(scenBoxW, titleWidth, textWidth);
        const textHeight = getTextHeight(scenarioTexts[i], scenBoxW - 20, 100);
        scenBoxH = Math.max(scenBoxH, 38 + textHeight);
      }

      // Offsets proporcionales
      const hypoOffset = Math.max(H * 0.25, boxH/2 + H * 0.15);
      const offset = Math.max(W * 0.15, scenBoxW/2 + W * 0.07);

      // Calcular bounding box total para centrar
      const left = centerX - hypoOffset - boxW/2;
      const right = centerX + hypoOffset + boxW/2;
      const top = centerY - hypoOffset - boxH/2;
      const bottom = centerY + hypoOffset + boxH/2;
      const totalW = right - left;
      const totalH = bottom - top;
      const dx = (W - totalW) / 2 - (left - 0);
      const dy = (H - totalH) / 2 - (top - 0);

      // Fondo
      ctx.fillStyle = '#fff';
      ctx.fillRect(0, 0, W, H);

      // Ejes y flechas
      ctx.save();
      ctx.strokeStyle = 'red';
      ctx.lineWidth = Math.max(4, W * 0.007);
      // Eje Y
      ctx.beginPath();
      ctx.moveTo(centerX + dx, centerY - hypoOffset + boxH/2 + dy + H * 0.014);
      ctx.lineTo(centerX + dx, centerY + hypoOffset - boxH/2 + dy - H * 0.014);
      ctx.stroke();
      // Eje X
      ctx.beginPath();
      ctx.moveTo(centerX - hypoOffset + boxW/2 + dx + W * 0.011, centerY + dy);
      ctx.lineTo(centerX + hypoOffset - boxW/2 + dx - W * 0.011, centerY + dy);
      ctx.stroke();
      // Flecha arriba
      ctx.beginPath();
      ctx.moveTo(centerX + dx, centerY - hypoOffset + boxH/2 + dy + H * 0.014);
      ctx.lineTo(centerX + dx - W * 0.017, centerY - hypoOffset + boxH/2 + dy + H * 0.057);
      ctx.lineTo(centerX + dx + W * 0.017, centerY - hypoOffset + boxH/2 + dy + H * 0.057);
      ctx.closePath();
      ctx.fillStyle = 'red';
      ctx.fill();
      // Flecha abajo
      ctx.beginPath();
      ctx.moveTo(centerX + dx, centerY + hypoOffset - boxH/2 + dy - H * 0.014);
      ctx.lineTo(centerX + dx - W * 0.017, centerY + hypoOffset - boxH/2 + dy - H * 0.057);
      ctx.lineTo(centerX + dx + W * 0.017, centerY + hypoOffset - boxH/2 + dy - H * 0.057);
      ctx.closePath();
      ctx.fill();
      // Flecha izquierda
      ctx.beginPath();
      ctx.moveTo(centerX - hypoOffset + boxW/2 + dx + W * 0.011, centerY + dy);
      ctx.lineTo(centerX - hypoOffset + boxW/2 + dx + W * 0.045, centerY + dy - H * 0.021);
      ctx.lineTo(centerX - hypoOffset + boxW/2 + dx + W * 0.045, centerY + dy + H * 0.021);
      ctx.closePath();
      ctx.fill();
      // Flecha derecha
      ctx.beginPath();
      ctx.moveTo(centerX + hypoOffset - boxW/2 + dx - W * 0.011, centerY + dy);
      ctx.lineTo(centerX + hypoOffset - boxW/2 + dx - W * 0.045, centerY + dy - H * 0.021);
      ctx.lineTo(centerX + hypoOffset - boxW/2 + dx - W * 0.045, centerY + dy + H * 0.021);
      ctx.closePath();
      ctx.fill();
      ctx.restore();

      // Cuadros de hipótesis
      ctx.save();
      ctx.font = titleFont;
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      ctx.fillStyle = '#EEF2FF';
      ctx.strokeStyle = '#E0E7FF';
      ctx.lineWidth = 2;
      // H1+
      ctx.fillRect(centerX - boxW/2 + dx, centerY - hypoOffset - boxH/2 + dy, boxW, boxH);
      ctx.strokeRect(centerX - boxW/2 + dx, centerY - hypoOffset - boxH/2 + dy, boxW, boxH);
      ctx.fillStyle = '#4F46E5';
      ctx.fillText('HIPÓTESIS 1+', centerX + dx, centerY - hypoOffset - boxH/2 + dy + 8);
      ctx.font = font;
      ctx.fillStyle = '#222';
      wrapText(ctx, getHypothesisText('H1', 'H1'), centerX + dx, centerY - hypoOffset - boxH/2 + dy + 30, boxW - 20, 100);
      // H1-
      ctx.font = titleFont;
      ctx.fillStyle = '#EEF2FF';
      ctx.fillRect(centerX - boxW/2 + dx, centerY + hypoOffset - boxH/2 + dy, boxW, boxH);
      ctx.strokeRect(centerX - boxW/2 + dx, centerY + hypoOffset - boxH/2 + dy, boxW, boxH);
      ctx.fillStyle = '#4F46E5';
      ctx.fillText('HIPÓTESIS 1-', centerX + dx, centerY + hypoOffset - boxH/2 + dy + 8);
      ctx.font = font;
      ctx.fillStyle = '#222';
      wrapText(ctx, getHypothesisText('H1', 'H0'), centerX + dx, centerY + hypoOffset - boxH/2 + dy + 30, boxW - 20, 100);
      // H2-
      ctx.font = titleFont;
      ctx.fillStyle = '#EEF2FF';
      ctx.fillRect(centerX - hypoOffset - boxW/2 + dx, centerY - boxH/2 + dy, boxW, boxH);
      ctx.strokeRect(centerX - hypoOffset - boxW/2 + dx, centerY - boxH/2 + dy, boxW, boxH);
      ctx.fillStyle = '#4F46E5';
      ctx.fillText('HIPÓTESIS 2-', centerX - hypoOffset + dx, centerY - boxH/2 + dy + 8);
      ctx.font = font;
      ctx.fillStyle = '#222';
      wrapText(ctx, getHypothesisText('H2', 'H0'), centerX - hypoOffset + dx, centerY - boxH/2 + dy + 30, boxW - 20, 100);
      // H2+
      ctx.font = titleFont;
      ctx.fillStyle = '#EEF2FF';
      ctx.fillRect(centerX + hypoOffset - boxW/2 + dx, centerY - boxH/2 + dy, boxW, boxH);
      ctx.strokeRect(centerX + hypoOffset - boxW/2 + dx, centerY - boxH/2 + dy, boxW, boxH);
      ctx.fillStyle = '#4F46E5';
      ctx.fillText('HIPÓTESIS 2+', centerX + hypoOffset + dx, centerY - boxH/2 + dy + 8);
      ctx.font = font;
      ctx.fillStyle = '#222';
      wrapText(ctx, getHypothesisText('H2', 'H1'), centerX + hypoOffset + dx, centerY - boxH/2 + dy + 30, boxW - 20, 100);
      ctx.restore();

      // Cuadros de escenarios
      ctx.save();
      ctx.font = titleFont;
      ctx.textAlign = 'center';
      ctx.textBaseline = 'top';
      ctx.fillStyle = '#EEF2FF';
      ctx.strokeStyle = '#E0E7FF';
      ctx.lineWidth = 2;
      // Escenario 1 (arriba derecha)
      drawScenarioBox(centerX + offset + dx, centerY - offset + dy, 1, 0);
      // Escenario 2 (abajo derecha)
      drawScenarioBox(centerX + offset + dx, centerY + offset + dy, 2, 1);
      // Escenario 3 (abajo izquierda)
      drawScenarioBox(centerX - offset + dx, centerY + offset + dy, 3, 2);
      // Escenario 4 (arriba izquierda)
      drawScenarioBox(centerX - offset + dx, centerY - offset + dy, 4, 3);
      ctx.restore();

      // Función para dibujar cuadros de escenario
      function drawScenarioBox(x, y, num, idx) {
        ctx.save();
        ctx.fillStyle = '#fff';
        ctx.strokeStyle = '#E0E7FF';
        ctx.lineWidth = 2;
        ctx.fillRect(x - scenBoxW/2, y - scenBoxH/2, scenBoxW, scenBoxH);
        ctx.strokeRect(x - scenBoxW/2, y - scenBoxH/2, scenBoxW, scenBoxH);
        ctx.font = titleFont;
        ctx.fillStyle = '#4F46E5';
        ctx.fillText(`ESCENARIO ${num}`, x, y - scenBoxH/2 + 8);
        ctx.font = font;
        ctx.fillStyle = '#222';
        wrapText(ctx, getScenarioText(idx), x, y - scenBoxH/2 + 30, scenBoxW - 20, 100);
        ctx.restore();
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
        const words = text.split(' ');
        let line = '';
        let lines = [];
        for (let n = 0; n < words.length; n++) {
          const testLine = line + words[n] + ' ';
          const metrics = ctx.measureText(testLine);
          if (metrics.width > maxWidth && n > 0) {
            lines.push(line);
            line = words[n] + ' ';
          } else {
            line = testLine;
          }
        }
        lines.push(line);
        for (let i = 0; i < lines.length; i++) {
          ctx.fillText(lines[i], x, y + i * lineHeight);
        }
      }
    }

    onMounted(() => { nextTick(drawSchwartz); });
    watch(() => props.scenarios, drawSchwartz, { deep: true });
    watch(() => props.hypotheses, drawSchwartz, { deep: true });
    watch(() => [props.width, props.height], drawSchwartz);

    return { schwartzCanvas };
  }
};
</script>

<style scoped>
.schwartz-pdf-canvas-container {
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
 