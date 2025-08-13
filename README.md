<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Proyecto Prospectiva

## Instalación y primeros pasos

### 1. Clona el repositorio
```bash
git clone <URL-del-repo>
cd <carpeta-del-proyecto>
```

### 2. Instala dependencias backend (Laravel)
```bash
composer install
```

### 3. Instala dependencias frontend (Vite + Vue)
```bash
npm install
```

### 4. Copia el archivo de entorno y configura tu base de datos
```bash
cp .env.example .env
```
Edita `.env` con tus credenciales de base de datos.

### 5. Genera la clave de la app
```bash
php artisan key:generate
```

### 6. Ejecuta las migraciones
```bash
php artisan migrate
```

### 7. Ejecuta los seeders para datos iniciales
```bash
php artisan db:seed
```

### 8. Verifica la instalación
```bash
php artisan check:migrations
php artisan check:seeders
```

## Solución de problemas comunes

### Error: Foreign key constraint fails en TestDataSeeder

Si encuentras el error `SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails`, sigue estos pasos:

#### Opción 1: Diagnóstico automático
```bash
php artisan diagnose:migration-issues
```

#### Opción 2: Solución específica del TestDataSeeder
```bash
php artisan fix:test-data-seeder
```

#### Opción 3: Instalación limpia (recomendado para nuevos desarrolladores)
```bash
php artisan migrate:fresh --seed
```

### Tabla de sectores económicos no se creó

Si la tabla `economic_sectors` no se creó durante la migración:

1. Verifica el estado de las migraciones:
```bash
php artisan migrate:status
```

2. Ejecuta las migraciones pendientes:
```bash
php artisan migrate
```

3. Ejecuta el seeder específico:
```bash
php artisan db:seed --class=EconomicSectorSeeder
```

### Orden correcto de migraciones y seeders

Es **CRÍTICO** que las migraciones y seeders se ejecuten en el orden correcto para evitar errores de foreign key constraints.

#### Orden de migraciones:
1. `status_users` (debe crearse antes que `users`)
2. `users` (depende de `status_users`)
3. `economic_sectors` (debe crearse antes de la foreign key)
4. Foreign key `users.economic_sector` (depende de `economic_sectors`)
5. Resto de tablas

#### Orden de seeders:
1. `StateUserSeeder` - Datos de status_users
2. `EconomicSectorSeeder` - Datos de economic_sectors  
3. `AdminUserSeeder` - Usuario administrador
4. `DatabaseSeeder` - Otros datos del sistema
5. `TestDataSeeder` - Datos de prueba (opcional)

#### Verificar orden correcto:
```bash
php artisan check:migration-seeder-order
```

#### Ejecutar en orden correcto automáticamente:
```bash
php artisan execute:correct-order        # Ejecutar migraciones y seeders
php artisan execute:correct-order --fresh # Ejecutar migrate:fresh y seeders
```

### Verificación completa del proyecto

Para verificar que todo esté funcionando correctamente:

```bash
php artisan setup:project
```

Este comando ejecutará automáticamente:
- Migraciones en el orden correcto
- Seeders en el orden correcto
- Verificaciones de integridad
- Configuraciones adicionales

## Comandos útiles

### Verificación y diagnóstico
```bash
php artisan check:migrations              # Verifica estado de migraciones
php artisan check:seeders                 # Verifica seeders
php artisan check:migration-seeder-order  # Verifica orden correcto de migraciones y seeders
php artisan diagnose:migration-issues     # Diagnóstico completo
php artisan fix:test-data-seeder          # Soluciona problemas del TestDataSeeder
php artisan execute:correct-order         # Ejecuta migraciones y seeders en orden correcto
php artisan setup:project                 # Configuración completa del proyecto
```

### Desarrollo
```bash
php artisan serve                     # Inicia servidor de desarrollo
npm run dev                          # Compila assets en modo desarrollo
npm run build                        # Compila assets para producción
```

### Base de datos
```bash
php artisan migrate                   # Ejecuta migraciones pendientes
php artisan migrate:fresh --seed      # Reinicia BD y ejecuta seeders
php artisan migrate:rollback          # Revierte última migración
php artisan db:seed                   # Ejecuta todos los seeders
php artisan db:seed --class=NombreSeeder # Ejecuta seeder específico
```
php artisan db:seed
```

### 8. Verifica la instalación
```bash
php artisan check:migrations
php artisan check:seeders
```

### 9. Inicia los servidores de desarrollo
En dos terminales diferentes:
```bash
npm run dev
```
y
```bash
php artisan serve
```

---

## Dependencias importantes

### PDF (backend y frontend)
- **Backend:**
  - barryvdh/laravel-dompdf
  - dompdf/dompdf
- **Frontend:**
  - jspdf
  - jspdf-autotable
  - html2canvas

### Gráficas
- chart.js
- chartjs-plugin-annotation
- vue-chartjs

Estas dependencias ya están incluidas en `composer.json` y `package.json`.

---

## Notas
- Si tienes problemas con permisos, ejecuta:
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```
- Si necesitas datos de prueba, revisa los seeders en `database/seeders` y ejecuta:
  ```bash
  php artisan db:seed
  ```
- Para producción, ejecuta:
  ```bash
  npm run build
  ```

---

¡Listo! El proyecto debería funcionar completamente, incluyendo PDF y gráficas, tras estos pasos.

---

## Troubleshooting

### Problemas comunes y soluciones

#### 1. Error "Table doesn't exist"
Si encuentras errores de tablas que no existen:
```bash
php artisan migrate:fresh --seed
```

#### 2. Sectores económicos no aparecen
Si los sectores económicos no se cargan:
```bash
php artisan db:seed --class=EconomicSectorSeeder
```

#### 3. Verificar estado de migraciones
Para verificar que todas las migraciones estén ejecutadas:
```bash
php artisan check:migrations
```

#### 4. Verificar seeders
Para verificar que todos los seeders funcionen:
```bash
php artisan check:seeders
```

#### 5. Limpiar cache
Si hay problemas de cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

#### 6. Problemas con dependencias
Si hay problemas con las dependencias:
```bash
composer install --no-dev
npm install --production
```

### Comandos útiles

- `php artisan check:migrations` - Verifica estado de migraciones
- `php artisan check:seeders` - Verifica estado de seeders
- `php artisan migrate:status` - Muestra estado de migraciones
- `php artisan db:seed` - Ejecuta todos los seeders
- `php artisan cache:clear` - Limpia cache de la aplicación
