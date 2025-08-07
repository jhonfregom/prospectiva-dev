@extends('layouts.auth')
@section('title', 'Restablecer Contraseña')
@section('class-footer','static')

@section('content')
<div class="reset-password">
    <section class="hero">
        <div class="hero-body">
            <div class="container has-text-centered">
                <div class="content">
                    <div class="box">
                        <article class="media">
                            <div class="media-left">
                                <figure class="avatar">
                                    <img class="image" src="{{ Vite::asset('resources/img/unad.png') }}" alt="Cloud Back">
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
                                    />
                                </div>
                                <p class="help">Mínimo 8 caracteres</p>
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

document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const resetBtn = document.getElementById('resetBtn');
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    const token = document.querySelector('input[name="token"]').value;
    const email = document.querySelector('input[name="email"]').value;

    if (password !== passwordConfirmation) {
        showMessage('errorMessage', 'errorText', 'Las contraseñas no coinciden');
        return;
    }

    if (password.length < 8) {
        showMessage('errorMessage', 'errorText', 'La contraseña debe tener al menos 8 caracteres');
        return;
    }

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

function showMessage(messageId, textId, message) {
    document.getElementById(textId).textContent = message;
    document.getElementById(messageId).style.display = 'block';

    if (messageId === 'successMessage') {
        document.getElementById('errorMessage').style.display = 'none';
    } else {
        document.getElementById('successMessage').style.display = 'none';
    }
}
</script>
@endsection