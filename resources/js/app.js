//Import styles
import '../sass/app.scss';

//Lodash import
import _ from 'lodash';
window._ = _;

// ------------------
// -- Axios setup --
// ------------------
import axios from 'axios';
window.axios = axios;

//Resources
import.meta.glob([
  '../img/**',
  '../fonts/**',
]);

// ------------------*** ------------------
// ---------- Start Vue setup ----------
// ------------------*** ------------------
import { createApp } from 'vue';
import Buefy from 'buefy'
import { createPinia } from 'pinia';

//Create App vue
const app = createApp({
    data() {
        return {
        }
    }
});

// Buefy setup
app.use(Buefy, {
    defaultIconPack: 'fas',
});

//Pinia setup
const pinia = createPinia();
app.use(pinia);

//-------------------------------------
// App components
//-------------------------------------
import LoginFormComponent from './components/app/ui/LoginFormComponent.vue';
app.component('login-form', LoginFormComponent);

import MainAppComponent from './components/app/MainAppComponent.vue';
app.component('main-app', MainAppComponent);

import RegisterFormComponent from './components/app/ui/RegisterFormComponent.vue';
app.component('register-form', RegisterFormComponent);

import RestorePasswordFormComponent from './components/app/ui/RestorePasswordFormComponent.vue';
app.component('restore-password-form', RestorePasswordFormComponent);

import FloatingBubbleComponent from './components/app/ui/FloatingBubbleComponent.vue';
app.component('floating-bubble-component', FloatingBubbleComponent);
//Mount the app
app.mount('#app');

