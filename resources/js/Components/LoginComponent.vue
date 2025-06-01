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

<script setup>
import { ref } from 'vue'

const email = ref('')
const password = ref('')

const handleLogin = () => {
  console.log('Login:', email.value, password.value)
  // Aquí puedes llamar a la API o store
}

const goToRegister = () => {
  console.log('Ir a registro')
  // Aquí puedes redireccionar con router o window.location.href
}

const goToForgotPassword = () => {
  console.log('Ir a recuperar contraseña')
}
</script>

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
