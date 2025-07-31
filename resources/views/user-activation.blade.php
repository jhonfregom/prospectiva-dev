<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Usuario - Prospectiva</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .activation-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 90%;
        }
        .card-header {
            background: #3273dc;
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            text-align: center;
        }
        .card-content {
            padding: 30px;
        }
        .user-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3273dc;
        }
        .btn-activate {
            background: #3273dc;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-activate:hover {
            background: #2366d1;
        }
        .btn-activate:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .success-message {
            background: #48c774;
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .error-message {
            background: #f14668;
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="activation-card">
        <div class="card-header">
            <h1 class="title is-4 has-text-white">
                <i class="fas fa-user-check"></i>
                Activación de Usuario
            </h1>
        </div>
        
        <div class="card-content">
            @if($isActive)
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <strong>Usuario ya activado</strong>
                    <p>Este usuario ya ha sido activado anteriormente y puede acceder al sistema.</p>
                </div>
                
                <div class="user-info">
                    <h3 class="title is-5">Datos del usuario:</h3>
                    <p><strong>Nombre:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
                    <p><strong>Correo:</strong> {{ $user->user }}</p>
                    <p><strong>Documento:</strong> {{ $user->document_id }}</p>
                    <p><strong>Tipo de registro:</strong> {{ $user->registration_type === 'natural' ? 'Persona Natural' : 'Empresa' }}</p>
                    @if($user->registration_type === 'company' && $user->economic_sector)
                        <p><strong>Sector económico:</strong> {{ $user->economic_sector }}</p>
                    @endif
                    <p><strong>Estado:</strong> <span class="tag is-success">Activo</span></p>
                </div>
            @else
                <div class="user-info">
                    <h3 class="title is-5">Datos del usuario:</h3>
                    <p><strong>Nombre:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
                    <p><strong>Correo:</strong> {{ $user->user }}</p>
                    <p><strong>Documento:</strong> {{ $user->document_id }}</p>
                    <p><strong>Tipo de registro:</strong> {{ $user->registration_type === 'natural' ? 'Persona Natural' : 'Empresa' }}</p>
                    @if($user->registration_type === 'company' && $user->economic_sector)
                        <p><strong>Sector económico:</strong> {{ $user->economic_sector }}</p>
                    @endif
                    <p><strong>Estado:</strong> <span class="tag is-warning">Pendiente de activación</span></p>
                </div>
                
                <div style="text-align: center;">
                    <button id="activateBtn" class="btn-activate" onclick="activateUser()">
                        <i class="fas fa-user-check"></i>
                        Activar Usuario
                    </button>
                </div>
                
                <div id="messageContainer" style="display: none;"></div>
            @endif
        </div>
    </div>

    <script>
        function activateUser() {
            const btn = document.getElementById('activateBtn');
            const messageContainer = document.getElementById('messageContainer');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Activando...';
            
            fetch('{{ route("user.activate", ["userId" => $user->id, "token" => $token]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageContainer.innerHTML = `
                        <div class="success-message">
                            <i class="fas fa-check-circle"></i>
                            <strong>¡Usuario activado correctamente!</strong>
                            <p>El usuario ahora puede acceder al sistema.</p>
                        </div>
                    `;
                    btn.style.display = 'none';
                } else {
                    messageContainer.innerHTML = `
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Error:</strong> ${data.message}
                        </div>
                    `;
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-user-check"></i> Activar Usuario';
                }
                messageContainer.style.display = 'block';
            })
            .catch(error => {
                messageContainer.innerHTML = `
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Error:</strong> Ocurrió un error al activar el usuario.
                    </div>
                `;
                messageContainer.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-user-check"></i> Activar Usuario';
            });
        }
    </script>
</body>
</html> 