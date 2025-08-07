# Configuración de Correo Electrónico para Restablecimiento de Contraseñas

## Configuración Gmail SMTP

Para que el sistema de restablecimiento de contraseñas funcione correctamente, necesitas configurar las siguientes variables en tu archivo `.env`:

### 1. Configuración SMTP de Gmail

```env
# Configuración de correo
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-correo@gmail.com
MAIL_PASSWORD=tu-contraseña-de-aplicacion
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-correo@gmail.com
MAIL_FROM_NAME="Sistema Prospectiva"
```

### 2. Configuración de la aplicación

```env
APP_NAME="Sistema Prospectiva"
APP_URL=http://localhost:8000
```

## Pasos para configurar Gmail SMTP

### Paso 1: Habilitar verificación en dos pasos
1. Ve a tu cuenta de Google
2. Activa la verificación en dos pasos si no está activada

### Paso 2: Generar contraseña de aplicación
1. Ve a la configuración de seguridad de Google
2. Busca "Contraseñas de aplicación"
3. Genera una nueva contraseña para "Correo"
4. Usa esta contraseña en `MAIL_PASSWORD`

### Paso 3: Verificar configuración
Ejecuta el comando de prueba:
```bash
php artisan test:password-reset-system tu-correo@ejemplo.com nueva-contraseña
```

## Configuración alternativa para desarrollo

Si estás en desarrollo y no quieres configurar SMTP real, puedes usar el driver de log:

```env
MAIL_MAILER=log
```

Esto guardará los correos en `storage/logs/laravel.log` para que puedas verificar que el sistema funciona.

## Verificación del sistema

### 1. Verificar que las migraciones estén ejecutadas
```bash
php artisan migrate
```

### 2. Verificar que los campos de restablecimiento existan
```bash
php artisan tinker
>>> Schema::hasColumn('users', 'password_reset_token')
>>> Schema::hasColumn('users', 'password_reset_expires_at')
```

### 3. Probar el sistema completo
```bash
php artisan test:password-reset-system admin@ejemplo.com nueva123456
```

## Flujo del sistema

1. **Usuario solicita restablecimiento**: Va a `/login/restore-password`
2. **Ingresa su correo**: Se valida que exista en la base de datos
3. **Se genera token**: Token único de 64 caracteres válido por 1 hora
4. **Se envía correo**: Con enlace para restablecer contraseña
5. **Usuario hace clic**: En el enlace del correo
6. **Ingresa nueva contraseña**: En el formulario de restablecimiento
7. **Se actualiza contraseña**: Y se limpia el token

## URLs del sistema

- **Formulario de solicitud**: `/login/restore-password`
- **Envío de enlace**: `POST /password/email`
- **Formulario de restablecimiento**: `/password/reset/{token}?email={email}`
- **Procesamiento**: `POST /password/reset`

## Solución de problemas

### Error: "No existe una cuenta con este correo"
- Verificar que el correo esté registrado en la tabla `users`
- Verificar que el campo sea `user` y no `email`

### Error: "Error al enviar correo"
- Verificar configuración SMTP en `.env`
- Verificar que la contraseña de aplicación sea correcta
- Verificar que el puerto 587 esté abierto

### Error: "Token no válido"
- Verificar que el token no haya expirado (1 hora)
- Verificar que el token coincida con el usuario

### Error: "Las contraseñas no coinciden"
- Verificar que la confirmación de contraseña sea idéntica
- Verificar que la contraseña tenga al menos 8 caracteres
