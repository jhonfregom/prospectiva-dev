import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Login from './Components/LoginComponent.vue'
import axios from 'axios'

// ------------------
// -- Axios setup --
// ------------------
window.axios = axios

// Resources (para Vite)
import.meta.glob([
  '../img/**',
  '../fonts/**',
])

// Create Vue app
const app = createApp(Login)

// Pinia setup
const pinia = createPinia()
app.use(pinia)

// Mount the app
app.mount('#app')
