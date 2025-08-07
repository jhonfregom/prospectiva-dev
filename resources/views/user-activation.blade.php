@extends('layouts.activation')
@section('title', 'Activación de Usuario')
@section('class-footer','static')

@section('content')
<div class="activation-container">
    <div class="activation-box">
        <div class="activation-header">
            <h1>🔐 Activación de Usuario</h1>
            <p>Sistema de Prospectiva</p>
        </div>
        
        <div class="user-info">
            <h3>📋 Información del Usuario</h3>
            <div class="info-row">
                <span class="info-label">Nombre:</span>
                <span class="info-value">{{ $user->first_name }} {{ $user->last_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Identificación:</span>
                <span class="info-value">{{ $user->document_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Usuario:</span>
                <span class="info-value">{{ $user->user }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value status-pending">Pendiente de Activación</span>
            </div>
        </div>
        
        <div class="activation-question">
            <h3>❓ ¿Desea habilitar al usuario?</h3>
            <p>Al habilitar al usuario, podrá acceder al sistema de Prospectiva.</p>
        </div>
        
        <div class="activation-buttons">
            <button id="activateBtn" class="btn-activate" onclick="activateUser()">
                ✅ Habilitar Usuario
            </button>
            <button id="cancelBtn" class="btn-cancel" onclick="cancelActivation()">
                ❌ Cancelar
            </button>
        </div>
        
        <div id="message" class="message" style="display: none;"></div>
    </div>
</div>

<script>
function activateUser() {
    const activateBtn = document.getElementById('activateBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const message = document.getElementById('message');

    activateBtn.disabled = true;
    cancelBtn.disabled = true;
    activateBtn.textContent = '⏳ Procesando...';

    fetch(`/user-activation/activate/{{ $user->id }}/{{ $token }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            message.className = 'message success';
            message.textContent = data.message;
            message.style.display = 'block';

            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        } else {
            message.className = 'message error';
            message.textContent = data.message;
            message.style.display = 'block';

            activateBtn.disabled = false;
            cancelBtn.disabled = false;
            activateBtn.textContent = '✅ Habilitar Usuario';
        }
    })
    .catch(error => {
        message.className = 'message error';
        message.textContent = 'Error al procesar la solicitud.';
        message.style.display = 'block';

        activateBtn.disabled = false;
        cancelBtn.disabled = false;
        activateBtn.textContent = '✅ Habilitar Usuario';
    });
}

function cancelActivation() {
    const activateBtn = document.getElementById('activateBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const message = document.getElementById('message');

    activateBtn.disabled = true;
    cancelBtn.disabled = true;
    cancelBtn.textContent = '⏳ Procesando...';

    fetch(`/user-activation/cancel/{{ $user->id }}/{{ $token }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            message.className = 'message info';
            message.textContent = data.message;
            message.style.display = 'block';

            setTimeout(() => {
                window.location.href = '/';
            }, 2000);
        } else {
            message.className = 'message error';
            message.textContent = data.message;
            message.style.display = 'block';

            activateBtn.disabled = false;
            cancelBtn.disabled = false;
            cancelBtn.textContent = '❌ Cancelar';
        }
    })
    .catch(error => {
        message.className = 'message error';
        message.textContent = 'Error al procesar la solicitud.';
        message.style.display = 'block';

        activateBtn.disabled = false;
        cancelBtn.disabled = false;
        cancelBtn.textContent = '❌ Cancelar';
    });
}
</script>

@endsection