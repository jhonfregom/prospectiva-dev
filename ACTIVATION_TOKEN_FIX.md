# Solución para Error de activation_token

## 🔍 Problema Identificado

Los usuarios que descargan el proyecto desde Git experimentan errores de `activation_token` porque:

1. **Las migraciones de activación están vacías en el repositorio Git**
2. **Los campos `activation_token` y `activation_token_expires_at` no se crean en la base de datos**
3. **El código intenta usar campos que no existen**

## 🛠️ Soluciones Disponibles

### Opción 1: Comando Automático (Recomendado)

Ejecuta el comando que soluciona automáticamente el problema:

```bash
php artisan fix:activation-token-issue
```

Este comando:
- ✅ Detecta si faltan los campos de activación
- ✅ Crea las migraciones necesarias
- ✅ Ejecuta las migraciones automáticamente
- ✅ Verifica que todo funcione correctamente

### Opción 2: Solución Manual

Si prefieres hacerlo manualmente:

1. **Crear la migración:**
```bash
php artisan make:migration add_activation_fields_to_users_table --table=users
```

2. **Editar el archivo de migración** (`database/migrations/YYYY_MM_DD_HHMMSS_add_activation_fields_to_users_table.php`):

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('activation_token', 255)->nullable()->after('password');
        $table->timestamp('activation_token_expires_at')->nullable()->after('activation_token');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['activation_token', 'activation_token_expires_at']);
    });
}
```

3. **Ejecutar la migración:**
```bash
php artisan migrate
```

### Opción 3: Verificación del Sistema

Para verificar que todo funciona correctamente:

```bash
# Verificar estado de migraciones
php artisan migrate:status

# Verificar estructura de la base de datos
php artisan check:migrations

# Probar el sistema de activación
php artisan test:complete-registration test@example.com
```

## 🎯 Campos Requeridos

El sistema de activación necesita estos campos en la tabla `users`:

- `activation_token` (VARCHAR 255, nullable)
- `activation_token_expires_at` (TIMESTAMP, nullable)

## 🔍 Diagnóstico

Si experimentas errores de `activation_token`, verifica:

1. **¿Existen los campos en la base de datos?**
```bash
php artisan tinker --execute="echo 'activation_token: ' . (\Schema::hasColumn('users', 'activation_token') ? 'EXISTE' : 'NO EXISTE');"
```

2. **¿Están las migraciones ejecutadas?**
```bash
php artisan migrate:status
```

3. **¿Hay errores en los logs?**
```bash
tail -f storage/logs/laravel.log
```

## 🚨 Errores Comunes

### Error: "Column 'activation_token' doesn't exist"
**Causa:** Los campos no existen en la base de datos
**Solución:** Ejecutar `php artisan fix:activation-token-issue`

### Error: "SQLSTATE[HY000] [2002] No se puede establecer una conexión"
**Causa:** Problema de conexión a la base de datos
**Solución:** Verificar configuración en `.env` y ejecutar `php artisan migrate:status`

### Error: "Token de activación inválido"
**Causa:** El token no coincide o ha expirado
**Solución:** Generar un nuevo token o verificar la configuración del sistema

## 📞 Soporte

Si el problema persiste después de aplicar estas soluciones:

1. Ejecuta `php artisan fix:activation-token-issue`
2. Verifica los logs en `storage/logs/laravel.log`
3. Ejecuta `php artisan check:migrations` para verificar el estado del sistema

## ✅ Verificación Final

Después de aplicar la solución, verifica que todo funciona:

```bash
# 1. Verificar campos
php artisan tinker --execute="echo 'Campos de activación: ' . (\Schema::hasColumn('users', 'activation_token') ? 'OK' : 'FALTA');"

# 2. Probar registro completo
php artisan test:complete-registration test@example.com

# 3. Verificar activación
php artisan test:activation-url [USER_ID]
```

Si todos los comandos muestran resultados positivos, el problema está solucionado.
