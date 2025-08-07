<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recuperar Contraseña - Sistema Prospectiva</title>
    
    <!-- Bulma CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    
    <style>
        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .restore-password {
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }
        
        .hero {
            background: none;
            min-height: auto;
        }
        
        .hero-body {
            padding: 0;
        }
        
        .box {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        .avatar img {
            border-radius: 50%;
            max-width: 80px;
            height: auto;
        }
        
        .notification {
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .button.is-primary {
            background-color: #3273dc;
            border-color: #3273dc;
        }
        
        .button.is-primary:hover {
            background-color: #2366d1;
            border-color: #2366d1;
        }
        
        .sub-content {
            margin-top: 1.5rem;
        }
        
        .sub-content a {
            color: #3273dc;
            text-decoration: none;
        }
        
        .sub-content a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .restore-password {
                padding: 10px;
            }
            
            .box {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="restore-password">
        <section class="hero">
            <div class="hero-body">
                <div class="container has-text-centered">
                    <div class="content">
                        <div class="box">
                            <article class="media">
                                <div class="media-left">
                                    <figure class="avatar">
                                        <img src="{{ Vite::asset('resources/img/unad.png') }}" alt="Logo">
                                    </figure>
                                </div>
                                <div class="media-content">
                                    <h1 class="title">🔐 Recuperar Contraseña</h1>
                                </div>
                            </article>
                            <h2 class="subtitle">Ingresa tu correo electrónico para recibir un enlace de restablecimiento</h2>
                            
                            <form id="restorePasswordForm">
                                <div class="field">
                                    <label class="label">Correo Electrónico</label>
                                    <div class="control">
                                        <input
                                            id="email"
                                            name="email"
                                            type="email"
                                            placeholder="Ingresa tu correo electrónico"
                                            class="input"
                                            required
                                            maxlength="255"
                                        />
                                    </div>
                                    <p class="help">Máximo 255 caracteres</p>
                                </div>

                                <div class="field">
                                    <div class="control">
                                        <button
                                            type="submit"
                                            id="submitBtn"
                                            class="button is-primary is-fullwidth"
                                        >
                                            📧 Enviar Enlace de Restablecimiento
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Mensaje de éxito -->
                            <div id="successMessage" class="notification is-success" style="display: none;">
                                <button class="delete" onclick="hideMessage('successMessage')"></button>
                                <span id="successText"></span>
                            </div>

                            <!-- Mensaje de error -->
                            <div id="errorMessage" class="notification is-danger" style="display: none;">
                                <button class="delete" onclick="hideMessage('errorMessage')"></button>
                                <span id="errorText"></span>
                            </div>

                            <div class="sub-content columns is-mobile is-centered">
                                <a class="column" href="{{ route('login') }}">← Volver al login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        function hideMessage(messageId) {
            document.getElementById(messageId).style.display = 'none';
        }

        function showMessage(messageId, textId, message) {
            document.getElementById(textId).textContent = message;
            document.getElementById(messageId).style.display = 'block';
            
            // Ocultar otros mensajes
            if (messageId === 'successMessage') {
                document.getElementById('errorMessage').style.display = 'none';
            } else {
                document.getElementById('successMessage').style.display = 'none';
            }
        }

        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('restorePasswordForm');
            const emailInput = document.getElementById('email');
            
            if (form) {
                // Validación en tiempo real del email
                emailInput.addEventListener('input', function() {
                    const email = this.value.trim();
                    
                    if (email.length > 255) {
                        this.classList.add('is-danger');
                        showMessage('errorMessage', 'errorText', 'El correo electrónico no puede exceder los 255 caracteres');
                    } else if (email && !validateEmail(email)) {
                        this.classList.add('is-danger');
                        showMessage('errorMessage', 'errorText', 'El formato del correo electrónico no es válido');
                    } else {
                        this.classList.remove('is-danger');
                        hideMessage('errorMessage');
                    }
                });

                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const submitBtn = document.getElementById('submitBtn');
                    const email = emailInput.value.trim();
                    
                    // Validaciones del lado cliente
                    if (!email) {
                        showMessage('errorMessage', 'errorText', 'El correo electrónico es obligatorio');
                        emailInput.focus();
                        return;
                    }
                    
                    if (email.length > 255) {
                        showMessage('errorMessage', 'errorText', 'El correo electrónico no puede exceder los 255 caracteres');
                        emailInput.focus();
                        return;
                    }
                    
                    if (!validateEmail(email)) {
                        showMessage('errorMessage', 'errorText', 'El formato del correo electrónico no es válido');
                        emailInput.focus();
                        return;
                    }
                    
                    // Deshabilitar botón
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '⏳ Enviando...';
                    
                    try {
                        const response = await fetch('{{ route("password.email") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                email: email
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            showMessage('successMessage', 'successText', data.message);
                            // Limpiar formulario
                            emailInput.value = '';
                            emailInput.classList.remove('is-danger');
                        } else {
                            showMessage('errorMessage', 'errorText', data.message || 'Error al enviar el enlace de restablecimiento');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showMessage('errorMessage', 'errorText', 'Error al procesar la solicitud. Verifica tu conexión a internet.');
                    } finally {
                        // Habilitar botón
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '📧 Enviar Enlace de Restablecimiento';
                    }
                });
            }
        });
    </script>
</body>
</html>
