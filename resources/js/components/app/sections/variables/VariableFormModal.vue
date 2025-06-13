<!-- 
    VariableFormModal.vue
    
    Este componente implementa el modal para crear nuevas variables.
    Proporciona:
    - Un formulario para ingresar el nombre de la variable
    - Validación de datos
    - Manejo de errores
    - Integración con el store de variables
-->
<template>
    <!-- Modal para crear nuevas variables -->
    <b-modal
        v-model="isActive"
        has-modal-card
        trap-focus
        :destroy-on-hide="false"
        aria-role="dialog"
        aria-modal>
        
        <template #default="props">
            <!-- Formulario de creación -->
            <div class="modal-card" style="width: auto">
                <header class="modal-card-head">
                    <p class="modal-card-title">Nueva Variable</p>
                </header>
                <section class="modal-card-body">
                    <b-field label="Nombre de la Variable">
                        <b-input
                            v-model="form.name_variable"
                            placeholder="Ingrese el nombre de la variable"
                            required>
                        </b-input>
                    </b-field>
                </section>
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
    // Define los eventos que emite el componente
    emits: ['close'],
    
    // Inicializa el store de variables
    setup() {
        const variablesStore = useVariablesStore();
        return { variablesStore };
    },

    // Estado local del componente
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
         * 3. Muestra notificaciones de éxito/error
         * 4. Cierra el modal si la creación fue exitosa
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
                let errorMessage = 'Error al crear la variable';
                if (error.response && error.response.status === 400) {
                    errorMessage = error.response.data.message || 'Se ha alcanzado el límite máximo de 15 variables permitidas';
                }
                this.$buefy.toast.open({
                    message: errorMessage,
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
         * 1. Reinicia el formulario a su estado inicial
         * 2. Emite el evento de cierre al componente padre
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

/* Espaciado entre campos del formulario */
.b-field {
    margin-bottom: 1rem;
}
</style>