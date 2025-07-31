<template>
    <div>
        <form
            :action="storeUrls.register"
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
                        <span v-if="!registration_type" class="placeholder">Seleccione el tipo de registro</span>
                        <span v-else>{{ getRegistrationTypeText(registration_type) }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div v-if="showRegistrationTypeDropdown" class="dropdown-options">
                        <div 
                            class="option" 
                            @click="selectRegistrationType('natural')"
                            :class="{ 'selected': registration_type === 'natural' }">
                            Persona Natural
                        </div>
                        <div 
                            class="option" 
                            @click="selectRegistrationType('company')"
                            :class="{ 'selected': registration_type === 'company' }">
                            Empresa u Organización
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
                        name="first_name"
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.first_name.placeholder)"
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
                    v-bind:type="{ 'is-danger' : fields.city.error }"
                    :message="fields.city.error ? fields.city.msg : ''">
                    <b-input
                        name="city"
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
                        name="company_name"
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.company_name.placeholder)"
                        v-model="company_name" />
                </b-field>

                <b-field
                    v-bind:type="{ 'is-danger' : fields.nit.error }"
                    :message="fields.nit.error ? fields.nit.msg : ''">
                    <b-input
                        name="nit"
                        size="is-large"
                        type="text"
                        :placeholder="capitalize(fields.nit.placeholder)"
                        v-model="nit" />
                </b-field>

                <b-field
                    v-bind:type="{ 'is-danger' : fields.company_city.error }"
                    :message="fields.company_city.error ? fields.company_city.msg : ''">
                    <b-input
                        name="company_city"
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
                        <input type="hidden" name="economic_sector" :value="economic_sector">
                    </div>
                </b-field>
            </div>

            <!-- Campos comunes para ambos tipos -->
            <b-field
                v-bind:type="{ 'is-danger' : fields.user.error }"
                :message="fields.user.error ? fields.user.msg : ''">
                <b-input
                    name="user"
                    size="is-large"
                    type="email"
                    placeholder="Correo electrónico"
                    v-model="user" />
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
                v-bind:type="{ 'is-danger' : !passwordMatch }"
                :message="!passwordMatch ? 'Las contraseñas no coinciden' : ''">
                <b-input
                    name="confirm_password"
                    size="is-large"
                    type="password"  
                    :placeholder="capitalize(fields.confirm_password.placeholder)"
                    v-model="confirm_password" />
            </b-field>

            <!-- Autorización de uso de datos -->
            <div class="data-authorization-section">
                <div class="authorization-container">
                    <div class="authorization-label">
                        <div class="custom-checkbox" @click="toggleAuthorization">
                            <input 
                                type="checkbox" 
                                v-model="data_authorization" 
                                name="data_authorization"
                                class="authorization-checkbox">
                            <span class="checkmark"></span>
                        </div>
                        <span class="authorization-text">
                            Acepto la 
                            <a href="#" @click.prevent="showDataAuthorizationModal = true" class="authorization-link">
                                autorización de uso de datos
                            </a>
                        </span>
                    </div>
                    <div v-if="!data_authorization" class="authorization-error">
                        Debe aceptar la autorización de uso de datos
                    </div>
                </div>
            </div>

            <b-button class="is-block is-info" size="is-large is-fullwidth" native-type="submit">
                {{ fields.submit.label }}
            </b-button>
        </form>

        <!-- Modal de autorización de uso de datos -->
        <div v-if="showDataAuthorizationModal" class="modal-overlay" @click="showDataAuthorizationModal = false">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h3 class="modal-title">Autorización de uso de datos</h3>
                    <button class="modal-close" @click="showDataAuthorizationModal = false">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="authorization-text">
                        En cumplimiento de lo dispuesto por la Ley 1581 de 2012 y el Decreto 1377 de 2013, autorizo de manera previa, expresa e informada a <strong>Proyecto Prospectiva</strong> para que recolecte, almacene, utilice y trate mis datos personales, con la finalidad de gestionar mi acceso al sistema, personalizar la experiencia de usuario y realizar análisis agregados para mejorar el servicio.
                    </p>
                    <p class="authorization-text">
                        Entiendo que como titular de la información tengo derecho a conocer, actualizar, rectificar y suprimir mis datos personales, así como a revocar esta autorización en cualquier momento.
                    </p>
                </div>
                <div class="modal-footer">
                    <button class="button is-info" @click="showDataAuthorizationModal = false">
                        Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
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
            showDataAuthorizationModal: false,
            fields: [],
            copy_msg_user: '',
            // Sectores económicos
            economicSectors: [
                { value: '1', text: 'Agricultura, ganadería, caza, silvicultura y pesca' },
                { value: '2', text: 'Explotación de minas y canteras' },
                { value: '3', text: 'Industrias manufactureras' },
                { value: '4', text: 'Suministro de electricidad, gas, vapor y aire acondicionado' },
                { value: '5', text: 'Suministro de agua; gestión de residuos y saneamiento ambiental' },
                { value: '6', text: 'Construcción' },
                { value: '7', text: 'Comercio al por mayor y al por menor' },
                { value: '8', text: 'Transporte y almacenamiento' },
                { value: '9', text: 'Alojamiento y servicios de comida' },
                { value: '10', text: 'Información y comunicaciones' },
                { value: '11', text: 'Actividades financieras y de seguros' },
                { value: '12', text: 'Actividades inmobiliarias' },
                { value: '13', text: 'Actividades profesionales, científicas y técnicas' },
                { value: '14', text: 'Actividades administrativas y de apoyo' },
                { value: '15', text: 'Educación' },
                { value: '16', text: 'Salud humana y asistencia social' },
                { value: '17', text: 'Arte, entretenimiento y recreación' },
                { value: '18', text: 'Otros servicios (organizaciones sociales, sindicatos, ONG, etc.)' },
                { value: '19', text: 'Administración pública y defensa' },
                { value: '20', text: 'Actividades de los hogares como empleadores' },
                { value: '21', text: 'Organismos internacionales y otras instituciones extraterritoriales' }
            ]
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
            if (value === 'natural') return 'Persona Natural';
            if (value === 'company') return 'Empresa u Organización';
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

                this.fields.document_id.error = this.document_id === '';
                this.fields.document_id.msg = this.document_id === '' ? 'Este campo es requerido' : '';

                this.fields.city.error = this.city === '';
                this.fields.city.msg = this.city === '' ? 'Este campo es requerido' : '';
            } else if (this.registration_type === 'company') {
                this.fields.company_name.error = this.company_name === '';
                this.fields.company_name.msg = this.company_name === '' ? 'Este campo es requerido' : '';

                this.fields.nit.error = this.nit === '';
                this.fields.nit.msg = this.nit === '' ? 'Este campo es requerido' : '';

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

            // Validar autorización de datos
            const dataAuthorizationError = !this.data_authorization;

            // Verificar si hay errores
            const hasErrors = Object.values(this.fields).some(field => field.error) || dataAuthorizationError;

            if (!hasErrors && this.passwordMatch) {
                const data = {
                    _token: this.csrf_token,
                    registration_type: this.registration_type,
                    user: this.user,
                    password: this.password,
                    confirm_password: this.confirm_password,
                    data_authorization: this.data_authorization ? "1" : "0"
                };

                // Agregar campos según el tipo de registro
                if (this.registration_type === 'natural') {
                    Object.assign(data, {
                        first_name: this.first_name,
                        last_name: this.last_name,
                        document_id: this.document_id,
                        city: this.city
                    });
                } else if (this.registration_type === 'company') {
                    Object.assign(data, {
                        company_name: this.company_name,
                        nit: this.nit,
                        company_city: this.company_city,
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
        },
        toggleAuthorization() {
            this.data_authorization = !this.data_authorization;
        }
    }
}
</script>

<style scoped>
/* Estilos para el checkbox de autorización */
.data-authorization-section {
    margin: 20px 0;
}

.authorization-container {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.authorization-label {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    gap: 12px;
    font-size: 14px;
    line-height: 1.5;
    color: #495057;
    user-select: none;
}

.custom-checkbox {
    position: relative;
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    cursor: pointer;
    margin-top: 2px;
}

.authorization-checkbox {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 16px;
    width: 16px;
    background-color: #fff;
    border: 2px solid #ddd;
    border-radius: 3px;
    transition: all 0.2s ease;
    margin-top: 0;
}

.custom-checkbox:hover .checkmark {
    border-color: #3273dc;
}

.authorization-checkbox:checked ~ .checkmark {
    background-color: #3273dc;
    border-color: #3273dc;
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
    left: 4px;
    top: 1px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.authorization-checkbox:checked ~ .checkmark:after {
    display: block;
}

.authorization-text {
    font-weight: 400;
    text-align: center;
}

.authorization-checkbox {
    margin: 0;
    cursor: pointer;
    width: 14px;
    height: 14px;
    accent-color: #3273dc;
    flex-shrink: 0;
    padding: 0;
    border: none;
    outline: none;
}

.authorization-link {
    color: #3273dc;
    text-decoration: underline;
    font-weight: 500;
    transition: color 0.2s ease;
    pointer-events: auto;
}

.authorization-link:hover {
    color: #2366d1;
    text-decoration: none;
}

.authorization-error {
    margin-top: 8px;
    color: #dc3545;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
}

/* Estilos para el modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    backdrop-filter: blur(2px);
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 650px;
    width: 90%;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    border: 1px solid #e9ecef;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 25px 20px 25px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
}

.modal-title {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #2c3e50;
}

.modal-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: #6c757d;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s ease;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: #495057;
    background: #e9ecef;
}

.modal-body {
    padding: 25px;
    background: white;
}

.authorization-text {
    margin: 0 0 20px 0;
    line-height: 1.7;
    color: #495057;
    font-size: 15px;
    text-align: justify;
}

.modal-footer {
    padding: 20px 25px 25px 25px;
    border-top: 1px solid #e9ecef;
    text-align: right;
    background: #f8f9fa;
    border-radius: 0 0 12px 12px;
}
</style>

