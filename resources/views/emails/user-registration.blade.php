<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario Registrado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: 
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: 
        }
        .email-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid 
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: 
            margin: 0;
            font-size: 24px;
        }
        .user-info {
            background-color: 
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid 
        }
        .user-info h3 {
            margin-top: 0;
            color: 
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid 
        }
        .info-label {
            font-weight: bold;
            color: 
        }
        .info-value {
            color: 
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid 
            color: 
            font-size: 14px;
        }
        .timestamp {
            background-color: 
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            color: 
        }
        .activation-section {
            background-color: 
            padding: 25px;
            border-radius: 10px;
            margin: 25px 0;
            border: 2px solid 
            text-align: center;
        }
        .activation-section h3 {
            color: 
            margin: 0 0 15px 0;
            font-size: 20px;
        }
        .activation-link {
            margin: 20px 0;
        }
        .btn-activate-link {
            display: inline-block;
            background-color: 
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn-activate-link:hover {
            background-color: 
        }
        .activation-note {
            background-color: 
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0 0 0;
            color: 
            font-size: 14px;
            border-left: 4px solid 
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🚀 Nuevo Usuario Registrado</h1>
            <p>Sistema de Prospectiva</p>
        </div>

        <p>Hola Administrador,</p>
        
        <p>Se ha registrado un nuevo usuario en el sistema de Prospectiva. A continuación los detalles:</p>

        <div class="user-info">
            <h3>📋 Información del Usuario</h3>
            
            <div class="info-row">
                <span class="info-label">Nombre:</span>
                <span class="info-value">{{ $newUser->first_name }} {{ $newUser->last_name }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Identificación:</span>
                <span class="info-value">{{ $newUser->document_id }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Usuario:</span>
                <span class="info-value">{{ $newUser->user }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Rol:</span>
                <span class="info-value">{{ $newUser->role == 1 ? 'Administrador' : 'Usuario' }}</span>
            </div>
        </div>

        <div class="timestamp">
            <strong>📅 Fecha de Registro:</strong> {{ $newUser->created_at->format('d/m/Y H:i:s') }}
        </div>

        <div class="activation-section">
            <h3>🔐 Activar Usuario</h3>
            <p>Para habilitar el acceso de este usuario al sistema, haga clic en el siguiente enlace:</p>
            <div class="activation-link">
                <a href="{{ url('/user-activation/' . $newUser->id . '/' . $activationToken) }}" class="btn-activate-link">
                    ✅ Habilitar Usuario
                </a>
            </div>
            <p class="activation-note">
                <strong>Nota:</strong> Este enlace es único y específico para este usuario. 
                Al hacer clic, podrá activar o cancelar la activación del usuario.
            </p>
        </div>

        <p><strong>Nota:</strong> Este es un mensaje automático del sistema. No es necesario responder.</p>

        <div class="footer">
            <p>© {{ date('Y') }} Sistema de Prospectiva</p>
            <p>Este email fue enviado automáticamente cuando se registró un nuevo usuario.</p>
        </div>
    </div>
</body>
</html>