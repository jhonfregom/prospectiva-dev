<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecimiento de Contraseña</title>
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
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .title {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #7f8c8d;
            font-size: 16px;
        }
        .content {
            margin-bottom: 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .reset-button {
            display: inline-block;
            background-color: #3498db;
            color: #ffffff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .reset-button:hover {
            background-color: #2980b9;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 14px;
        }
        .link {
            color: #3498db;
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">🔐 Restablecimiento de Contraseña</h1>
            <p class="subtitle">Foresight tool</p>
        </div>

        <div class="content">
            <div class="greeting">
                Hola{{ $userName ? ' ' . $userName : '' }},
            </div>

            <div class="message">
                Has solicitado restablecer tu contraseña en el Sistema Foresight tool. 
                Para continuar con el proceso, haz clic en el botón de abajo.
            </div>

            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button">
                    🔄 Restablecer Contraseña
                </a>
            </div>

            <div class="warning">
                <strong>⚠️ Importante:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <li>Este enlace es válido por 1 hora</li>
                    <li>Si no solicitaste este restablecimiento, puedes ignorar este correo</li>
                    <li>Tu contraseña actual permanecerá sin cambios hasta que completes el proceso</li>
                </ul>
            </div>

            <div class="message">
                Si el botón no funciona, puedes copiar y pegar el siguiente enlace en tu navegador:
                <br>
                <a href="{{ $resetUrl }}" class="link">{{ $resetUrl }}</a>
            </div>
        </div>

        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            <p>Si tienes problemas, contacta al administrador del sistema.</p>
        </div>
    </div>
</body>
</html>





