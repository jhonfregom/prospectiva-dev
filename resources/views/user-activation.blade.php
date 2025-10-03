<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Usuario - Foresight tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .activation-container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 100%;
            margin: 20px;
        }
        .activation-header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .activation-header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .activation-header h1::before {
            content: "🔐";
            margin-right: 15px;
            font-size: 32px;
        }
        .activation-header p {
            margin: 10px 0 0 0;
            color: #666;
            font-size: 16px;
        }
        .user-info {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            border-left: 5px solid #007bff;
        }
        .user-info h3 {
            margin-top: 0;
            color: #007bff;
            font-size: 20px;
            display: flex;
            align-items: center;
        }
        .user-info h3::before {
            content: "📋";
            margin-right: 12px;
            font-size: 24px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            font-size: 16px;
        }
        .info-value {
            color: #6c757d;
            font-size: 16px;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .activation-question {
            background-color: #e3f2fd;
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            border-left: 5px solid #2196f3;
            text-align: center;
        }
        .activation-question h3 {
            margin-top: 0;
            color: #1976d2;
            font-size: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .activation-question h3::before {
            content: "❓";
            margin-right: 12px;
            font-size: 26px;
        }
        .activation-question p {
            color: #424242;
            font-size: 16px;
            margin: 15px 0 0 0;
        }
        .activation-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        .btn-activate, .btn-cancel {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-activate {
            background-color: #28a745;
            color: white;
        }
        .btn-activate:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        .btn-activate:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        .btn-cancel:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        .btn-cancel:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            display: none;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        @media (max-width: 768px) {
            .activation-container {
                margin: 10px;
                padding: 20px;
            }
            .activation-buttons {
                flex-direction: column;
                align-items: center;
            }
            .btn-activate, .btn-cancel {
                width: 100%;
                max-width: 300px;
            }
            .info-row {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="activation-container">
        <div class="activation-header">
            <h1>Activación de Usuario</h1>
            <p>Foresight tool</p>
        </div>
        
        <div class="user-info">
            <h3>Información del Usuario</h3>
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
            <h3>¿Desea habilitar al usuario?</h3>
            <p>Al habilitar al usuario, podrá acceder al sistema de Foresight tool.</p>
        </div>
        
        <div class="activation-buttons">
            <button id="activateBtn" class="btn-activate" onclick="activateUser()">
                ✅ Habilitar Usuario
            </button>
            <button id="cancelBtn" class="btn-cancel" onclick="cancelActivation()">
                ❌ Cancelar
            </button>
        </div>
        
        <div id="message" class="message"></div>
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
                    }, 3000);
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
                    }, 3000);
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
</body>
</html>





