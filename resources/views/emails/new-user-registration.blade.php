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
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        .user-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .user-info h3 {
            margin-top: 0;
            color: #007bff;
            display: flex;
            align-items: center;
        }
        .user-info h3::before {
            content: "📋";
            margin-right: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
        }
        .timestamp {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            color: #495057;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .timestamp::before {
            content: "📅";
            margin-right: 10px;
        }
        .activation-section {
            background-color: #d4edda;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        .activation-section h3 {
            margin-top: 0;
            color: #155724;
            display: flex;
            align-items: center;
        }
        .activation-section h3::before {
            content: "🔐";
            margin-right: 10px;
        }
        .activation-link {
            text-align: center;
            margin: 20px 0;
        }
        .btn-activate-link {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn-activate-link:hover {
            background-color: #218838;
        }
        .btn-activate-link::before {
            content: "✅";
            margin-right: 10px;
        }
        .activation-note {
            font-size: 14px;
            color: #6c757d;
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="header-icon">🚀</div>
            <div>
                <h1>Nuevo Usuario Registrado</h1>
                <p>Sistema de Prospectiva</p>
            </div>
        </div>

        <p>Hola Administrador,</p>
        
        <p>Se ha registrado un nuevo usuario en el sistema de Prospectiva. A continuación los detalles:</p>

        <div class="user-info">
            <h3>Información del Usuario</h3>
            
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
            <strong>Fecha de Registro:</strong> {{ $newUser->created_at->format('d/m/Y H:i:s') }}
        </div>

        <div class="activation-section">
            <h3>Activar Usuario</h3>
            <p>Para habilitar el acceso de este usuario al sistema, haga clic en el siguiente enlace:</p>
            <div class="activation-link">
                <a href="{{ $activationUrl }}" class="btn-activate-link">
                    Habilitar Usuario
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
