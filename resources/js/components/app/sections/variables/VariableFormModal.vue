<!-- 
    VariableFormModal.vue
    
    Este componente implementa un modal para la creación de nuevas variables.
    Proporciona:
    - Formulario simple con validación
    - Manejo de estados de carga
    - Notificaciones de éxito/error
    - Limpieza automática al cerrar
-->
<template>
    <!-- Modal usando el componente b-modal de Buefy -->
    <b-modal
        v-model="isActive"
        has-modal-card
        trap-focus
        :destroy-on-hide="false"
        aria-role="dialog"
        aria-modal>
        
        <template #default="props">
            <!-- Tarjeta del modal con el formulario -->
            <div class="modal-card" style="width: auto">
                <!-- Encabezado del modal -->
                <header class="modal-card-head">
                    <p class="modal-card-title">Nueva Variable</p>
                </header>

                <!-- Cuerpo del modal con el campo de nombre -->
                <section class="modal-card-body">
                    <b-field label="Nombre de la Variable">
                        <b-input
                            v-model="form.name_variable"
                            placeholder="Ingrese el nombre de la variable"
                            required>
                        </b-input>
                    </b-field>
                </section>

                <!-- Pie del modal con botones de acción -->
                <footer class="modal-card-foot">
                    <b-button
                        :loading="isLoading"
                        type="is-primary"
                        @click="handleSubmit">
                        Guardar
                    </b-button>
                    <b-button
                        type="is-danger"
                        @click="handleClose">
                        Cancelar
                    </b-button>
                </footer>
            </div>
        </template>
    </b-modal>
</template>

<script>
import { useVariablesStore } from '@/stores/variables';

export default {
    // Define los eventos que el componente puede emitir
    emits: ['close'],
    
    /**
     * Setup del componente usando Composition API
     * 
     * Inicializa el store de variables para:
     * - Crear nuevas variables
     * - Mantener el estado global actualizado
     */
    setup() {
        const variablesStore = useVariablesStore();
        return { variablesStore };
    },

    /**
     * Estado local del componente
     * 
     * Maneja:
     * - Visibilidad del modal
     * - Estado de carga durante el guardado
     * - Datos del formulario
     */
    data() {
        return {
            isActive: true,     // Controla la visibilidad del modal
            isLoading: false,   // Controla el estado del botón de submit
            form: {
                name_variable: ''  // Campo para el nombre de la variable
            }
        };
    },

    methods: {
        /**
         * Maneja el envío del formulario
         * 
         * Este método:
         * 1. Activa el estado de carga
         * 2. Intenta crear la variable
         * 3. Muestra notificación de éxito/error
         * 4. Cierra el modal si hay éxito
         * 5. Desactiva el estado de carga
         */
        async handleSubmit() {
            this.isLoading = true;
            try {
                const success = await this.variablesStore.createVariable(this.form);
                if (success) {
                    this.$buefy.toast.open({
                        message: 'Variable creada exitosamente',
                        type: 'is-success'
                    });
                    this.handleClose();
                } else {
                    throw new Error('No se pudo crear la variable');
                }
            } catch (error) {
                this.$buefy.toast.open({
                    message: error.message || 'Error al crear la variable',
                    type: 'is-danger'
                });
            } finally {
                this.isLoading = false;
            }
        },

        /**
         * Limpia el formulario y cierra el modal
         * 
         * Este método:
         * 1. Reinicia el formulario a valores iniciales
         * 2. Emite el evento 'close' al componente padre
         */
        handleClose() {
            // Limpiamos el formulario
            this.form = {
                name_variable: ''
            };
            // Emitimos el evento de cierre
            this.$emit('close');
        }
    }
};
</script>

<style lang="scss" scoped>
/* Estilos para el cuerpo del modal */
.modal-card-body {
    padding: 20px;
}

/* Estilos para el pie del modal */
.modal-card-foot {
    justify-content: flex-end;
    padding: 20px;
}

/* Estilos para los campos del formulario */
.b-field {
    margin-bottom: 1rem;
}
</style>