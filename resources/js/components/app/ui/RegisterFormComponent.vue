<script>
import { capitalize } from '../../../../js/functions';
import { useUrlsStore } from '../../../stores/urls';

export default {
    setup() {
        const storeUrls = useUrlsStore();
        return { storeUrls };
    },
    props: {
        csrf_token: {
            type: String,
            required: true
        },
        urls_json: {
            type: String,
            required: true,
        },
        fields_json: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            first_name: '',
            last_name: '',
            user: '',
            password: '',
            confirm_password: '',
            document_id: '',
            fields: [],
            copy_msg_user: ''
        }
    },

    computed: {
        passwordMatch() {
            if (this.password === '' && this.confirm_password === '') return true;
            return this.password === this.confirm_password;
        }
    },

    
    watch: {
        first_name(newValue) {
            if (newValue !== '') {
                this.fields.first_name.error = false;
                this.fields.first_name.msg = '';
            }
        },
        last_name(newValue) {
            if (newValue !== '') {
                this.fields.last_name.error = false;
                this.fields.last_name.msg = '';
            }
        },
        user(newValue) {
            if (newValue !== '') {
                this.fields.user.error = false;
                this.fields.user.msg = '';
            }
        },
        password(newValue) {
            if (newValue !== '') {
                this.fields.password.error = false;
                this.fields.password.msg = '';
            }
        },
        document_id(newValue) {
            if (newValue !== '') {
                this.fields.document_id.error = false;
                this.fields.document_id.msg = '';
            }
        },
        confirm_password(newValue) {
            if (newValue !== '') {
                this.fields.confirm_password.error = false;
                this.fields.confirm_password.msg = '';
            }
        }
    },

    created() {
        this.initUrlsStore();
        this.fields = JSON.parse(this.fields_json);
        this.copy_msg_user = this.fields.user.msg;
    },
    methods: {
        capitalize,
        initUrlsStore() {
            let urls = JSON.parse(this.urls_json);
            this.storeUrls.setUrls(urls);
        },
        clickRegister(e) {
            e.preventDefault();
            
            // Validar campos vacíos
            this.fields.first_name.error = this.first_name === '';
            this.fields.first_name.msg = this.first_name === '' ? 'Este campo es requerido' : '';

            this.fields.last_name.error = this.last_name === '';
            this.fields.last_name.msg = this.last_name === '' ? 'Este campo es requerido' : '';

            this.fields.user.error = this.user === '';
            this.fields.user.msg = this.user === '' ? 'Este campo es requerido' : '';

            this.fields.password.error = this.password === '';
            this.fields.password.msg = this.password === '' ? 'Este campo es requerido' : '';

            this.fields.document_id.error = this.document_id === '';
            this.fields.document_id.msg = this.document_id === '' ? 'Este campo es requerido' : '';

            this.fields.confirm_password.error = this.confirm_password === '';
            this.fields.confirm_password.msg = this.confirm_password === '' ? 'Este campo es requerido' : '';

            if (!this.fields.first_name.error && !this.fields.last_name.error && 
                !this.fields.user.error && !this.fields.password.error && !this.fields.document_id.error && !this.fields.confirm_password.error) {
                
                const data = {
                    _token: this.csrf_token,
                    first_name: this.first_name,
                    last_name: this.last_name,
                    user: this.user,
                    password: this.password,
                    confirm_password: this.confirm_password, 
                    document_id: this.document_id
                };

                axios.post(this.storeUrls.register, data)
                    .then(res => {
                        if (res.data.status === 'success') {
                            window.location.href = res.data.redirect;//############<-
                            //window.location.href = res.data.redirect;
                        } else {
                            this.fields.user.error = true;
                            this.fields.user.msg = res.data.message;
                        }
                    })

                    //.catch(error => {
                    //    this.fields.user.error = true;
                    //    this.fields.user.msg = 'An error occurred during registration.';
                    //});
                    .catch(error => {//############<-
                        console.error('Registration error:', error);
                        if (error.response && error.response.data) {
                            const errors = error.response.data.errors;
                            if (errors) {
                                Object.keys(errors).forEach(field => {
                                    if (this.fields[field]) {
                                        this.fields[field].error = true;
                                        this.fields[field].msg = errors[field][0];
                                    }
                                });
                            }
                        } else {
                            this.fields.user.error = true;
                            this.fields.user.msg = 'An error occurred during registration.';
                        }
                    });
            }
        }
    }
}
</script>

<template>
    <div class="register">
        <div class="container">
            <div class="content">
                <div class="box">
                    <h1 class="title has-text-centered">Registro</h1>
                    <form
                        :action="storeUrls.register"
                        @submit="clickRegister"
                        method="POST">
                        <input type="hidden" name="_token" :value="csrf_token">
                        
                        <b-field
                            v-bind:type="{ 'is-danger' : fields.first_name.error }"
                            :message="fields.first_name.error ? fields.first_name.msg : ''">
                            <b-input
                                name="first_name"
                                size="is-large"
                                type="text"
                                :placeholder="capitalize(fields.first_name.placeholder)"
                                autofocus
                                v-model="first_name" />
                        </b-field>

                        <b-field
                            v-bind:type="{ 'is-danger' : fields.last_name.error }"
                            :message="fields.last_name.error ? fields.last_name.msg : ''">
                            <b-input
                                name="last_name"
                                size="is-large"
                                type="text"
                                :placeholder="capitalize(fields.last_name.placeholder)"
                                v-model="last_name" />
                        </b-field>

                        <b-field
                            v-bind:type="{ 'is-danger' : fields.user.error }"
                            :message="fields.user.error ? fields.user.msg : ''">
                            <b-input
                                name="user"
                                size="is-large"
                                type="text"
                                :placeholder="capitalize(fields.user.placeholder)"
                                v-model="user" />
                        </b-field>

                        <b-field
                            v-bind:type="{ 'is-danger' : fields.document_id.error }"
                            :message="fields.document_id.error ? fields.document_id.msg : ''">
                            <b-input
                                name="document_id"
                                size="is-large"
                                type="text"
                                :placeholder="capitalize(fields.document_id.placeholder)"
                                v-model="document_id" />
                        </b-field>

                        <b-field
                            v-bind:type="{ 'is-danger' : fields.password.error }"
                            :message="fields.password.error ? fields.password.msg : ''">
                            <b-input
                                name="password"
                                size="is-large"
                                type="password"
                                :placeholder="capitalize(fields.password.placeholder)"
                                v-model="password" />
                        </b-field>

                        <b-field
                            v-bind:type="{ 'is-danger' : fields.confirm_password.error }"
                            :message="fields.confirm_password.error ? fields.confirm_password.msg : ''">
                            <b-input
                                name="confirm_password"
                                size="is-large"
                                type="password"
                                :placeholder="capitalize(fields.confirm_password.placeholder)"
                                v-model="confirm_password" />
                        </b-field>

                        <b-button 
                            type="is-info" 
                            size="is-large" 
                            native-type="submit"
                            class="is-block">
                            {{ fields.submit.label }}
                        </b-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.register {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;

    .container {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        
        .content {
            width: 100%;
            
            .box {
                background: white;
                padding: 3rem;
                border-radius: 1rem;
                box-shadow: 0 0.5em 1.5em -0.125em rgba(0, 0, 0, 0.3), 0 0px 0 1px rgba(0, 0, 0, 0.02);
                
                .title {
                    color: #005368;
                    margin-bottom: 2rem;
                    text-align: center;
                    font-size: 2rem;
                    font-weight: 600;
                }

                .field {
                    margin-bottom: 1.5rem;
                }

                input {
                    border-radius: 0.7rem;
                    box-shadow: 1px 1px 18px -11px rgba(0,0,0,0.40) inset;
                    font-weight: 400;
                    font-size: 1.1rem;
                    padding: 1.5rem;
                    text-align: center;
                    width: 100%;
                    border: 1px solid #dbdbdb;
                    
                    &::placeholder {
                        color: #666;
                        text-align: center;
                    }

                    &:focus {
                        border-color: #005368;
                        box-shadow: 0 0 0 0.125em rgba(0, 83, 104, 0.25);
                    }
                }

                .button.is-info {
                    background-color: #005368;
                    border-radius: 0.7rem;
                    font-size: 1.2rem;
                    font-weight: 600;
                    margin-top: 2rem;
                    width: 40%;
                    margin-left: auto;
                    margin-right: auto;
                    display: block;
                    height: 3.5rem;
                    
                    &:hover {
                        background-color: #00B0B9;
                        border-color: #00B0B9;
                    }
                }

                .help.is-danger {
                    color: #FF3E3E;
                    font-size: 0.9rem;
                    margin-top: 0.5rem;
                }
            }
        }
    }
}
</style>