<template>
  <div 
    v-if="shouldShow" 
    class="floating-bubble"
    :style="{ left: position.x + 'px', top: position.y + 'px' }"
    @mousedown="startDrag"
    @touchstart="startDrag"
  >
    <!-- Botón principal de la burbuja -->
    <div 
      class="bubble-toggle"
      @click="toggleMenu"
      :class="{ 'is-active': isMenuOpen }"
      title="Herramientas de prospectiva: Notas, IA e Información"
    >
      <i class="fas fa-magic"></i>
    </div>

    <!-- Menú desplegable -->
    <div 
      class="bubble-menu"
      :class="{ 'is-open': isMenuOpen }"
    >
      <!-- Opción de Notas -->
      <div class="menu-option" @click="openNotes" title="Registrar observaciones y reflexiones sobre el entorno que influye en la organización">
        <div class="option-icon">
          <i class="fas fa-sticky-note"></i>
        </div>
        <span class="option-text">Notas</span>
      </div>

             <!-- Opción de IA -->
       <div class="menu-option" @click="openAI" title="Asistente IA para análisis, corrección y mejora de textos">
         <div class="option-icon">
           <i class="fas fa-robot"></i>
         </div>
         <span class="option-text">Asistente IA</span>
       </div>

       <!-- Opción de Información -->
       <div class="menu-option" @click="showOrientingText" title="Ver texto orientador">
         <div class="option-icon">
           <i class="fas fa-info-circle"></i>
         </div>
         <span class="option-text">Información</span>
       </div>


    </div>

         <!-- Modal de Notas -->
     <div 
       class="modal-overlay"
       :class="{ 'is-open': isNotesOpen }"
       @click="closeNotes"
     >
       <div 
         class="modal-content notes-modal"
         @click.stop
       >
         <div class="modal-header">
           <h3>Mis Notas</h3>
           <button @click="closeNotes" class="close-btn">
             <i class="fas fa-times"></i>
           </button>
         </div>
         
         <div class="notes-content">
           <div class="notes-sidebar">
             <div class="notes-header">
               <h4>Mis Notas</h4>
               <button @click="newNote" class="new-note-btn">
                 <i class="fas fa-plus"></i> Nueva
               </button>
             </div>
             <div class="notes-list">
               <div 
                 v-for="(note, index) in notes" 
                 :key="index"
                 class="note-item"
                 @click="selectNote(index)"
                 :class="{ 'selected': selectedNoteIndex === index }"
               >
                 <div class="note-preview">
                   <h5>{{ note.title || 'Nota sin título' }}</h5>
                   <p>{{ note.content ? (note.content.substring(0, 80) + (note.content.length > 80 ? '...' : '')) : 'Sin contenido' }}</p>
                   <small>{{ formatDate(note.updated_at) }}</small>
                 </div>
                 <button @click.stop="deleteNote(index)" class="delete-note-btn" title="Eliminar nota">
                   <i class="fas fa-trash"></i>
                 </button>
               </div>
             </div>
           </div>
           
           <div class="note-editor">
             <div class="editor-header">
               <input 
                 v-model="currentNote.title" 
                 placeholder="Título de la nota"
                 class="note-title-input"
               />
               <button @click="saveNote" class="save-btn" :disabled="!currentNote.title.trim() && !currentNote.content.trim()">
                 <i class="fas fa-save"></i> Guardar
               </button>
             </div>
             <textarea 
               v-model="currentNote.content" 
               placeholder="Escribe tu nota aquí..."
               class="note-content-input"
               rows="25"
             ></textarea>
           </div>
         </div>
       </div>
     </div>

     <!-- Modal del Texto Orientador -->
     <div 
       class="modal-overlay"
       :class="{ 'is-open': isOrientingTextOpen }"
       @click="closeOrientingText"
     >
       <div 
         class="modal-content orienting-modal"
         @click.stop
       >
         <div class="modal-header">
           <h3>Texto Orientador</h3>
           <button @click="closeOrientingText" class="close-btn">
             <i class="fas fa-times"></i>
           </button>
         </div>
         
         <div class="orienting-content">
           <div class="orienting-icon">
             <i class="fas fa-lightbulb"></i>
           </div>
           <p>En esta sección podrás registrar tus observaciones, descripciones y reflexiones sobre el entorno que influye en la organización. Aquí puedes tomar apuntes clave sobre factores políticos, económicos, sociales, tecnológicos, ambientales y legales, así como cualquier otro aspecto relevante para el ejercicio prospectivo. Este espacio está diseñado para facilitar una comprensión integral del presente y servir de base para la identificación de variables estratégicas.</p>
         </div>
       </div>
     </div>

     <!-- Modal de IA -->
     <div 
       class="modal-overlay"
       :class="{ 'is-open': isAIOpen }"
       @click="closeAI"
     >
       <div 
         class="modal-content ai-modal"
         @click.stop
       >
         <div class="modal-header">
           <div class="header-content">
             <h3>Asistente IA</h3>
             <div class="ai-provider-selector">
               <label>IA:</label>
                                <select v-model="selectedAIProvider" @change="onAIProviderChange">
                   <option value="openrouter">🦙 Llama 3 (OpenRouter)</option>
                   <option value="ollama">🤖 Ollama (Local)</option>
                 </select>
                         <span class="provider-status" :class="selectedAIProvider">
                         {{
               selectedAIProvider === 'openrouter' ? '✅ Conectado' :
               selectedAIProvider === 'ollama' ? '✅ Conectado' : ''
             }}
          </span>
             </div>
           </div>
           <button @click="closeAI" class="close-btn">
             <i class="fas fa-times"></i>
           </button>
         </div>
         
         <div class="ai-messages" ref="aiMessagesContainer">
           <div 
             v-for="(message, index) in aiMessages" 
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
         
         <div class="ai-input">
           <textarea
             v-model="currentAIMessage"
             @keydown="handleAIKeydown"
             placeholder="Escribe tu texto para que lo analice o corrija... (Shift+Enter para nueva línea, Enter para enviar)"
             rows="4"
             :disabled="isTyping"
           ></textarea>
           <button 
             @click="sendAIMessage"
             :disabled="!currentAIMessage.trim() || isTyping"
             class="send-btn"
           >
             <i class="fas fa-paper-plane"></i>
           </button>
         </div>
                </div>
       </div>

       
   </div>
 </template>

<script>
export default {
  name: 'FloatingBubbleComponent',
  data() {
    return {
      // Posición de la burbuja
      position: { x: 0, y: 0 },
      isDragging: false,
      hasDragged: false,
      dragOffset: { x: 0, y: 0 },
      
             // Estados de las ventanas
       isMenuOpen: false,
       isNotesOpen: false,
       isAIOpen: false,
       isOrientingTextOpen: false,
      
      // Notas
      notes: [],
      selectedNoteIndex: null,
      currentNote: { title: '', content: '' },
      
             // IA
       aiMessages: [],
       currentAIMessage: '',
       isTyping: false,
                    selectedAIProvider: 'openrouter', // 'openrouter', 'ollama'
       ollamaUrl: '/ollama/generate',
       openrouterUrl: '/openrouter/generate',
        

    }
  },
     computed: {
     shouldShow() {
       const currentPath = window.location.pathname;
       const hiddenPaths = ['/login', '/register', '/'];
       return !hiddenPaths.includes(currentPath);
     },
     

   },
  mounted() {
    console.log('FloatingBubbleComponent mounted successfully!');
    this.loadNotes();
    this.initializeAIMessage();
    this.initializePosition();
    
    // Event listeners para el drag
    document.addEventListener('mousemove', this.onDrag);
    document.addEventListener('mouseup', this.stopDrag);
    document.addEventListener('touchmove', this.onDrag);
    document.addEventListener('touchend', this.stopDrag);
    
    // Event listener para cerrar modales con Escape
    document.addEventListener('keydown', this.handleKeydown);
    
    // Event listener para redimensionamiento de ventana
    window.addEventListener('resize', this.handleResize);
  },
  beforeUnmount() {
    document.removeEventListener('mousemove', this.onDrag);
    document.removeEventListener('mouseup', this.stopDrag);
    document.removeEventListener('touchmove', this.onDrag);
    document.removeEventListener('touchend', this.stopDrag);
    document.removeEventListener('keydown', this.handleKeydown);
    window.removeEventListener('resize', this.handleResize);
  },
  methods: {
    // Posición inicial y manejo de redimensionamiento
    initializePosition() {
      // Posicionar en el lado derecho, 20px desde el borde derecho y 100px desde abajo
      this.position = {
        x: window.innerWidth - 80,
        y: window.innerHeight - 100
      };
    },
    
    handleResize() {
      // Asegurar que la burbuja permanezca visible cuando se redimensiona la ventana
      const maxX = window.innerWidth - 80;
      const maxY = window.innerHeight - 100;
      
      this.position = {
        x: Math.min(this.position.x, maxX),
        y: Math.min(this.position.y, maxY)
      };
    },
    
    // Drag functionality
    startDrag(event) {
      // Solo permitir drag en el botón principal, no en las ventanas ni inputs
      if (event.target.closest('.modal-overlay') || 
          event.target.closest('.bubble-menu') || 
          event.target.closest('input') || 
          event.target.closest('textarea') ||
          event.target.closest('button')) {
        return;
      }
      
      event.preventDefault();
      this.isDragging = true;
      this.hasDragged = false;
      const rect = event.target.getBoundingClientRect();
      this.dragOffset = {
        x: event.clientX - rect.left,
        y: event.clientY - rect.top
      };
    },
    
    onDrag(event) {
      if (!this.isDragging) return;
      
      const clientX = event.clientX || event.touches[0].clientX;
      const clientY = event.clientY || event.touches[0].clientY;
      
      this.position = {
        x: Math.max(0, Math.min(window.innerWidth - 80, clientX - this.dragOffset.x)),
        y: Math.max(0, Math.min(window.innerHeight - 80, clientY - this.dragOffset.y))
      };
      
      // Marcar que se ha arrastrado
      this.hasDragged = true;
    },
    
    stopDrag() {
      this.isDragging = false;
      // Resetear hasDragged después de un pequeño delay para permitir clicks normales
      setTimeout(() => {
        this.hasDragged = false;
      }, 100);
    },
    
    // Menu functionality
         toggleMenu() {
       if (this.isDragging || this.hasDragged) return;
       
       // Cerrar todas las ventanas primero
       this.isNotesOpen = false;
       this.isAIOpen = false;
       
       // Luego alternar el menú
       this.isMenuOpen = !this.isMenuOpen;
     },
    
    // Notes functionality
    openNotes() {
      this.isMenuOpen = false;
      this.isNotesOpen = true;
      this.isAIOpen = false;
    },
    
    closeNotes() {
      this.isNotesOpen = false;
    },
    
    async loadNotes() {
      try {
        console.log('📥 Cargando notas desde el servidor...');
        
        const response = await axios.get('/notes', {
          headers: {
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache'
          }
        });
        
        if (response.data.success) {
          this.notes = response.data.data || [];
          console.log('✅ Notas cargadas:', this.notes.length);
        } else {
          console.error('❌ Error cargando notas:', response.data);
          this.notes = [];
        }
      } catch (error) {
        console.error('❌ Error cargando notas:', error);
        this.notes = [];
      }
    },
    
    saveNotes() {
      // Este método ya no se usa, las notas se guardan directamente en la BD
      console.log('📝 Método saveNotes obsoleto - las notas se guardan en la BD');
    },
    
    selectNote(index) {
      this.selectedNoteIndex = index;
      const note = this.notes[index];
      this.currentNote = { 
        title: note.title || '', 
        content: note.content || '' 
      };
    },
    
    async saveNote() {
      if (!this.currentNote.title.trim() && !this.currentNote.content.trim()) {
        console.log('📝 Contenido vacío, saltando guardado');
        return;
      }
      
      try {
        console.log('💾 Guardando nota en el servidor...');
        
        const noteData = {
          title: this.currentNote.title,
          content: this.currentNote.content
        };
        
        let response;
        if (this.selectedNoteIndex !== null && this.notes[this.selectedNoteIndex].id) {
          // Actualizar nota existente
          const noteId = this.notes[this.selectedNoteIndex].id;
          console.log('🔄 Actualizando nota ID:', noteId);
          response = await axios.put(`/notes/${noteId}`, noteData);
        } else {
          // Crear nueva nota
          console.log('➕ Creando nueva nota...');
          response = await axios.post('/notes', noteData);
        }
        
        if (response.data.success) {
          console.log('✅ Nota guardada exitosamente');
          // Recargar lista de notas
          await this.loadNotes();
          this.newNote();
        } else {
          console.error('❌ Error guardando nota:', response.data);
        }
      } catch (error) {
        console.error('❌ Error guardando nota:', error);
      }
    },
    
    newNote() {
      this.selectedNoteIndex = null;
      this.currentNote = { title: '', content: '' };
    },
    
    async deleteNote(index) {
      if (index === undefined) return;
      
      const note = this.notes[index];
      if (!note || !note.id) {
        console.log('❌ No se puede eliminar: nota sin ID');
        return;
      }
      
      try {
        console.log('🗑️ Eliminando nota ID:', note.id);
        
        const response = await axios.delete(`/notes/${note.id}`);
        
        if (response.data.success) {
          console.log('✅ Nota eliminada exitosamente');
          // Recargar lista de notas
          await this.loadNotes();
          
          // Ajustar índice seleccionado
          if (this.selectedNoteIndex === index) {
            this.newNote();
          } else if (this.selectedNoteIndex > index) {
            this.selectedNoteIndex--;
          }
        } else {
          console.error('❌ Error eliminando nota:', response.data);
        }
      } catch (error) {
        console.error('❌ Error eliminando nota:', error);
      }
    },
    
    formatDate(timestamp) {
      if (!timestamp) return 'Sin fecha';
      
      const date = new Date(timestamp);
      return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    // AI functionality
    openAI() {
      this.isMenuOpen = false;
      this.isNotesOpen = false;
      this.isAIOpen = true;
    },
    
    closeAI() {
      this.isAIOpen = false;
    },
    
    // Texto orientador functionality
    showOrientingText() {
      this.isOrientingTextOpen = true;
    },
    
    closeOrientingText() {
      this.isOrientingTextOpen = false;
    },
    
    initializeAIMessage() {
      let providerText, welcomeMessage;
      if (this.selectedAIProvider === 'openrouter') {
        providerText = '🦙 Asistente IA Llama 3 (OpenRouter)';
        welcomeMessage = `¡Hola! Soy tu ${providerText}. ¿En qué puedo ayudarte hoy? 😊`;
      } else if (this.selectedAIProvider === 'ollama') {
        providerText = '🤖 Asistente IA Local (Ollama)';
        welcomeMessage = `¡Hola! Soy tu ${providerText}. ¿En qué puedo ayudarte hoy? 😊`;
             } else {
         providerText = '🤖 Asistente IA Local (Ollama)';
         welcomeMessage = `¡Hola! Soy tu ${providerText}. ¿En qué puedo ayudarte hoy? 😊`;
       }
      this.addAIMessage({
        type: 'bot',
        text: welcomeMessage,
        timestamp: new Date()
      });
    },
    
    async sendAIMessage() {
      if (!this.currentAIMessage.trim() || this.isTyping) return;
      
      const userMessage = this.currentAIMessage.trim();
      
      this.addAIMessage({
        type: 'user',
        text: userMessage,
        timestamp: new Date()
      });
      
      this.currentAIMessage = '';
      this.isTyping = true;
      
      // Agregar mensaje de "pensando" mientras procesa
      this.addAIMessage({
        type: 'bot',
        text: '🤔 Pensando...',
        timestamp: new Date(),
        isThinking: true
      });
      
             try {
         let response;
         
         // Usar solo Ollama
           const prompt = this.createPrompt(userMessage);
           response = await this.callAI(prompt);
         
         // Reemplazar el mensaje de "pensando" con la respuesta real
         const thinkingMessageIndex = this.aiMessages.findIndex(msg => msg.isThinking);
         if (thinkingMessageIndex !== -1) {
           this.aiMessages[thinkingMessageIndex] = {
             type: 'bot',
             text: response,
             timestamp: new Date()
           };
         } else {
           this.addAIMessage({
             type: 'bot',
             text: response,
             timestamp: new Date()
           });
         }
         
                } catch (error) {
           console.error('Error al comunicarse con la IA:', error);
           
           let errorMessage = 'Lo siento, no pude procesar tu solicitud. Inténtalo de nuevo más tarde.';
           
           // Intentar obtener mensaje de error más específico
           if (error.message && error.message.includes('500')) {
             errorMessage = 'Error del servidor. Verifica que el sistema esté funcionando correctamente.';
           } else if (error.message && error.message.includes('503')) {
             errorMessage = 'Servicio no disponible. Inténtalo de nuevo más tarde.';
           }
           
           // Remover el mensaje de "pensando" si existe
           const thinkingMessageIndex = this.aiMessages.findIndex(msg => msg.isThinking);
           if (thinkingMessageIndex !== -1) {
             this.aiMessages.splice(thinkingMessageIndex, 1);
           }
           
           this.addAIMessage({
             type: 'bot',
             text: errorMessage,
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
      // Construir el historial de la conversación
      let conversationHistory = '';
      
      
      const recentMessages = this.aiMessages.slice(-6);
      
      if (recentMessages.length > 0) {
        conversationHistory = recentMessages.map(msg => {
          if (msg.type === 'user') {
            return `Usuario: ${msg.text}`;
          } else if (msg.type === 'bot' && !msg.isThinking) {
            return `Asistente: ${msg.text}`;
          }
          return '';
        }).filter(text => text !== '').join('\n') + '\n';
      }
      
      return `Eres un asistente IA amigable. Responde de manera natural y concisa. Mantén el contexto de la conversación. Para análisis de textos largos, sé específico y directo.

${conversationHistory}Usuario: ${userText}`;
    },
    
         async callAI(prompt) {
       let url, requestBody;
       if (this.selectedAIProvider === 'openrouter') {
         url = this.openrouterUrl;
         requestBody = {
           prompt: prompt,
           model: 'meta-llama/llama-3-8b-instruct',
           temperature: 0.7
         };
       } else if (this.selectedAIProvider === 'ollama') {
         url = this.ollamaUrl;
         requestBody = {
           model: 'gemma3:4b',
           prompt: prompt,
           stream: false,
           options: {
             temperature: 0.3,
             top_p: 0.7,
             max_tokens: 4000,
             num_predict: 150,
             top_k: 15,
             repeat_penalty: 1.05
           }
         };
       }
       
       const response = await fetch(url, {
         method: 'POST',
         headers: {
           'Content-Type': 'application/json',
           'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
         },
         body: JSON.stringify(requestBody)
       });
       if (!response.ok) {
         throw new Error(`HTTP error! status: ${response.status}`);
       }
       const data = await response.json();
       return data.response || 'No se recibió respuesta del modelo.';
     },
     
     onAIProviderChange() {
       // Limpiar mensajes cuando se cambia de proveedor
       this.aiMessages = [];
       this.initializeAIMessage();
     },
     

    
    addAIMessage(message) {
      this.aiMessages.push(message);
    },
    
    formatMessage(text) {
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
      const container = this.$refs.aiMessagesContainer;
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    },
    
         handleKeydown(event) {
       if (event.key === 'Escape') {
         if (this.isNotesOpen) {
           this.closeNotes();
         } else if (this.isAIOpen) {
           this.closeAI();
         }
       }
     },
     
     handleAIKeydown(event) {
       if (event.key === 'Enter') {
         if (event.shiftKey) {
           // Shift+Enter: permitir nueva línea
           return;
         } else {
           // Enter sin Shift: enviar mensaje
           event.preventDefault();
           this.sendAIMessage();
         }
       }
     },
     

      
      showNotification(message) {
        // Crear notificación temporal
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
          notification.remove();
        }, 3000);
      }
  }
}
</script>

<style scoped>
.floating-bubble {
  position: fixed;
  z-index: 9999;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  user-select: none;
  pointer-events: auto;
}

.bubble-toggle {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: grab;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
  color: white;
  font-size: 24px;
  position: relative;
}

.bubble-toggle:active {
  cursor: grabbing;
  transform: scale(0.95);
}

.bubble-toggle:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
}

.bubble-toggle.is-active {
  background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
}

.bubble-menu {
  position: absolute;
  bottom: 80px;
  right: 0;
  background: white;
  border-radius: 15px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  padding: 10px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(20px) scale(0.9);
  transition: all 0.3s ease;
  min-width: 200px;
}

.bubble-menu.is-open {
  opacity: 1;
  visibility: visible;
  transform: translateY(0) scale(1);
}

.menu-option {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  cursor: pointer;
  border-radius: 10px;
  transition: background-color 0.2s ease;
  margin-bottom: 5px;
}

.menu-option:hover {
  background: #f8f9fa;
}

.menu-option:last-child {
  margin-bottom: 0;
}

.option-icon {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 12px;
  font-size: 16px;
}

.menu-option:first-child .option-icon {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
}

 .menu-option:nth-child(2) .option-icon {
   background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
   color: white;
 }
 
 .menu-option:nth-child(3) .option-icon {
   background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
   color: white;
 }

.option-text {
  font-weight: 500;
  color: #333;
}

/* Modales */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  backdrop-filter: blur(5px);
}

.modal-overlay.is-open {
  opacity: 1;
  visibility: visible;
}

.modal-content {
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  max-width: 90vw;
  max-height: 90vh;
  transform: scale(0.9) translateY(20px);
  transition: all 0.3s ease;
  overflow: hidden;
  pointer-events: auto !important;
  user-select: text;
}

.modal-overlay.is-open .modal-content {
  transform: scale(1) translateY(0);
}

.modal-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px 25px;
  border-radius: 20px 20px 0 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.info-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 16px;
}

.info-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.modal-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.notes-modal {
  width: 1000px;
  height: 700px;
}

.ai-modal {
  width: 800px;
}

.orienting-modal {
  width: 600px;
  max-width: 90vw;
}

.orienting-content {
  padding: 30px;
  text-align: center;
}

.orienting-icon {
  font-size: 4rem;
  color: #667eea;
  margin-bottom: 20px;
}

.orienting-content p {
  font-size: 1.1rem;
  line-height: 1.7;
  color: #495057;
  margin: 0;
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

/* Estilos específicos para Notas */
.notes-content {
  flex: 1;
  display: flex;
  padding: 15px;
  background: #f8f9fa;
  gap: 15px;
  pointer-events: auto !important;
  user-select: text;
}

.notes-sidebar {
  width: 250px;
  background: white;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  height: 100%;
  max-height: 500px;
}

.notes-header {
  padding: 15px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8f9fa;
}

.notes-header h4 {
  margin: 0;
  font-size: 16px;
  color: #333;
}

.new-note-btn {
  background: #28a745;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  cursor: pointer;
  font-size: 12px;
  transition: background-color 0.2s ease;
}

.new-note-btn:hover {
  background: #218838;
}

.notes-list {
  flex: 1;
  overflow-y: auto;
  padding: 10px;
  max-height: 400px;
  min-height: 200px;
}

.note-item {
  background: white;
  border-radius: 8px;
  padding: 10px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid transparent;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
}

.note-item:hover {
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.note-item.selected {
  border-color: #667eea;
  background: #f0f4ff;
}

.note-preview {
  flex: 1;
}

.note-preview h5 {
  margin: 0 0 5px 0;
  font-size: 13px;
  color: #333;
  font-weight: 600;
}

.note-preview p {
  margin: 0 0 3px 0;
  font-size: 11px;
  color: #666;
  line-height: 1.3;
}

.note-preview small {
  color: #999;
  font-size: 9px;
}

.delete-note-btn {
  background: none;
  border: none;
  color: #dc3545;
  cursor: pointer;
  padding: 2px;
  border-radius: 3px;
  font-size: 10px;
  opacity: 0.7;
  transition: opacity 0.2s ease;
  flex-shrink: 0;
}

.delete-note-btn:hover {
  opacity: 1;
  background: rgba(220, 53, 69, 0.1);
}

.note-editor {
  flex: 1;
  background: white;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.editor-header {
  padding: 15px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  gap: 10px;
  align-items: center;
  background: #f8f9fa;
}

.note-title-input {
  flex: 1;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  padding: 10px;
  font-size: 14px;
  font-family: inherit;
  pointer-events: auto !important;
  user-select: text !important;
  cursor: text !important;
  position: relative;
  z-index: 1;
}

.note-title-input:focus {
  outline: none;
  border-color: #667eea;
}

.note-content-input {
  flex: 1;
  border: none;
  border-radius: 0;
  padding: 15px;
  font-size: 14px;
  font-family: inherit;
  resize: none;
  pointer-events: auto !important;
  user-select: text !important;
  cursor: text !important;
  outline: none;
  position: relative;
  z-index: 1;
}

.note-content-input:focus {
  outline: none;
  border-color: #667eea;
}

.save-btn {
  background: #28a745;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 8px 16px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.save-btn:hover:not(:disabled) {
  background: #218838;
}

.save-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
}



/* Estilos específicos para IA */
.ai-messages {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  background: #f8f9fa;
  pointer-events: auto !important;
  user-select: text;
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

.ai-input {
  padding: 15px;
  background: white;
  border-radius: 0 0 15px 15px;
  display: flex;
  gap: 10px;
  align-items: flex-end;
  pointer-events: auto !important;
  user-select: text;
}

.ai-input textarea {
  flex: 1;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  padding: 10px;
  resize: none;
  font-family: inherit;
  font-size: 14px;
  line-height: 1.4;
  transition: border-color 0.2s ease;
  pointer-events: auto !important;
  user-select: text !important;
  cursor: text !important;
  position: relative;
  z-index: 1;
}

.ai-input textarea:focus {
  outline: none;
  border-color: #667eea;
}

.ai-input textarea:disabled {
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


 
 .loading-container {
   display: flex;
   flex-direction: column;
   align-items: center;
   justify-content: center;
   height: 100%;
   background: white;
 }
 
 .loading-spinner {
   font-size: 2rem;
   color: #667eea;
   margin-bottom: 15px;
 }
 
 .loading-container p {
   color: #6c757d;
   font-size: 16px;
 }
 
 .welcome-screen {
   display: flex;
   align-items: center;
   justify-content: center;
   height: 100%;
   background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
   color: white;
 }
 
 .welcome-content {
   text-align: center;
   padding: 40px;
 }
 
 .welcome-content i {
   font-size: 4rem;
   margin-bottom: 20px;
   opacity: 0.8;
 }
 
 .welcome-content h4 {
   font-size: 2rem;
   margin-bottom: 15px;
   font-weight: 600;
 }
 
 .welcome-content p {
   font-size: 1.1rem;
   margin-bottom: 30px;
   opacity: 0.9;
 }
 
 .quick-links {
   display: flex;
   gap: 15px;
   justify-content: center;
   flex-wrap: wrap;
 }
 
 .quick-link {
   background: rgba(255, 255, 255, 0.2);
   border: 2px solid rgba(255, 255, 255, 0.3);
   border-radius: 10px;
   padding: 12px 20px;
   color: white;
   cursor: pointer;
   transition: all 0.3s ease;
   font-size: 14px;
   font-weight: 500;
   backdrop-filter: blur(10px);
 }
 
 .quick-link:hover {
   background: rgba(255, 255, 255, 0.3);
   border-color: rgba(255, 255, 255, 0.5);
   transform: translateY(-2px);
 }
 
   .quick-link i {
    margin-right: 8px;
    font-size: 16px;
  }
  
  .iframe-blocked {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: #f8f9fa;
  }
  
  .blocked-content {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    max-width: 500px;
  }
  
  .blocked-content i {
    font-size: 4rem;
    color: #dc3545;
    margin-bottom: 20px;
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

/* Scrollbar personalizado */
.notes-list::-webkit-scrollbar,
.ai-messages::-webkit-scrollbar {
  width: 8px;
}

.notes-list::-webkit-scrollbar-track,
.ai-messages::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.notes-list::-webkit-scrollbar-thumb,
.ai-messages::-webkit-scrollbar-thumb {
  background: #667eea;
  border-radius: 4px;
}

.notes-list::-webkit-scrollbar-thumb:hover,
.ai-messages::-webkit-scrollbar-thumb:hover {
  background: #5a6fd8;
}

/* Texto orientador */
.orienting-text {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border: 1px solid #dee2e6;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.orienting-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
  color: #495057;
  font-weight: 600;
  font-size: 1.1rem;
}

.orienting-header i {
  color: #667eea;
  font-size: 1.2rem;
}

.orienting-text p {
  color: #6c757d;
  line-height: 1.6;
  margin: 0;
  font-size: 0.95rem;
}

/* Tooltips mejorados */
.menu-option {
  position: relative;
}

.menu-option:hover::after {
  content: attr(title);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
  white-space: nowrap;
  z-index: 10000;
  margin-bottom: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.menu-option:hover::before {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 5px solid transparent;
  border-top-color: rgba(0, 0, 0, 0.9);
  margin-bottom: 3px;
  z-index: 10000;
}

/* Tooltip para la burbuja principal */
.bubble-toggle {
  position: relative;
}

.bubble-toggle:hover::after {
  content: attr(title);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
  white-space: nowrap;
  z-index: 10000;
  margin-bottom: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.bubble-toggle:hover::before {
  content: '';
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 5px solid transparent;
  border-top-color: rgba(0, 0, 0, 0.9);
  margin-bottom: 3px;
  z-index: 10000;
}

/* Estilos para el selector de IA */
.header-content {
  display: flex;
  align-items: center;
  gap: 20px;
  flex: 1;
}

.ai-provider-selector {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 14px;
  background: rgba(255, 255, 255, 0.1);
  padding: 8px 12px;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  position: relative;
  overflow: hidden;
}

.ai-provider-selector label {
  color: white;
  font-weight: 600;
  font-size: 13px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.ai-provider-selector select {
  padding: 6px 12px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.15);
  color: white;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(5px);
  min-width: 140px;
}

.ai-provider-selector select:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.ai-provider-selector select:focus {
  outline: none;
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.7);
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.2);
}

.ai-provider-selector select option {
  background: #2c3e50;
  color: white;
  padding: 8px;
}

/* Efectos adicionales para el selector de IA */
.ai-provider-selector::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
  border-radius: 12px;
  z-index: -1;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.ai-provider-selector:hover::before {
  opacity: 1;
}

/* Efecto sutil para el estado del proveedor */
.provider-status:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.provider-status {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 6px;
  margin-left: 8px;
  font-weight: 600;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.provider-status.ollama {
  background: linear-gradient(135deg, rgba(76, 175, 80, 0.9), rgba(76, 175, 80, 0.7));
  color: white;
  border-color: rgba(76, 175, 80, 0.5);
}

.provider-status.openrouter {
  background: linear-gradient(135deg, rgba(33, 150, 243, 0.9), rgba(33, 150, 243, 0.7));
  color: white;
  border-color: rgba(33, 150, 243, 0.5);
}

/* Responsive */
 @media (max-width: 1024px) {
   .notes-modal {
     width: 90vw;
     height: 80vh;
   }
   
   .ai-modal {
     width: 90vw;
     height: 70vh;
   }
   

 }

@media (max-width: 768px) {
  .modal-content {
    margin: 20px;
    max-width: calc(100vw - 40px);
    max-height: calc(100vh - 40px);
  }
  
  .notes-modal {
    width: 100%;
    height: 100%;
  }
  
     .ai-modal {
     width: 100%;
     height: 100%;
   }
   

  
  .notes-content {
    flex-direction: column;
  }
  
  .notes-sidebar {
    width: 100%;
    height: 200px;
  }
  
  .message-text {
    max-width: 280px;
  }
  
  .bubble-menu {
    min-width: 180px;
  }
}

@media (max-width: 480px) {
  .modal-header {
    padding: 15px 20px;
  }
  
  .modal-header h3 {
    font-size: 16px;
  }
  
  .message-text {
    max-width: 230px;
  }
}
</style> 