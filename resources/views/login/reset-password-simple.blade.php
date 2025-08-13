<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Restablecer Contraseña - Sistema Prospectiva</title>
    
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
        
        .reset-password {
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
        
        .help {
            color: #7a7a7a;
            font-size: 0.875rem;
            margin-top: 0.25rem;
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
        
        .password-requirements {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 0.75rem;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }
        
        .requirement {
            margin: 0.25rem 0;
            display: flex;
            align-items: center;
        }
        
        .requirement.valid {
            color: #28a745;
        }
        
        .requirement.invalid {
            color: #dc3545;
        }
        
        .requirement-icon {
            margin-right: 0.5rem;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .reset-password {
                padding: 10px;
            }
            
            .box {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-password">
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
                                    <h1 class="title">🔐 Restablecer Contraseña</h1>
                                </div>
                            </article>
                            <h2 class="subtitle">Ingresa tu nueva contraseña</h2>
                            
                            <form id="resetPasswordForm">
                                <input type="hidden" name="token" value="{{ $token }}">
                                <input type="hidden" name="email" value="{{ $email }}">
                                
                                <div class="field">
                                    <label class="label">Nueva Contraseña</label>
                                    <div class="control">
                                        <input
                                            id="password"
                                            name="password"
                                            type="password"
                                            placeholder="Ingresa tu nueva contraseña"
                                            class="input"
                                            required
                                            minlength="8"
                                            maxlength="255"
                                        />
                                    </div>
                                    <p class="help">Mínimo 8 caracteres, máximo 255 caracteres</p>
                                    
                                    <!-- Requisitos de contraseña -->
                                    <div class="password-requirements" id="passwordRequirements" style="display: none;">
                                        <div class="requirement" id="req-length">
                                            <span class="requirement-icon">⭕</span>
                                            Al menos 8 caracteres
                                        </div>
                                        <div class="requirement" id="req-uppercase">
                                            <span class="requirement-icon">⭕</span>
                                            Al menos una letra mayúscula
                                        </div>
                                        <div class="requirement" id="req-lowercase">
                                            <span class="requirement-icon">⭕</span>
                                            Al menos una letra minúscula
                                        </div>
                                        <div class="requirement" id="req-number">
                                            <span class="requirement-icon">⭕</span>
                                            Al menos un número
                                        </div>
                                        <div class="requirement" id="req-special">
                                            <span class="requirement-icon">⭕</span>
                                            Al menos un carácter especial (!@#$%^&*()_+-=[]{};':"\|,.<>/?~`)
                                        </div>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Confirmar Contraseña</label>
                                    <div class="control">
                                        <input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            placeholder="Confirma tu nueva contraseña"
                                            class="input"
                                            required
                                            minlength="8"
                                            maxlength="255"
                                        />
                                    </div>
                                </div>

                                <div class="field">
                                    <div class="control">
                                        <button
                                            type="submit"
                                            id="resetBtn"
                                            class="button is-primary is-fullwidth"
                                        >
                                            🔄 Restablecer Contraseña
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

        function validatePassword(password) {
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(password)
            };
            
            return requirements;
        }

        function updatePasswordRequirements(password) {
            const requirements = validatePassword(password);
            const requirementsDiv = document.getElementById('passwordRequirements');
            
            if (password.length > 0) {
                requirementsDiv.style.display = 'block';
            } else {
                requirementsDiv.style.display = 'none';
                return;
            }
            
            // Actualizar cada requisito
            document.getElementById('req-length').className = 
                `requirement ${requirements.length ? 'valid' : 'invalid'}`;
            document.getElementById('req-length').querySelector('.requirement-icon').textContent = 
                requirements.length ? '✅' : '❌';
            
            document.getElementById('req-uppercase').className = 
                `requirement ${requirements.uppercase ? 'valid' : 'invalid'}`;
            document.getElementById('req-uppercase').querySelector('.requirement-icon').textContent = 
                requirements.uppercase ? '✅' : '❌';
            
            document.getElementById('req-lowercase').className = 
                `requirement ${requirements.lowercase ? 'valid' : 'invalid'}`;
            document.getElementById('req-lowercase').querySelector('.requirement-icon').textContent = 
                requirements.lowercase ? '✅' : '❌';
            
            document.getElementById('req-number').className = 
                `requirement ${requirements.number ? 'valid' : 'invalid'}`;
            document.getElementById('req-number').querySelector('.requirement-icon').textContent = 
                requirements.number ? '✅' : '❌';
            
            document.getElementById('req-special').className = 
                `requirement ${requirements.special ? 'valid' : 'invalid'}`;
            document.getElementById('req-special').querySelector('.requirement-icon').textContent = 
                requirements.special ? '✅' : '❌';
        }

        function isPasswordValid(password) {
            const requirements = validatePassword(password);
            return Object.values(requirements).every(req => req === true);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetPasswordForm');
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            
            if (form) {
                // Validación en tiempo real de la contraseña
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    updatePasswordRequirements(password);
                    
                    if (password.length > 255) {
                        this.classList.add('is-danger');
                        showMessage('errorMessage', 'errorText', 'La contraseña no puede exceder los 255 caracteres');
                    } else {
                        this.classList.remove('is-danger');
                        hideMessage('errorMessage');
                    }
                });

                // Validación en tiempo real de la confirmación
                passwordConfirmationInput.addEventListener('input', function() {
                    const password = passwordInput.value;
                    const confirmation = this.value;
                    
                    if (confirmation && password !== confirmation) {
                        this.classList.add('is-danger');
                        showMessage('errorMessage', 'errorText', 'Las contraseñas no coinciden');
                    } else {
                        this.classList.remove('is-danger');
                        hideMessage('errorMessage');
                    }
                });

                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const resetBtn = document.getElementById('resetBtn');
                    const password = passwordInput.value;
                    const passwordConfirmation = passwordConfirmationInput.value;
                    const token = document.querySelector('input[name="token"]').value;
                    const email = document.querySelector('input[name="email"]').value;
                    
                    // Validaciones del lado cliente
                    if (!password) {
                        showMessage('errorMessage', 'errorText', 'La nueva contraseña es obligatoria');
                        passwordInput.focus();
                        return;
                    }
                    
                    if (password.length < 8) {
                        showMessage('errorMessage', 'errorText', 'La contraseña debe tener al menos 8 caracteres');
                        passwordInput.focus();
                        return;
                    }
                    
                    if (password.length > 255) {
                        showMessage('errorMessage', 'errorText', 'La contraseña no puede exceder los 255 caracteres');
                        passwordInput.focus();
                        return;
                    }
                    
                    if (!isPasswordValid(password)) {
                        showMessage('errorMessage', 'errorText', 'La contraseña debe cumplir con todos los requisitos de seguridad');
                        passwordInput.focus();
                        return;
                    }
                    
                    if (!passwordConfirmation) {
                        showMessage('errorMessage', 'errorText', 'La confirmación de contraseña es obligatoria');
                        passwordConfirmationInput.focus();
                        return;
                    }
                    
                    if (password !== passwordConfirmation) {
                        showMessage('errorMessage', 'errorText', 'Las contraseñas no coinciden');
                        passwordConfirmationInput.focus();
                        return;
                    }
                    
                    // Deshabilitar botón
                    resetBtn.disabled = true;
                    resetBtn.innerHTML = '⏳ Procesando...';
                    
                    try {
                        const response = await fetch('{{ route("password.reset") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                token: token,
                                email: email,
                                password: password,
                                password_confirmation: passwordConfirmation
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            showMessage('successMessage', 'successText', data.message);
                            // Redirigir después de 3 segundos
                            setTimeout(() => {
                                window.location.href = data.redirect || '{{ route("login") }}';
                            }, 3000);
                        } else {
                            showMessage('errorMessage', 'errorText', data.message || 'Error al restablecer la contraseña');
                            resetBtn.disabled = false;
                            resetBtn.innerHTML = '🔄 Restablecer Contraseña';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showMessage('errorMessage', 'errorText', 'Error al procesar la solicitud');
                        resetBtn.disabled = false;
                        resetBtn.innerHTML = '🔄 Restablecer Contraseña';
                    }
                });
            }
        });
    </script>
</body>
</html>
