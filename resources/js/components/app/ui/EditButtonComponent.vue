<template>
  <div class="edit-button-container">
    <b-button
      :type="isEditing ? 'is-success' : 'is-info'"
      size="is-small"
      :icon-left="isEditing ? 'save' : 'edit'"
      @click="handleClick"
      outlined
      :disabled="isLocked"
      class="edit-button"
    >
      {{ isEditing ? saveText : editText }}
    </b-button>
    
    <!-- Mensaje de bloqueado estandarizado -->
    <span v-if="isLocked" class="locked-tag">
      {{ lockedText }}
    </span>
  </div>
</template>

<script>
export default {
  name: 'EditButtonComponent',
  props: {
    isEditing: {
      type: Boolean,
      default: false
    },
    isLocked: {
      type: Boolean,
      default: false
    },
    editText: {
      type: String,
      default: 'Editar'
    },
    saveText: {
      type: String,
      default: 'Guardar'
    },
    lockedText: {
      type: String,
      default: 'Bloqueado'
    }
  },
  emits: ['click'],
  methods: {
    handleClick() {
      if (!this.isLocked) {
        this.$emit('click');
      }
    }
  }
}
</script>

<style scoped>
.edit-button-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.edit-button {
  min-width: 80px;
  transition: all 0.3s ease;
}

.edit-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.locked-tag {
  background-color: #f56565 !important;
  color: white !important;
  padding: 0.25rem 0.5rem !important;
  border-radius: 4px !important;
  font-size: 0.75rem !important;
  font-weight: 600 !important;
  text-transform: uppercase !important;
  letter-spacing: 0.05em !important;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
}

/* Estilos para diferentes estados */
.edit-button.is-info {
  border-color: #005883 !important;
  color: #005883 !important;
}

.edit-button.is-info:hover:not(:disabled) {
  background-color: #005883 !important;
  color: white !important;
}

.edit-button.is-success {
  border-color: #38a169 !important;
  color: #38a169 !important;
}

.edit-button.is-success:hover:not(:disabled) {
  background-color: #38a169 !important;
  color: white !important;
}

/* Animación para el botón bloqueado */
.edit-button:disabled {
  animation: pulse-disabled 2s infinite;
}

@keyframes pulse-disabled {
  0%, 100% {
    opacity: 0.6;
  }
  50% {
    opacity: 0.4;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .edit-button-container {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.25rem;
  }
  
  .edit-button {
    min-width: 70px;
    font-size: 0.75rem;
  }
  
  .locked-tag {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
  }
}
</style>
