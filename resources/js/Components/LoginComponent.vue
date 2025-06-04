<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  csrf_token: String
})


const email = ref('')
const password = ref('')

const handleLogin = async () => {
  
  console.log('Intentando iniciar sesión con:', email.value, password.value)
  
  try {
    const response = await axios.post('/login', {
      email: email.value,
      password: password.value,
      _token: props.csrf_token 
    });

    if (response.data.success) {
     
      window.location.href = response.data.redirect_url;
    } else {
      
      alert(response.data.message);
    }
  } catch (error) {
    alert('Error en el servidor.');
  }
}

const goToRegister = () => {
  console.log('Ir a registro')
  window.location.href = '/register';
}

const goToForgotPassword = () => {
  console.log('Ir a recuperar contraseña')
}
</script>

<template>
  <div class="login-container">
    <h2>Iniciar sesión</h2>
    <form @submit.prevent="handleLogin">
      <div class="form-group">
        <label for="email">Correo electrónico</label>
        <input v-model="email" type="email" id="email" required />
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input v-model="password" type="password" id="password" required />
      </div>

      <button type="submit">Iniciar sesión</button>
    </form>

    <div class="links">
      <a href="#" @click.prevent="goToRegister">Registrarse</a> |
      <a href="#" @click.prevent="goToForgotPassword">¿Olvidó su contraseña?</a>
    </div>
  </div>
</template>



<style scoped>
.login-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
}

h2 {
  text-align: center;
}

.form-group {
  margin-bottom: 15px;
}

input[type="email"],
input[type="password"] {
  width: 100%;
  padding: 8px;
  box-sizing: border-box;
}

button {
  width: 100%;
  padding: 10px;
  background-color: #3490dc;
  color: white;
  border: none;
  border-radius: 4px;
}

.links {
  text-align: center;
  margin-top: 15px;
}

.links a {
  color: #3490dc;
  text-decoration: none;
  margin: 0 5px;
}
</style>
