# 🔑 Configuración de Credenciales de Google Custom Search API

## 🎯 Estado Actual
✅ **Implementación funcionando**: El código detecta Google y usa la API  
❌ **Credenciales pendientes**: Necesitas configurar las credenciales reales  
❌ **Error 400**: La API rechaza las credenciales de placeholder  

## 📋 Pasos Detallados

### Paso 1: Crear Proyecto en Google Cloud Console

1. **Ir a Google Cloud Console**
   - Ve a: https://console.cloud.google.com/
   - Inicia sesión con tu cuenta de Google

2. **Crear nuevo proyecto**
   - Haz clic en el selector de proyectos (arriba a la izquierda)
   - Haz clic en "Nuevo proyecto"
   - Nombre: `Prospectiva Browser` (o el que prefieras)
   - Haz clic en "Crear"

3. **Seleccionar el proyecto**
   - Asegúrate de que el proyecto esté seleccionado

### Paso 2: Habilitar Custom Search API

1. **Ir a APIs y Servicios**
   - En el menú lateral, ve a "APIs y servicios" > "Biblioteca"

2. **Buscar la API**
   - En la barra de búsqueda, escribe: `Custom Search API`
   - Haz clic en "Custom Search API"

3. **Habilitar la API**
   - Haz clic en "Habilitar"
   - Espera a que se complete

### Paso 3: Crear API Key

1. **Ir a Credenciales**
   - En el menú lateral, ve a "APIs y servicios" > "Credenciales"

2. **Crear credencial**
   - Haz clic en "Crear credenciales" > "Clave de API"
   - Se generará una API Key

3. **Copiar la API Key**
   - La API Key comienza con `AIzaSyC...`
   - Tiene aproximadamente 39 caracteres
   - **¡Cópiala ahora!**

### Paso 4: Crear Custom Search Engine

1. **Ir a Programmable Search Engine**
   - Ve a: https://programmablesearchengine.google.com/
   - Inicia sesión con la misma cuenta de Google

2. **Crear motor de búsqueda**
   - Haz clic en "Create a search engine"

3. **Configurar el motor**
   - **Sites to search**: Deja vacío (para buscar en toda la web)
   - **Name**: `Prospectiva Browser Search`
   - Haz clic en "Create"

4. **Obtener Search Engine ID**
   - En la configuración del motor, busca "Search engine ID"
   - Tiene formato: `123456789:abcdefghijk`
   - **¡Cópialo ahora!**

### Paso 5: Actualizar archivo .env

1. **Abrir el archivo .env**
   ```bash
   notepad .env
   ```

2. **Reemplazar las líneas**
   ```env
   # Cambiar esto:
   GOOGLE_SEARCH_API_KEY=tu_api_key_aqui
   GOOGLE_SEARCH_ENGINE_ID=tu_search_engine_id_aqui
   
   # Por esto (con tus credenciales reales):
   GOOGLE_SEARCH_API_KEY=AIzaSyC...tu_api_key_real_aqui
   GOOGLE_SEARCH_ENGINE_ID=123456789:abcdefghijk
   ```

3. **Guardar el archivo**

### Paso 6: Limpiar caché

```bash
php artisan config:clear
```

### Paso 7: Probar

1. **Búsqueda directa**
   - Ve a: `http://prospectiva.com/browser/search-api?q=test`

2. **Desde la aplicación**
   - Navega a tu app
   - Haz clic en "Google" en los enlaces rápidos
   - O escribe cualquier término de búsqueda

## 🔍 Verificación de Credenciales

### Formato de API Key
- ✅ Comienza con `AIzaSyC`
- ✅ Tiene aproximadamente 39 caracteres
- ✅ Solo contiene letras y números

### Formato de Search Engine ID
- ✅ Tiene formato: `números:letras`
- ✅ Ejemplo: `123456789:abcdefghijk`
- ✅ No tiene espacios

## 🚨 Solución de Problemas

### Error 400 - Bad Request
- ✅ Verifica que las credenciales estén correctas
- ✅ Asegúrate de que la API esté habilitada
- ✅ Comprueba que no haya espacios extra

### Error 403 - Forbidden
- ✅ Verifica que la API Key no tenga restricciones
- ✅ Asegúrate de que el proyecto esté activo

### Error 429 - Too Many Requests
- ✅ Has excedido el límite gratuito (100 búsquedas/día)
- ✅ Considera actualizar a un plan de pago

## 💰 Costos

- **Gratuito**: 100 búsquedas por día
- **Pago**: $5 USD por 1000 búsquedas adicionales

## 🎉 Resultado Esperado

Una vez configuradas las credenciales:
- ✅ No más errores 400
- ✅ Búsquedas funcionando
- ✅ Resultados reales de Google
- ✅ Navegación fluida desde resultados

---

**¡Con estas credenciales configuradas, tendrás un buscador web completamente funcional!** 🎉 