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
                    <p class="modal-card-title">{{ textsStore.getText('variables_section.modal.title') }}</p>
                </header>
                <section class="modal-card-body">
                    <b-field :label="textsStore.getText('variables_section.modal.name_label')">
                        <b-input
                            v-model="form.name_variable"
                            :placeholder="textsStore.getText('variables_section.modal.name_placeholder')"
                            required>
                        </b-input>
                    </b-field>
                </section>
                <footer class="modal-card-foot">
                    <b-button
                        :loading="isLoading"
                        type="is-primary"
                        class="modal-button"
                        @click="handleSubmit">
                        {{ textsStore.getText('variables_section.modal.save') }}
                    </b-button>
                    <b-button
                        type="is-danger"
                        class="modal-button"
                        @click="handleClose">
                        {{ textsStore.getText('variables_section.modal.cancel') }}
                    </b-button>
                </footer>
            </div>
        </template>
    </b-modal>
</template>

<script>
import { useVariablesStore } from '@/stores/variables';
import { useTextsStore } from '@/stores/texts';

export default {
    emits: ['close'],
    
    setup() {
        const variablesStore = useVariablesStore();
        const textsStore = useTextsStore();
        return { variablesStore, textsStore };
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
                        message: this.textsStore.getText('variables_section.messages.create_success'),
                        type: 'is-success'
                    });
                    this.handleClose();
                } else {
                    throw new Error('No se pudo crear la variable');
                }
            } catch (error) {
                let errorMessage = this.textsStore.getText('variables_section.messages.create_error');
                if (error.response && error.response.status === 400) {
                    errorMessage = error.response.data.message || this.textsStore.getText('variables_section.messages.limit_reached');
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
    gap: 10px;
}

.modal-button {
    width: 120px !important;
    min-width: 120px !important;
    max-width: 120px !important;
    height: 40px !important;
    min-height: 40px !important;
    max-height: 40px !important;
    flex: 0 0 120px !important;
    text-align: center;
    box-sizing: border-box;
    display: inline-block !important;
    line-height: 1 !important;
    padding: 8px 16px !important;
}

.b-field {
    margin-bottom: 1rem;
}
</style>