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
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #667eea;
            margin: 0;
            font-size: 24px;
        }
        .user-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .user-info h3 {
            margin-top: 0;
            color: #667eea;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .timestamp {
            background-color: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            color: #0066cc;
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

        <p><strong>Nota:</strong> Este es un mensaje automático del sistema. No es necesario responder.</p>

        <div class="footer">
            <p>© {{ date('Y') }} Sistema de Prospectiva</p>
            <p>Este email fue enviado automáticamente cuando se registró un nuevo usuario.</p>
        </div>
    </div>
</body>
</html> 