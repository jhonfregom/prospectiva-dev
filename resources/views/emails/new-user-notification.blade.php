<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nuevo usuario registrado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3273dc;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #e9ecef;
        }
        .user-info {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #3273dc;
        }
        .btn {
            display: inline-block;
            background-color: #3273dc;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
        }
        .btn:hover {
            background-color: #2366d1;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nuevo Usuario Registrado</h1>
    </div>
    
    <div class="content">
        <p>Hola administrador,</p>
        
        <p>Se ha registrado un nuevo usuario en el sistema que requiere activación.</p>
        
        <div class="user-info">
            <h3>Datos del usuario:</h3>
            <p><strong>Nombre:</strong> {{ $newUser->first_name }} {{ $newUser->last_name }}</p>
            <p><strong>Correo:</strong> {{ $newUser->user }}</p>
            <p><strong>Documento:</strong> {{ $newUser->document_id }}</p>
            <p><strong>Tipo de registro:</strong> {{ $newUser->registration_type === 'natural' ? 'Persona Natural' : 'Empresa' }}</p>
            @if($newUser->registration_type === 'company' && $newUser->economic_sector)
                <p><strong>Sector económico:</strong> {{ $newUser->economic_sector }}</p>
            @endif
        </div>
        
        <p>Para activar este usuario, haz clic en el siguiente botón:</p>
        
        <a href="{{ $activationUrl }}" class="btn">Activar Usuario</a>
        
        <p><small>Si el botón no funciona, copia y pega este enlace en tu navegador:</small></p>
        <p><small>{{ $activationUrl }}</small></p>
    </div>
    
    <div class="footer">
        <p>Este es un correo automático del sistema Prospectiva.</p>
        <p>No respondas a este correo.</p>
    </div>
</body>
</html> 