# 🔍 Configuración de Google Custom Search API

## 📋 Resumen

Este documento explica cómo configurar la **Google Custom Search API** para reemplazar el sistema de proxy que intentaba embeber Google directamente, evitando así los errores de CORS.

## 🎯 ¿Por qué usar la API?

- ✅ **Sin errores de CORS**: La API es oficial y permite peticiones desde cualquier dominio
- ✅ **Resultados reales**: Obtienes los mismos resultados que Google Search
- ✅ **Control total**: Puedes personalizar la presentación de resultados
- ✅ **Límites generosos**: 100 búsquedas gratuitas por día
- ✅ **Sin restricciones**: No hay problemas de X-Frame-Options o políticas de seguridad

## 🚀 Configuración Paso a Paso

### 1. Crear un proyecto en Google Cloud Console

1. Ve a [Google Cloud Console](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita la **Custom Search API**:
   - Ve a "APIs & Services" > "Library"
   - Busca "Custom Search API"
   - Haz clic en "Enable"

### 2. Obtener la API Key

1. Ve a "APIs & Services" > "Credentials"
2. Haz clic en "Create Credentials" > "API Key"
3. Copia la API Key generada
4. (Opcional) Restringe la API Key para mayor seguridad

### 3. Crear un Custom Search Engine

1. Ve a [Google Programmable Search Engine](https://programmablesearchengine.google.com/)
2. Haz clic en "Create a search engine"
3. En "Sites to search", puedes:
   - Dejar vacío para buscar en toda la web
   - O especificar sitios específicos
4. Haz clic en "Create"
5. En la configuración del motor de búsqueda, copia el **Search Engine ID**

### 4. Configurar las variables de entorno

Agrega estas líneas a tu archivo `.env`:

```env
GOOGLE_SEARCH_API_KEY=tu_api_key_aqui
GOOGLE_SEARCH_ENGINE_ID=tu_search_engine_id_aqui
```

### 5. Verificar la configuración

Puedes probar la API visitando:
```
http://tu-dominio.com/browser/search-api?q=test
```

## 💰 Costos

- **Gratuito**: 100 búsquedas por día
- **Pago**: $5 USD por 1000 búsquedas adicionales

## 🔧 Personalización

### Modificar el número de resultados

En `app/Http/Controllers/BrowserProxyController.php`, línea ~50:

```php
'num' => 10, // Cambiar a 5, 20, etc.
```

### Agregar filtros de búsqueda

Puedes agregar parámetros adicionales:

```php
$params = [
    'key' => $apiKey,
    'cx' => $searchEngineId,
    'q' => $query,
    'num' => 10,
    'safe' => 'active',
    'dateRestrict' => 'm1', // Último mes
    'sort' => 'date', // Ordenar por fecha
];
```

### Personalizar el diseño

Modifica el método `buildSearchResultsHtml()` para cambiar el estilo de los resultados.

## 🐛 Solución de Problemas

### Error: "Google Search API not configured"

Verifica que las variables de entorno estén configuradas correctamente:
```bash
php artisan config:clear
```

### Error: "API key not valid"

1. Verifica que la API Key sea correcta
2. Asegúrate de que la Custom Search API esté habilitada
3. Verifica que no haya restricciones en la API Key

### Error: "Search engine ID not valid"

1. Verifica que el Search Engine ID sea correcto
2. Asegúrate de que el motor de búsqueda esté activo

### Límite de cuota excedido

- Verifica tu uso en [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
- Considera actualizar a un plan de pago si necesitas más búsquedas

## 🔄 Migración desde el sistema anterior

El nuevo sistema es completamente compatible con el anterior. Los cambios incluyen:

1. **Nuevo endpoint**: `/browser/search-api` en lugar de `/browser/search-instant`
2. **Resultados reales**: En lugar de intentar embeber Google
3. **Sin CORS**: No más errores de políticas de seguridad

## 📱 Uso en el frontend

El componente Vue ya está configurado para usar la nueva API. Las búsquedas ahora:

1. Se envían a `/browser/search-api`
2. Reciben resultados reales de Google
3. Se muestran en una interfaz personalizada
4. Permiten navegación directa a los resultados

## 🎨 Personalización de la interfaz

Los resultados se muestran con un diseño limpio y moderno que incluye:

- Título del resultado
- URL del sitio
- Snippet de descripción
- Formulario de búsqueda integrado
- Navegación con postMessage al componente padre

## 🔒 Seguridad

- La API Key se almacena en variables de entorno
- Las búsquedas pasan por tu servidor Laravel
- Puedes agregar autenticación adicional si es necesario
- Los resultados se filtran automáticamente (safe search)

---

¡Con esta configuración, tendrás un buscador web completamente funcional sin errores de CORS! 🎉 