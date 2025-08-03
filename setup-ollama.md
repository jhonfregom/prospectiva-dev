# Configuración de Ollama para el Chatbot IA

## 🚀 Instalación de Ollama

### Windows (WSL2 recomendado)
```bash
# Instalar WSL2 si no lo tienes
wsl --install

# En WSL2, ejecutar:
curl -fsSL https://ollama.ai/install.sh | sh

# O descargar desde: https://ollama.ai/download
```

### macOS
```bash
curl -fsSL https://ollama.ai/install.sh | sh
```

### Linux
```bash
curl -fsSL https://ollama.ai/install.sh | sh
```

## 📥 Descargar Modelos

### Modelo recomendado para análisis de textos:
```bash
# Llama 2 (7B parámetros - buen balance)
ollama pull llama2

# O Mistral (muy bueno para español)
ollama pull mistral

# O Phi-2 (más pequeño, rápido)
ollama pull phi
```

## 🏃‍♂️ Ejecutar Ollama

```bash
# Iniciar el servicio
ollama serve

# En otra terminal, probar el modelo
ollama run llama2
```

## 🔧 Configuración del Chatbot

El chatbot está configurado para conectarse a:
- **URL**: `http://localhost:11434/api/generate`
- **Modelo por defecto**: `llama2`
- **Puerto**: `11434` (puerto por defecto de Ollama)

## 🧪 Probar la Conexión

### 1. Verificar que Ollama esté ejecutándose:
```bash
curl http://localhost:11434/api/tags
```

### 2. Probar una consulta simple:
```bash
curl -X POST http://localhost:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{
    "model": "llama2",
    "prompt": "Hola, ¿cómo estás?",
    "stream": false
  }'
```

## 🎯 Funcionalidades del Chatbot

### Análisis de Textos Académicos:
- ✅ Corrección gramatical y ortográfica
- ✅ Análisis de estructura y coherencia
- ✅ Sugerencias de mejora
- ✅ Feedback específico para prospectiva
- ✅ Puntuación del 1 al 10

### Casos de Uso:
- 📝 **Ensayos**: Corrección y análisis de estructura
- 🔬 **Trabajos científicos**: Revisión de metodología
- 💻 **Código**: Análisis de lógica y buenas prácticas
- 📊 **Análisis prospectivo**: Evaluación de variables y escenarios

## ⚙️ Personalización

### Cambiar el modelo en el componente:
Editar `resources/js/components/app/ui/FloatingChatbotComponent.vue`:

```javascript
// Línea 150 - cambiar el modelo
model: 'mistral', // en lugar de 'llama2'
```

### Ajustar parámetros:
```javascript
options: {
  temperature: 0.7,    // Creatividad (0-1)
  top_p: 0.9,         // Diversidad
  max_tokens: 1000    // Longitud máxima
}
```

## 🚨 Solución de Problemas

### Error: "Connection refused"
- Verificar que Ollama esté ejecutándose: `ollama serve`
- Comprobar el puerto: `netstat -an | grep 11434`

### Error: "Model not found"
- Descargar el modelo: `ollama pull llama2`
- Verificar modelos disponibles: `ollama list`

### Error: "Out of memory"
- Usar un modelo más pequeño: `ollama pull phi`
- Aumentar la RAM disponible para WSL2

## 📱 Uso en la Aplicación

1. **Abrir el chatbot**: Hacer clic en el botón flotante (robot)
2. **Escribir texto**: Enviar el texto a analizar
3. **Recibir feedback**: La IA proporcionará análisis detallado
4. **Cerrar**: Hacer clic en el botón de cerrar o en el robot

## 🔒 Privacidad

- ✅ **100% local**: Los textos nunca salen de tu servidor
- ✅ **Sin límites**: Sin costos por token
- ✅ **Sin registro**: No necesitas cuentas externas
- ✅ **Datos seguros**: Perfecto para contenido académico

## 📈 Rendimiento

### Requisitos mínimos:
- **RAM**: 8GB (16GB recomendado)
- **CPU**: 4 cores
- **Almacenamiento**: 5GB para modelos

### Tiempos de respuesta:
- **Llama 2**: ~2-5 segundos
- **Mistral**: ~1-3 segundos
- **Phi-2**: ~1-2 segundos

## 🎨 Personalización Visual

El chatbot incluye:
- 🎨 Diseño moderno con gradientes
- 📱 Responsive para móviles
- ⚡ Animaciones suaves
- 🎯 Indicador de escritura
- 📅 Timestamps en mensajes 