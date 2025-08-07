<template>
    <div>
        <form
            @submit="clickRegister"
            method="POST">
            <input type="hidden" name="_token" :value="csrf_token">
            
            <!-- Selector de tipo de registro -->
            <b-field
                v-bind:type="{ 'is-danger' : fields.registration_type.error }"
                :message="fields.registration_type.error ? fields.registration_type.msg : ''">
                <div class="custom-select">
                    <div 
                        class="select-button"
                        @click="toggleRegistrationTypeDropdown"
                        :class="{ 'is-active': showRegistrationTypeDropdown }">
                        <span v-if="!registration_type" class="placeholder">{{ textsStore.getText('register.select_type_placeholder') }}</span>
                        <span v-else>{{ getRegistrationTypeText(registration_type) }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div v-if="showRegistrationTypeDropdown" class="dropdown-options">
                        <div 
                            class="option" 
                            @click="selectRegistrationType('natural')"
                            :class="{ 'selected': registration_type === 'natural' }">
                            {{ textsStore.getText('register.natural_person') }}
                        </div>
                        <div 
                            class="option" 
                            @click="selectRegistrationType('company')"
                            :class="{ 'selected': registration_type === 'company' }">
                            {{ textsStore.getText('register.company') }}
                        </div>
                    </div>
                    <input type="hidden" name="registration_type" :value="registration_type">
                </div>
            </b-field>

            <!-- Campos para Persona Natural -->
            <div v-if="registration_type === 'natural'">
                <b-field
                    v-bind:type="{ 'is-danger' : fields.first_name.error }"
                    :message="fields.first_name.error ? fields.first_name.msg : ''">
                    <b-input
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.first_name.placeholder)"
                        v-model="first_name" />
                </b-field>

                <b-field
                    v-bind:type="{ 'is-danger' : fields.last_name.error }"
                    :message="fields.last_name.error ? fields.last_name.msg : ''">
                    <b-input
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.last_name.placeholder)"
                        v-model="last_name" />
                </b-field>

                <b-field
                    v-bind:type="{ 'is-danger' : fields.document_id.error }"
                    :message="fields.document_id.error ? fields.document_id.msg : ''">
                    <b-input
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.document_id.placeholder)"
                        v-model="document_id"
                        @input="validateNumericInput('document_id', $event)" />
                </b-field>

                <b-field
                    v-bind:type="{ 'is-danger' : fields.city.error }"
                    :message="fields.city.error ? fields.city.msg : ''">
                    <b-input
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.city.placeholder)"
                        v-model="city" />
                </b-field>
            </div>

            <!-- Campos para Empresa/Organización -->
            <div v-if="registration_type === 'company'">
                <b-field
                    v-bind:type="{ 'is-danger' : fields.company_name.error }"
                    :message="fields.company_name.error ? fields.company_name.msg : ''">
                    <b-input
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.company_name.placeholder)"
                        v-model="company_name" />
                </b-field>

                <b-field
                    v-bind:type="{ 'is-danger' : fields.nit.error }"
                    :message="fields.nit.error ? fields.nit.msg : ''">
                    <b-input
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.nit.placeholder)"
                        v-model="nit"
                        @input="validateNumericInput('nit', $event)" />
                </b-field>

                <b-field
                    v-bind:type="{ 'is-danger' : fields.company_city.error }"
                    :message="fields.company_city.error ? fields.company_city.msg : ''">
                    <b-input
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.company_city.placeholder)"
                        v-model="company_city" />
                </b-field>

                <b-field
                    v-bind:type="{ 'is-danger' : fields.economic_sector.error }"
                    :message="fields.economic_sector.error ? fields.economic_sector.msg : ''">
                    <div class="custom-select">
                        <div 
                            class="select-button"
                            @click="toggleEconomicSectorDropdown"
                            :class="{ 'is-active': showEconomicSectorDropdown }">
                            <span v-if="!economic_sector" class="placeholder">Seleccione el sector económico</span>
                            <span v-else>{{ getEconomicSectorText(economic_sector) }}</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div v-if="showEconomicSectorDropdown" class="dropdown-options">
                            <div 
                                v-for="sector in economicSectors" 
                                :key="sector.value"
                                class="option" 
                                @click="selectEconomicSector(sector.value)"
                                :class="{ 'selected': economic_sector === sector.value }">
                                {{ sector.text }}
                            </div>
                        </div>
                    </div>
                </b-field>
            </div>

            <!-- Campos comunes para ambos tipos -->
            <b-field
                v-bind:type="{ 'is-danger' : fields.user.error }"
                :message="fields.user.error ? fields.user.msg : ''">
                <b-input
                    size="is-large"
                    type="email"
                    placeholder="Correo electrónico"
                    v-model="user" />
            </b-field>

            <b-field
                v-bind:type="{ 'is-danger' : fields.password.error }"
                :message="fields.password.error ? fields.password.msg : ''">
                <b-input
                    size="is-large"
                    type="password"
                    :placeholder="capitalize(fields.password.placeholder)"
                    v-model="password"
                    @input="validatePassword" />
            </b-field>
            
            <!-- Lista de verificación de contraseña -->
            <div class="password-requirements" v-if="password">
                <p class="help">La contraseña debe cumplir con los siguientes requisitos:</p>
                <ul class="password-checklist">
                    <li :class="{ 'valid': passwordLength }">
                        <i :class="passwordLength ? 'fas fa-check' : 'fas fa-times'"></i>
                        Mínimo 8 caracteres
                    </li>
                    <li :class="{ 'valid': hasLowercase }">
                        <i :class="hasLowercase ? 'fas fa-check' : 'fas fa-times'"></i>
                        Al menos una letra minúscula
                    </li>
                    <li :class="{ 'valid': hasUppercase }">
                        <i :class="hasUppercase ? 'fas fa-check' : 'fas fa-times'"></i>
                        Al menos una letra mayúscula
                    </li>
                    <li :class="{ 'valid': hasNumber }">
                        <i :class="hasNumber ? 'fas fa-check' : 'fas fa-times'"></i>
                        Al menos un número
                    </li>
                    <li :class="{ 'valid': hasSpecialChar }">
                        <i :class="hasSpecialChar ? 'fas fa-check' : 'fas fa-times'"></i>
                        Al menos un carácter especial (@$!%*?&)
                    </li>
                </ul>
            </div>

            <b-field
                v-bind:type="{ 'is-danger' : !passwordMatch }"
                :message="!passwordMatch ? 'Las contraseñas no coinciden' : ''">
                <b-input
                    size="is-large"
                    type="password"  
                    :placeholder="capitalize(fields.confirm_password.placeholder)"
                    v-model="confirm_password" />
            </b-field>

            <!-- Autorización de datos -->
            <b-field
                v-bind:type="{ 'is-danger' : !data_authorization }"
                :message="!data_authorization ? 'Debe autorizar el uso de sus datos para continuar' : ''">
                <div class="data-authorization-container">
                    <label class="checkbox">
                        <input 
                            type="checkbox" 
                            v-model="data_authorization"
                            @change="toggleDataAuthorization">
                        <span class="checkmark"></span>
                        <span class="authorization-text">
                            <a 
                                href="#" 
                                @click.prevent="toggleAuthorizationText"
                                class="authorization-link">
                                <strong>Autorización de uso de datos personales</strong>
                            </a>
                        </span>
                    </label>
                    <div v-if="showAuthorizationText" class="authorization-details">
                        <p class="authorization-content">
                            En cumplimiento de lo dispuesto por la Ley 1581 de 2012 y el Decreto 1377 de 2013, autorizo de manera previa, expresa e informada a <strong>Prospectiva UNAD</strong> para que recolecte, almacene, utilice y trate mis datos personales, con la finalidad de gestionar mi acceso al sistema, personalizar la experiencia de usuario y realizar análisis agregados para mejorar el servicio.
                        </p>
                        <p class="authorization-content">
                            Entiendo que como titular de la información tengo derecho a conocer, actualizar, rectificar y suprimir mis datos personales, así como a revocar esta autorización en cualquier momento.
                        </p>
                    </div>
                </div>
            </b-field>

            <b-button class="is-block is-info" size="is-large is-fullwidth" native-type="submit">
                {{ fields.submit.label }}
            </b-button>
        </form>
    </div>
</template>
<script>
import { capitalize } from '../../../../js/functions';
import { useUrlsStore } from '../../../stores/urls';
import { useTextsStore } from '../../../stores/texts';

export default {
    setup() {
        const storeUrls = useUrlsStore();
        const textsStore = useTextsStore();
        return { storeUrls, textsStore };
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
            registration_type: null,
            showRegistrationTypeDropdown: false,
            showEconomicSectorDropdown: false,
            // Campos para Persona Natural
            first_name: '',
            last_name: '',
            document_id: '',
            city: '',
            // Campos para Empresa/Organización
            company_name: '',
            nit: '',
            company_city: '',
            economic_sector: null,
            // Campos comunes
            user: '',
            password: '',
            confirm_password: '',
            data_authorization: false,
            showAuthorizationText: false,
            fields: [],
            copy_msg_user: '',
            // Sectores económicos (se cargarán desde la API)
            economicSectors: [],
            // Validaciones de contraseña
            passwordLength: false,
            hasLowercase: false,
            hasUppercase: false,
            hasNumber: false,
            hasSpecialChar: false
        }
    },

    computed: {
        passwordMatch() {
            if (this.password === '' && this.confirm_password === '') return true;
            return this.password === this.confirm_password;
        }
    },

    mounted() {
        // Cerrar dropdowns al hacer clic fuera
        document.addEventListener('click', this.handleClickOutside);
        
        // Cargar sectores económicos desde la API
        this.loadEconomicSectors();
    },

    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },

    watch: {
        registration_type(newValue) {
            if (newValue !== null && newValue !== '') {
                this.fields.registration_type.error = false;
                this.fields.registration_type.msg = '';
            }
        },
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
        document_id(newValue) {
            if (newValue !== '') {
                this.fields.document_id.error = false;
                this.fields.document_id.msg = '';
            }
        },
        city(newValue) {
            if (newValue !== '') {
                this.fields.city.error = false;
                this.fields.city.msg = '';
            }
        },
        company_name(newValue) {
            if (newValue !== '') {
                this.fields.company_name.error = false;
                this.fields.company_name.msg = '';
            }
        },
        nit(newValue) {
            if (newValue !== '') {
                this.fields.nit.error = false;
                this.fields.nit.msg = '';
            }
        },
        company_city(newValue) {
            if (newValue !== '') {
                this.fields.company_city.error = false;
                this.fields.company_city.msg = '';
            }
        },
        economic_sector(newValue) {
            if (newValue !== null && newValue !== '') {
                this.fields.economic_sector.error = false;
                this.fields.economic_sector.msg = '';
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
        toggleRegistrationTypeDropdown() {
            this.showRegistrationTypeDropdown = !this.showRegistrationTypeDropdown;
            this.showEconomicSectorDropdown = false;
        },
        toggleEconomicSectorDropdown() {
            this.showEconomicSectorDropdown = !this.showEconomicSectorDropdown;
            this.showRegistrationTypeDropdown = false;
        },
        selectRegistrationType(value) {
            this.registration_type = value;
            this.showRegistrationTypeDropdown = false;
        },
        selectEconomicSector(value) {
            this.economic_sector = value;
            this.showEconomicSectorDropdown = false;
        },
        getRegistrationTypeText(value) {
            if (value === 'natural') return this.textsStore.getText('register.natural_person');
            if (value === 'company') return this.textsStore.getText('register.company');
            return '';
        },
        getEconomicSectorText(value) {
            const sector = this.economicSectors.find(s => s.value === value);
            return sector ? sector.text : '';
        },
        handleClickOutside(event) {
            if (!event.target.closest('.custom-select')) {
                this.showRegistrationTypeDropdown = false;
                this.showEconomicSectorDropdown = false;
            }
        },
        
        async loadEconomicSectors() {
            try {
                const response = await fetch('/economic-sectors');
                const data = await response.json();
                
                if (data.success) {
                    this.economicSectors = data.data.map(sector => ({
                        value: sector.id.toString(),
                        text: sector.name
                    }));
                } else {
                    console.error('Error al cargar sectores económicos:', data.message);
                }
            } catch (error) {
                console.error('Error al cargar sectores económicos:', error);
            }
        },
        toggleDataAuthorization() {
            // Este método se ejecuta cuando se marca/desmarca el checkbox
        },
        toggleAuthorizationText() {
            this.showAuthorizationText = !this.showAuthorizationText;
        },
        validateNumericInput(fieldName, event) {
            // Obtener el valor actual del input
            let value = event.target.value;
            
            // Remover todos los caracteres que no sean números
            const numericValue = value.replace(/\D/g, '');
            
            // Aplicar límites de longitud según el campo
            let limitedValue = numericValue;
            if (fieldName === 'document_id') {
                // Cédula: máximo 10 dígitos
                limitedValue = numericValue.slice(0, 10);
                if (numericValue.length > 10) {
                    this.fields.document_id.error = true;
                    this.fields.document_id.msg = 'La cédula debe tener exactamente 10 dígitos';
                } else if (numericValue.length === 10) {
                    this.fields.document_id.error = false;
                    this.fields.document_id.msg = '';
                } else if (numericValue.length > 0) {
                    this.fields.document_id.error = true;
                    this.fields.document_id.msg = `La cédula debe tener 10 dígitos (actual: ${numericValue.length})`;
                } else {
                    this.fields.document_id.error = false;
                    this.fields.document_id.msg = '';
                }
            } else if (fieldName === 'nit') {
                // NIT: máximo 9 dígitos
                limitedValue = numericValue.slice(0, 9);
                if (numericValue.length > 9) {
                    this.fields.nit.error = true;
                    this.fields.nit.msg = 'El NIT debe tener exactamente 9 dígitos';
                } else if (numericValue.length === 9) {
                    this.fields.nit.error = false;
                    this.fields.nit.msg = '';
                } else if (numericValue.length > 0) {
                    this.fields.nit.error = true;
                    this.fields.nit.msg = `El NIT debe tener 9 dígitos (actual: ${numericValue.length})`;
                } else {
                    this.fields.nit.error = false;
                    this.fields.nit.msg = '';
                }
            }
            
            // Solo actualizar si el valor cambió (para evitar bucles)
            if (value !== limitedValue) {
                // Actualizar el valor del campo correspondiente
                if (fieldName === 'document_id') {
                    this.document_id = limitedValue;
                } else if (fieldName === 'nit') {
                    this.nit = limitedValue;
                }
                
                // Forzar la actualización del input
                this.$nextTick(() => {
                    event.target.value = limitedValue;
                });
            }
        },
        validatePassword() {
            const password = this.password;
            
            // Validar longitud mínima (8 caracteres)
            this.passwordLength = password.length >= 8;
            
            // Validar letra minúscula
            this.hasLowercase = /[a-z]/.test(password);
            
            // Validar letra mayúscula
            this.hasUppercase = /[A-Z]/.test(password);
            
            // Validar número
            this.hasNumber = /\d/.test(password);
            
            // Validar carácter especial
            this.hasSpecialChar = /[@$!%*?&]/.test(password);
        },
        clickRegister(e) {
            e.preventDefault();
            
            // Validar tipo de registro
            this.fields.registration_type.error = this.registration_type === null || this.registration_type === '';
            this.fields.registration_type.msg = this.registration_type === null || this.registration_type === '' ? 'Este campo es requerido' : '';

            // Validar campos según el tipo de registro
            if (this.registration_type === 'natural') {
                this.fields.first_name.error = this.first_name === '';
                this.fields.first_name.msg = this.first_name === '' ? 'Este campo es requerido' : '';

                this.fields.last_name.error = this.last_name === '';
                this.fields.last_name.msg = this.last_name === '' ? 'Este campo es requerido' : '';

                // Validar cédula (10 dígitos)
                if (this.document_id === '') {
                    this.fields.document_id.error = true;
                    this.fields.document_id.msg = 'La cédula es obligatoria';
                } else if (this.document_id.length !== 10) {
                    this.fields.document_id.error = true;
                    this.fields.document_id.msg = `La cédula debe tener exactamente 10 dígitos (actual: ${this.document_id.length})`;
                } else {
                    this.fields.document_id.error = false;
                    this.fields.document_id.msg = '';
                }

                this.fields.city.error = this.city === '';
                this.fields.city.msg = this.city === '' ? 'Este campo es requerido' : '';
            } else if (this.registration_type === 'company') {
                this.fields.company_name.error = this.company_name === '';
                this.fields.company_name.msg = this.company_name === '' ? 'Este campo es requerido' : '';

                // Validar NIT (9 dígitos)
                if (this.nit === '') {
                    this.fields.nit.error = true;
                    this.fields.nit.msg = 'El NIT es obligatorio';
                } else if (this.nit.length !== 9) {
                    this.fields.nit.error = true;
                    this.fields.nit.msg = `El NIT debe tener exactamente 9 dígitos (actual: ${this.nit.length})`;
                } else {
                    this.fields.nit.error = false;
                    this.fields.nit.msg = '';
                }

                this.fields.company_city.error = this.company_city === '';
                this.fields.company_city.msg = this.company_city === '' ? 'Este campo es requerido' : '';

                this.fields.economic_sector.error = this.economic_sector === null || this.economic_sector === '';
                this.fields.economic_sector.msg = this.economic_sector === null || this.economic_sector === '' ? 'Este campo es requerido' : '';
            }

            // Validar campos comunes
            this.fields.user.error = this.user === '';
            this.fields.user.msg = this.user === '' ? 'Este campo es requerido' : '';

            this.fields.password.error = this.password === '';
            this.fields.password.msg = this.password === '' ? 'Este campo es requerido' : '';

            this.fields.confirm_password.error = this.confirm_password === '';
            this.fields.confirm_password.msg = this.confirm_password === '' ? 'Este campo es requerido' : '';

            // Verificar si hay errores
            const hasErrors = Object.values(this.fields).some(field => field.error) || !this.data_authorization;

            if (!hasErrors && this.passwordMatch) {
                const data = {
                    _token: this.csrf_token,
                    registration_type: this.registration_type,
                    user: this.user,
                    password: this.password,
                    confirm_password: this.confirm_password,
                    data_authorization: this.data_authorization
                };

                // Agregar campos según el tipo de registro
                if (this.registration_type === 'natural') {
                    Object.assign(data, {
                        first_name: this.first_name,
                        last_name: this.last_name,
                        document_id: this.document_id,
                        city: this.city || ''
                    });
                } else if (this.registration_type === 'company') {
                    Object.assign(data, {
                        company_name: this.company_name,
                        nit: this.nit,
                        company_city: this.company_city || '',
                        economic_sector: this.economic_sector
                    });
                }

                axios.post(this.storeUrls.register, data)
                    .then(res => {
                        if (res.data.status === 'success') {
                            window.location.href = res.data.redirect;
                        } else {
                            this.fields.user.error = true;
                            this.fields.user.msg = res.data.message;
                        }
                    })
                    .catch(error => {
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

<style scoped>
.password-requirements {
    margin-top: 10px;
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 5px;
    border-left: 4px solid #3273dc;
}

.password-requirements .help {
    margin-bottom: 10px;
    font-weight: 600;
    color: #363636;
}

.password-checklist {
    list-style: none;
    padding: 0;
    margin: 0;
}

.password-checklist li {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    font-size: 14px;
    color: #666;
    transition: color 0.3s ease;
}

.password-checklist li.valid {
    color: #48c774;
}

.password-checklist li i {
    margin-right: 8px;
    font-size: 12px;
    width: 16px;
    text-align: center;
}

.password-checklist li.valid i {
    color: #48c774;
}

.password-checklist li:not(.valid) i {
    color: #f14668;
}
</style>

