<template>
    <div class="restore-password-form">
        <form @submit.prevent="sendResetLink">
            <div class="field">
                <label class="label">{{ fields.email.label }}</label>
                <div class="control">
                    <input
                        v-model="email"
                        type="email"
                        :placeholder="fields.email.placeholder"
                        class="input"
                        :class="{ 'is-danger': fields.email.error }"
                        required
                    />
                </div>
                <p v-if="fields.email.error" class="help is-danger">{{ fields.email.msg }}</p>
            </div>

            <div class="field">
                <div class="control">
                    <button
                        type="submit"
                        class="button is-primary is-fullwidth"
                        :disabled="loading"
                    >
                        <span v-if="loading">⏳ Enviando...</span>
                        <span v-else>{{ texts.send_link }}</span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Mensaje de éxito -->
        <div v-if="successMessage" class="notification is-success">
            <button class="delete" @click="successMessage = ''"></button>
            {{ successMessage }}
        </div>

        <!-- Mensaje de error -->
        <div v-if="errorMessage" class="notification is-danger">
            <button class="delete" @click="errorMessage = ''"></button>
            {{ errorMessage }}
        </div>
    </div>
</template>

<script>
import { ref, reactive } from 'vue'
import axios from 'axios'

export default {
    name: 'RestorePasswordForm',
    props: {
        csrf_token: {
            type: String,
            required: true
        },
        urls_json: {
            type: String,
            required: true
        },
        fields_json: {
            type: String,
            required: true
        }
    },
    setup(props) {
        const email = ref('')
        const loading = ref(false)
        const successMessage = ref('')
        const errorMessage = ref('')

        const urls = JSON.parse(props.urls_json)
        const fields = reactive(JSON.parse(props.fields_json))
        const texts = {
            send_link: 'Enviar enlace de restablecimiento'
        }

        const sendResetLink = async () => {
            loading.value = true
            errorMessage.value = ''
            successMessage.value = ''

            try {
                const response = await axios.post(urls.send_reset_link, {
                    email: email.value
                }, {
                    headers: {
                        'X-CSRF-TOKEN': props.csrf_token,
                        'Content-Type': 'application/json'
                    }
                })

                if (response.data.status === 'success') {
                    successMessage.value = response.data.message
                    email.value = ''
                } else {
                    errorMessage.value = response.data.message || 'Error al enviar el enlace'
                }
            } catch (error) {
                console.error('Error:', error)
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage.value = error.response.data.message
                } else {
                    errorMessage.value = 'Error al enviar el enlace de restablecimiento'
                }
            } finally {
                loading.value = false
            }
        }

        return {
            email,
            loading,
            successMessage,
            errorMessage,
            fields,
            texts,
            sendResetLink
        }
    }
}
</script>

<style scoped>
.restore-password-form {
    max-width: 400px;
    margin: 0 auto;
}

.notification {
    margin-top: 1rem;
}
</style>



