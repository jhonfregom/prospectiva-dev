<template>
    <b-modal
        v-model="isActive"
        has-modal-card
        trap-focus
        :destroy-on-hide="false"
        aria-role="dialog"
        aria-modal>
        
        <template #default="props">
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
    emits: ['close'],
    
    setup() {
        const variablesStore = useVariablesStore();
        return { variablesStore };
    },

    data() {
        return {
            isActive: true,
            isLoading: false,
            form: {
                name_variable: ''
            }
        };
    },

    methods: {
        async handleSubmit() {
            this.isLoading = true;
            try {
                const success = await this.variablesStore.createVariable(this.form);
                if (success) {
                    await this.variablesStore.fetchVariables();
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

        handleClose() {
            this.form = {
                name_variable: ''
            };
            this.$emit('close');
        }
    }
};
</script>

<style lang="scss" scoped>
.modal-card-body {
    padding: 20px;
}

.modal-card-foot {
    justify-content: flex-end;
    padding: 20px;
}

.b-field {
    margin-bottom: 1rem;
}
</style>
