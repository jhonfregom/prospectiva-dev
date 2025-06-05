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


//Mount the app
app.mount('#app');

