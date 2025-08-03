<template>
  <div 
    class="floating-note-container"
    :style="{ left: position.x + 'px', top: position.y + 'px' }"
    ref="noteContainer"
  >
    <!-- Botón flotante para abrir/cerrar -->
    <div 
      class="floating-note-button"
      @click="toggleNote"
      @mousedown="startDrag"
      :class="{ 'is-active': isOpen, 'is-dragging': isDragging }"
      title="Notas flotantes - Haz clic para abrir/cerrar, arrastra para mover"
    >
      <i class="fas fa-sticky-note"></i>
      <span v-if="hasContent" class="note-indicator"></span>
      <div class="drag-handle">
        <i class="fas fa-grip-vertical"></i>
      </div>
    </div>

    <!-- Modal de la nota -->
    <div 
      v-if="isOpen" 
      class="floating-note-modal"
      @click.self="closeNote"
    >
      <div class="note-content">
        <div class="note-header">
          <h3 class="note-title">
            <i class="fas fa-sticky-note"></i>
            Notas
          </h3>
          <div class="note-actions">
            <button 
              class="button is-small is-success"
              @click="createNewNote"
              title="Nueva nota"
            >
              <i class="fas fa-plus"></i>
            </button>
            <button 
              class="button is-small is-info"
              @click="showNoteList = !showNoteList"
              title="Ver lista de notas"
            >
              <i class="fas fa-list"></i>
            </button>
            <button 
              class="button is-small is-info"
              @click="saveNote"
              :disabled="isSaving"
              title="Guardar nota"
            >
              <i class="fas fa-save"></i>
              {{ isSaving ? 'Guardando...' : 'Guardar' }}
            </button>
            <button 
              class="button is-small is-danger"
              @click="closeNote"
              title="Cerrar"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>

        <div class="note-body">
          <!-- Lista de notas -->
          <div v-if="showNoteList" class="notes-list-section">
            <div class="notes-header">
              <h4 class="title is-5">Mis Notas</h4>
              <button 
                class="button is-small is-info"
                @click="refreshNotes"
                title="Recargar notas"
              >
                <i class="fas fa-sync-alt"></i>
              </button>
            </div>
            <div v-if="isLoadingNotes" class="has-text-centered">
              <i class="fas fa-spinner fa-spin"></i> Cargando notas...
            </div>
            <div v-else-if="notes.length === 0" class="has-text-centered has-text-grey">
              <p>No hay notas guardadas</p>
            </div>
            <div v-else class="notes-list">
              <div 
                v-for="note in notes" 
                :key="note.id"
                class="note-item"
                :class="{ 'is-selected': selectedNote && selectedNote.id === note.id }"
                @click="selectNote(note)"
              >
                <div class="note-item-header">
                  <h5 class="note-item-title">
                    {{ note.title || 'Sin título' }}
                  </h5>
                  <button 
                    class="button is-small is-danger"
                    @click.stop="deleteNote(note.id)"
                    title="Eliminar nota"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
                <p class="note-item-preview">
                  {{ note.content.substring(0, 100) }}{{ note.content.length > 100 ? '...' : '' }}
                </p>
                <small class="note-item-date">
                  {{ formatDate(note.updated_at) }}
                </small>
              </div>
            </div>
          </div>

          <!-- Editor de notas -->
          <div v-else>
            <div class="field">
              <label class="label">Título (opcional)</label>
              <div class="control">
                <input 
                  v-model="noteTitle"
                  class="input"
                  type="text"
                  placeholder="Título de la nota..."
                  @input="autoSave"
                />
              </div>
            </div>

            <div class="field">
              <label class="label">Contenido</label>
              <div class="control">
                <textarea 
                  v-model="noteContent"
                  class="textarea"
                  placeholder="Escribe tus notas aquí... (máximo 10,000 caracteres)"
                  rows="15"
                  @input="autoSave"
                  :maxlength="10000"
                ></textarea>
              </div>
              <p class="help">
                {{ noteContent.length }}/10,000 caracteres
              </p>
            </div>

            <div v-if="lastSaved" class="note-footer">
              <small class="has-text-grey">
                Último guardado: {{ formatDate(lastSaved) }}
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useTraceabilityStore } from '../../../stores/traceability';

// Configurar axios para incluir el token CSRF
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (csrfToken) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
  console.log('🔑 CSRF Token configurado:', csrfToken.substring(0, 20) + '...');
} else {
  console.error('❌ No se pudo obtener el token CSRF');
}

export default {
  name: 'FloatingNoteComponent',
  props: {
    traceabilityId: {
      type: [Number, String],
      default: null
    }
  },
  setup(props) {
    const isOpen = ref(false);
    const noteContent = ref('');
    const noteTitle = ref('');
    const isSaving = ref(false);
    const lastSaved = ref(null);
    const currentNoteId = ref(null);
    const autoSaveTimeout = ref(null);
    
    // Variables para lista de notas
    const showNoteList = ref(false);
    const notes = ref([]);
    const isLoadingNotes = ref(false);
    const selectedNote = ref(null);
    
    // Variables para el arrastre
    const isDragging = ref(false);
    const wasDragging = ref(false); // Nueva variable para controlar si se estaba arrastrando
    const dragStart = ref({ x: 0, y: 0 });
    const position = ref({ x: 30, y: 30 }); // Posición inicial
    const noteContainer = ref(null);

    // Computed para verificar si hay contenido
    const hasContent = computed(() => {
      return noteContent.value.trim().length > 0 || noteTitle.value.trim().length > 0;
    });

    // Computed para obtener el traceabilityId actual
    const currentTraceabilityId = computed(() => {
      // Si se proporciona como prop, usarlo
      if (props.traceabilityId) {
        return props.traceabilityId;
      }
      
      // Intentar obtener desde el store de traceability
      try {
        const traceabilityStore = useTraceabilityStore();
        return traceabilityStore.getCurrentRoute?.id || null;
      } catch (error) {
        console.log('Traceability store no disponible');
        return null;
      }
    });

    // Cargar nota existente - DESHABILITADO
    const loadNote = async () => {
      console.log('📝 Funcionalidad de carga de nota deshabilitada');
      return;
    };

    // Cargar lista de notas - DESHABILITADO
    const loadNotesList = async () => {
      console.log('📝 Funcionalidad de carga de notas deshabilitada');
      notes.value = [];
      return;
    };

    // Seleccionar nota de la lista
    const selectNote = (note) => {
      selectedNote.value = note;
      noteContent.value = note.content || '';
      noteTitle.value = note.title || '';
      currentNoteId.value = note.id;
      lastSaved.value = note.updated_at;
      showNoteList.value = false;
    };

    // Crear nueva nota
    const createNewNote = () => {
      selectedNote.value = null;
      noteContent.value = '';
      noteTitle.value = '';
      currentNoteId.value = null;
      lastSaved.value = null;
      showNoteList.value = false;
    };

    // Eliminar nota - DESHABILITADO
    const deleteNote = async (noteId) => {
      console.log('📝 Funcionalidad de eliminación de notas deshabilitada');
      return;
    };

    // Funciones de UI
    const toggleNote = () => {
      // No abrir/cerrar si se estaba arrastrando
      if (wasDragging.value) {
        wasDragging.value = false;
        return;
      }
      
      isOpen.value = !isOpen.value;
      if (isOpen.value) {
        loadNote();
        loadNotesList();
      }
    };

    // Limpiar caché y recargar notas - DESHABILITADO
    const refreshNotes = async () => {
      console.log('📝 Funcionalidad de refresh de notas deshabilitada');
      return;
    };

    const closeNote = () => {
      isOpen.value = false;
      if (autoSaveTimeout.value) {
        clearTimeout(autoSaveTimeout.value);
      }
    };

    // Funciones de arrastre
    const startDrag = (event) => {
      // Solo permitir arrastre si no está abierto el modal
      if (isOpen.value) return;
      
      event.preventDefault();
      isDragging.value = true;
      wasDragging.value = false; // Reset al inicio del arrastre
      
      const rect = noteContainer.value.getBoundingClientRect();
      dragStart.value = {
        x: event.clientX - rect.left,
        y: event.clientY - rect.top
      };
      
      document.addEventListener('mousemove', onDrag);
      document.addEventListener('mouseup', stopDrag);
    };

    const onDrag = (event) => {
      if (!isDragging.value) return;
      
      // Marcar que se está arrastrando si se mueve el mouse
      wasDragging.value = true;
      
      const newX = event.clientX - dragStart.value.x;
      const newY = event.clientY - dragStart.value.y;
      
      // Limitar la posición dentro de la ventana
      const maxX = window.innerWidth - 60; // Ancho del botón
      const maxY = window.innerHeight - 60; // Alto del botón
      
      position.value = {
        x: Math.max(0, Math.min(newX, maxX)),
        y: Math.max(0, Math.min(newY, maxY))
      };
    };

    const stopDrag = () => {
      isDragging.value = false;
      document.removeEventListener('mousemove', onDrag);
      document.removeEventListener('mouseup', stopDrag);
      
      // Guardar posición en localStorage
      savePosition();
    };

    const savePosition = () => {
      try {
        const user = JSON.parse(localStorage.getItem('user')) || {};
        const userId = user.id || 'anonymous';
        const positionKey = `note_position_${userId}`;
        
        localStorage.setItem(positionKey, JSON.stringify(position.value));
      } catch (error) {
        console.error('Error guardando posición:', error);
      }
    };

    const loadPosition = () => {
      try {
        const user = JSON.parse(localStorage.getItem('user')) || {};
        const userId = user.id || 'anonymous';
        const positionKey = `note_position_${userId}`;
        
        const savedPosition = localStorage.getItem(positionKey);
        if (savedPosition) {
          position.value = JSON.parse(savedPosition);
        }
        
        // Ajustar posición si está fuera de la ventana
        adjustPositionToWindow();
      } catch (error) {
        console.error('Error cargando posición:', error);
      }
    };

    const adjustPositionToWindow = () => {
      const maxX = window.innerWidth - 60;
      const maxY = window.innerHeight - 60;
      
      if (position.value.x > maxX) {
        position.value.x = maxX;
      }
      if (position.value.y > maxY) {
        position.value.y = maxY;
      }
      if (position.value.x < 0) {
        position.value.x = 0;
      }
      if (position.value.y < 0) {
        position.value.y = 0;
      }
    };

    const handleWindowResize = () => {
      adjustPositionToWindow();
      savePosition();
    };

    // Guardar nota - DESHABILITADO
    const saveNote = async () => {
      console.log('📝 Funcionalidad de guardado de notas deshabilitada');
      return;
    };

    const autoSave = () => {
      // AutoSave deshabilitado
      console.log('📝 AutoSave deshabilitado');
      return;
    };

    // Formatear fecha
    const formatDate = (dateString) => {
      try {
        const date = new Date(dateString);
        return date.toLocaleString('es-ES', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch (error) {
        return 'Fecha no válida';
      }
    };

    // Cargar posición al montar el componente
    onMounted(async () => {
      loadPosition();
      window.addEventListener('resize', handleWindowResize);
      
      // NOTAS DESHABILITADAS - No cargar ni guardar notas
      console.log('📝 Funcionalidad de notas deshabilitada');
    });

    // Limpiar timeout al desmontar
    onUnmounted(() => {
      if (autoSaveTimeout.value) {
        clearTimeout(autoSaveTimeout.value);
      }
      window.removeEventListener('resize', handleWindowResize);
    });

    return {
      isOpen,
      noteContent,
      noteTitle,
      isSaving,
      lastSaved,
      hasContent,
      isDragging,
      position,
      noteContainer,
      showNoteList,
      notes,
      isLoadingNotes,
      selectedNote,
      currentTraceabilityId,
      toggleNote,
      closeNote,
      saveNote,
      autoSave,
      formatDate,
      startDrag,
      handleWindowResize,
      loadNote,
      loadNotesList,
      selectNote,
      refreshNotes,
      createNewNote,
      deleteNote
    };
  }
};
</script>

<style scoped>
.floating-note-container {
  position: fixed;
  z-index: 1000;
  user-select: none;
  cursor: grab;
}

.floating-note-container:active {
  cursor: grabbing;
}

.floating-note-button {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  position: relative;
  color: white;
  font-size: 1.5rem;
}

.floating-note-button:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.floating-note-button.is-active {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.floating-note-button.is-dragging {
  transform: scale(1.05);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
}

.note-indicator {
  position: absolute;
  top: -5px;
  right: -5px;
  width: 12px;
  height: 12px;
  background-color: #ff4757;
  border-radius: 50%;
  border: 2px solid white;
}

.drag-handle {
  position: absolute;
  bottom: -8px;
  right: -8px;
  width: 20px;
  height: 20px;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  color: #666;
  border: 1px solid #ddd;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.floating-note-button:hover .drag-handle {
  opacity: 1;
}

.floating-note-button.is-dragging .drag-handle {
  opacity: 1;
  background: rgba(255, 255, 255, 1);
}

.floating-note-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1001;
}

.note-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  width: 90%;
  max-width: 600px;
  max-height: 80vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.note-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.note-title {
  margin: 0;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

.note-actions {
  display: flex;
  gap: 10px;
}

.note-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

.note-footer {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #eee;
  text-align: center;
}

/* Estilos para la lista de notas */
.notes-list-section {
  max-height: 400px;
  overflow-y: auto;
}

.notes-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.notes-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.note-item {
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: #f9f9f9;
}

.note-item:hover {
  background: #f0f0f0;
  border-color: #3273dc;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.note-item.is-selected {
  background: #e8f4fd;
  border-color: #3273dc;
  box-shadow: 0 2px 8px rgba(50, 115, 220, 0.2);
}

.note-item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.note-item-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: #363636;
  flex: 1;
}

.note-item-preview {
  margin: 0 0 8px 0;
  color: #666;
  font-size: 0.9rem;
  line-height: 1.4;
}

.note-item-date {
  color: #999;
  font-size: 0.8rem;
}

.textarea {
  resize: vertical;
  min-height: 200px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.6;
}

.textarea:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 0.125em rgba(102, 126, 234, 0.25);
}

/* Responsive */
@media (max-width: 768px) {
  .floating-note-button {
    width: 50px;
    height: 50px;
    font-size: 1.2rem;
  }
  
  .drag-handle {
    width: 18px;
    height: 18px;
    font-size: 0.6rem;
  }
  
  .note-content {
    width: 95%;
    margin: 10px;
  }
  
  .note-header {
    padding: 15px;
  }
  
  .note-body {
    padding: 15px;
  }
}
</style> 