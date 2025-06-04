<script setup>

import { ref } from 'vue';


const name = ref('');
const email = ref('');
const password = ref('');
const confirmPassword = ref('');

const handleRegister = async () => {
  console.log('Intentando registrar con:', name.value, email.value, password.value, confirmPassword.value);
  
  if (password.value !== confirmPassword.value) {
    alert('Las contraseñas no coinciden');
    return;
  }

  try {
    const response = await axios.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
      _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    });

    if (response.data.success) {
      window.location.href = response.data.redirect_url;
    } else {
      alert(response.data.message);
    }
  } catch (error) {
    alert('Error en el servidor.');
  }
};

</script>

    <template>
        <div class="register-container">
            <h2>Registrarse</h2>
            <form @submit.prevent="handleRegister">
                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input v-model="name" type="text" id="name" required /> 
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input v-model="email" type="email" id="email" required />
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input v-model="password" type="password" id="password" required /> 
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirmar contraseña</label>
                    <input v-model="confirmPassword" type="password" id="confirmPassword" required />   
                </div>
                <button type="submit">Registrarse</button>
            </form>
            <p>¿Ya tienes una cuenta? <a href="/login">Iniciar sesión</a></p>
        </div>
    </template>

    <style scoped>
.register-container {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  background-color: #f9f9f9;
}

h2 {
  text-align: center;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
}

input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  box-sizing: border-box;
}

button {
  width: 100%;
  padding: 10px;
  background-color: #3490dc;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
}

button:hover {
  background-color: #2779bd;
}

.links {
  text-align: center;
  margin-top: 15px;
}

.links a {
  color: #3490dc;
  text-decoration: none;
}

.links a:hover {
  text-decoration: underline;
}
</style>