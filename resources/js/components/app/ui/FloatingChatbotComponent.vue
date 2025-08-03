<template>
  <div v-if="shouldShow" class="floating-chatbot">
    <!-- Botón flotante para abrir/cerrar el chat -->
    <div 
      class="chatbot-toggle"
      @click="toggleChat"
      :class="{ 'is-active': isOpen }"
    >
      <i class="fas fa-robot"></i>
      <span v-if="isOpen" class="close-icon">×</span>
    </div>

    <!-- Ventana del chat -->
    <div 
      class="chatbot-window"
      :class="{ 'is-open': isOpen }"
    >
      <!-- Header del chat -->
      <div class="chatbot-header">
        <h3>Asistente IA</h3>
        <button @click="toggleChat" class="close-btn">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Área de mensajes -->
      <div class="chatbot-messages" ref="messagesContainer">
        <div 
          v-for="(message, index) in messages" 
          :key="index"
          class="message"
          :class="message.type"
        >
          <div class="message-content">
            <div v-if="message.type === 'user'" class="user-avatar">
              <i class="fas fa-user"></i>
            </div>
            <div v-else class="bot-avatar">
              <i class="fas fa-robot"></i>
            </div>
            <div class="message-text" v-html="formatMessage(message.text)"></div>
          </div>
          <div class="message-time">{{ formatTime(message.timestamp) }}</div>
        </div>
        
        <!-- Indicador de escritura -->
        <div v-if="isTyping" class="message bot">
          <div class="message-content">
            <div class="bot-avatar">
              <i class="fas fa-robot"></i>
            </div>
            <div class="typing-indicator">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Área de entrada -->
      <div class="chatbot-input">
        <textarea
          v-model="currentMessage"
          @keydown="handleKeydown"
          placeholder="Escribe tu texto para que lo analice o corrija... (Shift+Enter para nueva línea, Enter para enviar)"
          rows="3"
          :disabled="isTyping"
        ></textarea>
        <button 
          @click="sendMessage"
          :disabled="!currentMessage.trim() || isTyping"
          class="send-btn"
        >
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FloatingChatbotComponent',
  data() {
    return {
      isOpen: false,
      messages: [],
      currentMessage: '',
      isTyping: false,
      ollamaUrl: '/ollama/generate'
    }
  },
  computed: {
    shouldShow() {
      // Obtener la ruta actual
      const currentPath = window.location.pathname;
      
      // Ocultar en login y registro
      const hiddenPaths = ['/login', '/register', '/'];
      
      return !hiddenPaths.includes(currentPath);
    }
  },
  mounted() {
    console.log('FloatingChatbotComponent mounted successfully!');
    // Mensaje de bienvenida
    this.addMessage({
      type: 'bot',
      text: '¡Hola! Soy tu asistente IA. Puedo ayudarte a corregir y analizar textos. Envía tu texto y te daré feedback detallado.',
      timestamp: new Date()
    });
  },
  methods: {
    toggleChat() {
      this.isOpen = !this.isOpen;
      if (this.isOpen) {
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      }
    },
    
    async sendMessage() {
      if (!this.currentMessage.trim() || this.isTyping) return;
      
      const userMessage = this.currentMessage.trim();
      
      // Agregar mensaje del usuario
      this.addMessage({
        type: 'user',
        text: userMessage,
        timestamp: new Date()
      });
      
      this.currentMessage = '';
      this.isTyping = true;
      
      try {
        // Crear prompt para Ollama
        const prompt = this.createPrompt(userMessage);
        
        // Enviar a Ollama
        const response = await this.callOllama(prompt);
        
        // Agregar respuesta del bot
        this.addMessage({
          type: 'bot',
          text: response,
          timestamp: new Date()
        });
        
      } catch (error) {
        console.error('Error al comunicarse con Ollama:', error);
        this.addMessage({
          type: 'bot',
          text: 'Lo siento, no pude procesar tu solicitud. Asegúrate de que Ollama esté ejecutándose en tu sistema.',
          timestamp: new Date()
        });
      } finally {
        this.isTyping = false;
        this.$nextTick(() => {
          this.scrollToBottom();
        });
      }
    },
    
    createPrompt(userText) {
      return `Eres un profesor experto en análisis prospectivo y corrección de textos. Analiza el siguiente texto del estudiante y proporciona:

1. **Correcciones gramaticales y ortográficas** (si las hay)
2. **Análisis de estructura y coherencia**
3. **Sugerencias de mejora específicas**
4. **Comentarios sobre claridad y precisión**
5. **Puntuación del 1 al 10**

Responde de manera constructiva y educativa. Si el texto está relacionado con prospectiva, variables, análisis de escenarios o metodologías similares, incluye comentarios específicos sobre la calidad del análisis prospectivo.

Texto del estudiante:
"${userText}"

Responde en español de manera clara y estructurada.`;
    },
    
    async callOllama(prompt) {
      const response = await fetch(this.ollamaUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          model: 'gemma3:4b',
          prompt: prompt,
          stream: false,
          options: {
            temperature: 0.3,
            top_p: 0.7,
            max_tokens: 4000,
            num_predict: 100,
            top_k: 15,
            repeat_penalty: 1.05
          }
        })
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();
      return data.response || 'No se recibió respuesta del modelo.';
    },
    
    addMessage(message) {
      this.messages.push(message);
    },
    
    formatMessage(text) {
      // Convertir saltos de línea en <br> y hacer el texto más legible
      return text
        .replace(/\n/g, '<br>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>');
    },
    
    formatTime(timestamp) {
      return timestamp.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    scrollToBottom() {
      const container = this.$refs.messagesContainer;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    },
    
    handleKeydown(event) {
      if (event.key === 'Enter') {
        if (event.shiftKey) {
          // Shift+Enter: permitir nueva línea
          return;
        } else {
          // Enter sin Shift: enviar mensaje
          event.preventDefault();
          this.sendMessage();
        }
      }
    }
  }
}
</script>

<style scoped>
.floating-chatbot {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  /* Debug styles - asegurar que sea visible */
  display: block !important;
  visibility: visible !important;
  opacity: 1 !important;
}

.chatbot-toggle {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
  color: white;
  font-size: 24px;
  position: relative;
  /* Debug styles - hacer más visible */
  border: 3px solid #ff0000;
  animation: pulse 2s infinite;
}

.chatbot-toggle:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
}

.chatbot-toggle.is-active {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
}

.close-icon {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ff4757;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

.chatbot-window {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 400px;
  height: 500px;
  background: white;
  border-radius: 15px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  opacity: 0;
  visibility: hidden;
  transform: translateY(20px) scale(0.9);
  transition: all 0.3s ease;
}

.chatbot-window.is-open {
  opacity: 1;
  visibility: visible;
  transform: translateY(0) scale(1);
}

.chatbot-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 15px 20px;
  border-radius: 15px 15px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chatbot-header h3 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 18px;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.2s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.chatbot-messages {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  background: #f8f9fa;
}

.message {
  margin-bottom: 15px;
  animation: fadeIn 0.3s ease;
}

.message-content {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.user-avatar, .bot-avatar {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-avatar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.bot-avatar {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
  color: white;
}

.message-text {
  background: white;
  padding: 12px 15px;
  border-radius: 15px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  max-width: 280px;
  word-wrap: break-word;
  line-height: 1.4;
}

.message.user .message-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.message.bot .message-text {
  background: white;
  color: #333;
}

.message-time {
  font-size: 11px;
  color: #999;
  margin-top: 5px;
  margin-left: 45px;
}

.typing-indicator {
  display: flex;
  gap: 4px;
  padding: 12px 15px;
  background: white;
  border-radius: 15px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #ccc;
  animation: typing 1.4s infinite ease-in-out;
}

.typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

.chatbot-input {
  padding: 15px;
  background: white;
  border-radius: 0 0 15px 15px;
  display: flex;
  gap: 10px;
  align-items: flex-end;
}

.chatbot-input textarea {
  flex: 1;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  padding: 10px;
  resize: none;
  font-family: inherit;
  font-size: 14px;
  line-height: 1.4;
  transition: border-color 0.2s ease;
}

.chatbot-input textarea:focus {
  outline: none;
  border-color: #667eea;
}

.chatbot-input textarea:disabled {
  background: #f8f9fa;
  color: #6c757d;
}

.send-btn {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 50%;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.send-btn:hover:not(:disabled) {
  transform: scale(1.1);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.send-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes typing {
  0%, 80%, 100% {
    transform: scale(0.8);
    opacity: 0.5;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(255, 0, 0, 0.7);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(255, 0, 0, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(255, 0, 0, 0);
  }
}

/* Scrollbar personalizado */
.chatbot-messages::-webkit-scrollbar {
  width: 6px;
}

.chatbot-messages::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.chatbot-messages::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.chatbot-messages::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive */
@media (max-width: 480px) {
  .chatbot-window {
    width: 350px;
    height: 450px;
    right: -50px;
  }
  
  .message-text {
    max-width: 230px;
  }
}
</style> 