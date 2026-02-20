# 🔒 Documentación de Seguridad y Validaciones

## Tabla de Contenidos
- [Resumen de Seguridad](#resumen-de-seguridad)
- [Validaciones de Entrada](#validaciones-de-entrada)
- [Rate Limiting](#rate-limiting)
- [Manejo de Errores](#manejo-de-errores)
- [Prevención de Ataques](#prevención-de-ataques)
- [Mejores Prácticas](#mejores-prácticas)

---

## Resumen de Seguridad

La API implementa múltiples capas de seguridad para proteger contra ataques comunes y abuso del servicio:

### ✅ Características de Seguridad Implementadas

| Característica | Estado | Descripción |
|----------------|--------|-------------|
| **Entropía Criptográfica** | ✅ | Usa `random_int()` de PHP 7+ |
| **Validación de Entrada** | ✅ | Form Requests con reglas estrictas |
| **Rate Limiting** | ✅ | Límites por IP para prevenir abuso |
| **Sanitización** | ✅ | Validación de caracteres seguros |
| **Límites de Recursos** | ✅ | Prevención de uso excesivo de CPU/memoria |
| **Manejo de Errores** | ✅ | Respuestas consistentes sin exponer detalles |
| **Logging** | ✅ | Registro de errores para auditoría |
| **CORS** | ⚠️ | Configurable (ver config) |

---

## Validaciones de Entrada

### 1. Límites de Longitud

#### Contraseñas Individuales

```php
// Límites estrictos definidos en PasswordService
LENGTH_MIN = 4 caracteres    // Mínimo absoluto
LENGTH_MAX = 128 caracteres  // Máximo absoluto
LENGTH_DEFAULT = 16          // Por defecto recomendado
LENGTH_RECOMMENDED_MIN = 12  // Mínimo para producción
```

**Validaciones:**
- ✅ `length >= 4` → Mínimo técnico
- ✅ `length <= 128` → Prevención de uso excesivo de memoria
- ⚠️ `length < 12` → Advertencia: no recomendado para producción

#### Generación Múltiple

```php
// Límites de cantidad
COUNT_MIN = 1 contraseña
COUNT_MAX = 100 contraseñas
COUNT_DEFAULT = 5

// Límite adicional: caracteres totales
MAX_TOTAL_CHARS = 10,000  // count × length
```

**Ejemplo:**
```json
// ❌ RECHAZADO: 100 × 128 = 12,800 caracteres
{
  "count": 100,
  "length": 128
}
// Error: "El total de caracteres a generar (12800) excede el límite de 10,000"

// ✅ ACEPTADO: 50 × 16 = 800 caracteres
{
  "count": 50,
  "length": 16
}
```

### 2. Validación de Caracteres (exclude)

**Límite:** Máximo 100 caracteres en el parámetro `exclude`

**Sanitización:**
```php
// Solo se permiten caracteres ASCII imprimibles (32-126)
// Previene inyección de caracteres de control
function isSafeString(string $str): bool {
    return preg_match('/^[\x20-\x7E]*$/', $str) === 1;
}
```

**Ejemplos:**
```json
// ✅ VÁLIDO
{
  "exclude": "aeiouAEIOU0123"
}

// ❌ INVÁLIDO: caracteres de control
{
  "exclude": "abc\x00\x01\x02"
}
// Error: "El parámetro exclude contiene caracteres no permitidos"

// ❌ INVÁLIDO: demasiado largo
{
  "exclude": "abcd...101 caracteres..."
}
// Error: "El parámetro exclude no puede exceder 100 caracteres"
```

### 3. Validación de Categorías

**Regla:** Al menos UNA categoría debe estar activa

```json
// ❌ INVÁLIDO
{
  "upper": false,
  "lower": false,
  "digits": false,
  "symbols": false
}
// Error: "Debe activarse al menos una categoría"

// ✅ VÁLIDO
{
  "upper": true,
  "lower": false,
  "digits": false,
  "symbols": false
}
```

### 4. Validación de require_each

**Regla:** `length >= número de categorías activas` cuando `require_each: true`

```json
// ❌ INVÁLIDO
{
  "length": 3,
  "upper": true,
  "lower": true,
  "digits": true,
  "symbols": true,
  "require_each": true
}
// Error: "La longitud debe ser al menos 4 cuando 'require_each' está activo"

// ✅ VÁLIDO
{
  "length": 4,  // Suficiente para 4 categorías
  "upper": true,
  "lower": true,
  "digits": true,
  "symbols": true,
  "require_each": true
}
```

---

## Rate Limiting

### Configuración

La API implementa rate limiting basado en IP para prevenir abuso.

#### Límites por Minuto

| Tipo | Límite | Descripción |
|------|--------|-------------|
| **Requests totales** | 60/minuto/IP | Máximo de peticiones HTTP |
| **Contraseñas generadas** | 500/minuto/IP | Total de contraseñas (suma de todas las peticiones) |

### Implementación

```php
// Middleware: RateLimitPasswordGeneration
class RateLimitPasswordGeneration {
    const RATE_LIMIT_PER_MINUTE = 60;
    const PASSWORD_GENERATION_LIMIT = 500;
}
```

### Respuesta de Rate Limit Excedido

**Código HTTP:** `429 Too Many Requests`

```json
{
  "success": false,
  "error": "Rate limit exceeded",
  "message": "Demasiadas peticiones. Límite: 60 por minuto.",
  "retry_after": 60
}
```

### Ejemplos de Escenarios

#### Escenario 1: Requests Repetidos
```
IP: 192.168.1.100
Minuto: 2026-02-20 14:30

Request 1-60:  ✅ Permitido
Request 61:    ❌ BLOQUEADO (429)
```

#### Escenario 2: Generación Masiva
```
IP: 192.168.1.100
Minuto: 2026-02-20 14:30

POST /generate-multiple {"count": 100, "length": 16}  x5
= 5 requests, 500 contraseñas ✅

POST /generate-multiple {"count": 1}
= BLOQUEADO: 501 contraseñas excede límite de 500 ❌
```

### Bypass de Rate Limit

El endpoint de configuración **NO** tiene rate limiting:
```bash
# Sin límite
GET /api/password/config
```

---

## Manejo de Errores

### Estructura de Respuesta Consistente

Todas las respuestas de error siguen el mismo formato:

```json
{
  "success": false,
  "error": "Mensaje de error legible",
  "details": {}  // Opcional, solo en errores específicos
}
```

### Códigos HTTP Estandarizados

| Código | Significado | Cuándo se usa |
|--------|-------------|---------------|
| **200** | OK | Operación exitosa |
| **400** | Bad Request | Parámetros inválidos o fuera de rango |
| **404** | Not Found | Endpoint no existe |
| **405** | Method Not Allowed | Método HTTP incorrecto |
| **422** | Unprocessable Entity | Error de validación (Laravel) |
| **429** | Too Many Requests | Rate limit excedido |
| **500** | Internal Server Error | Error del servidor |

### Ejemplos de Respuestas de Error

#### Error de Validación (400/422)

```json
// Parámetro fuera de rango
{
  "success": false,
  "error": "La longitud debe ser >= 4"
}

// Validación Laravel (422)
{
  "success": false,
  "error": "Error de validación",
  "validation_errors": {
    "length": ["La longitud debe ser un número entero"],
    "exclude": ["El parámetro exclude no puede exceder 100 caracteres"]
  }
}
```

#### Error de Lógica (400)

```json
{
  "success": false,
  "error": "Debe activarse al menos una categoría (upper/lower/digits/symbols)"
}
```

#### Error del Servidor (500)

```json
// Producción (app.debug = false)
{
  "success": false,
  "error": "Error interno del servidor"
}

// Desarrollo (app.debug = true)
{
  "success": false,
  "error": "Error interno del servidor",
  "debug": {
    "message": "Division by zero",
    "file": "/var/www/html/app/Services/PasswordService.php",
    "line": 245,
    "trace": [...]
  }
}
```

### Logging de Errores

Todos los errores 500 se registran automáticamente:

```php
Log::error('Error al generar contraseña: ' . $e->getMessage(), [
    'trace' => $e->getTraceAsString()
]);
```

**Ubicación de logs:** `storage/logs/laravel.log`

---

## Prevención de Ataques

### 1. DoS (Denial of Service)

#### Protecciones Implementadas

| Protección | Implementación |
|------------|----------------|
| **Rate Limiting** | Máximo 60 requests/minuto/IP |
| **Límite de contraseñas** | Máximo 500 contraseñas/minuto/IP |
| **Límite de caracteres totales** | Máximo 10,000 caracteres por request |
| **Límite de longitud** | Máximo 128 caracteres por contraseña |
| **Límite de count** | Máximo 100 contraseñas por request |

#### Ejemplos de Ataques Bloqueados

```bash
# Ataque 1: Request bombing
for i in {1..100}; do
  curl -X POST /api/password/generate
done
# → Bloqueado después del request 60

# Ataque 2: Generación masiva
curl -X POST /api/password/generate-multiple \
  -d '{"count": 1000, "length": 128}'
# → Bloqueado: count > 100

# Ataque 3: Consumo excesivo de memoria
curl -X POST /api/password/generate-multiple \
  -d '{"count": 100, "length": 128}'
# → Bloqueado: 12,800 caracteres > 10,000
```

### 2. Inyección de Código

#### Protecciones

- ✅ **Validación de tipos:** Form Requests validan tipos de datos
- ✅ **Sanitización:** Solo caracteres ASCII imprimibles en `exclude`
- ✅ **Límites de longitud:** Previene buffer overflow
- ✅ **Sin ejecución de código:** No se evalúa input del usuario

```php
// Validación estricta
private function isSafeString(string $str): bool {
    // Solo ASCII imprimible (32-126)
    return preg_match('/^[\x20-\x7E]*$/', $str) === 1;
}
```

#### Ejemplos de Ataques Bloqueados

```json
// Intento de inyección con caracteres de control
{
  "exclude": "abc\x00<script>alert('xss')</script>"
}
// → Bloqueado: caracteres no permitidos

// Intento de inyección SQL visual
{
  "exclude": "'; DROP TABLE users;--"
}
// → Permitido (son caracteres ASCII) pero sin efecto
//    (no se ejecuta, solo se excluye de la generación)
```

### 3. Information Disclosure

#### Protecciones

- ✅ **Sin detalles en producción:** `app.debug = false` oculta stack traces
- ✅ **Mensajes genéricos:** Errores 500 no exponen detalles internos
- ✅ **Logging seguro:** Logs no contienen contraseñas generadas
- ✅ **Sin almacenamiento:** Contraseñas nunca se guardan

```php
// Producción: mensaje genérico
{
  "error": "Error interno del servidor"
}

// Desarrollo: detalles completos
{
  "error": "Error interno del servidor",
  "debug": { /* detalles */ }
}
```

### 4. Timing Attacks

#### Mitigación Parcial

La API no es vulnerable a timing attacks críticos porque:
- ✅ No valida contraseñas existentes (no hay comparación)
- ✅ La generación siempre toma tiempo variable (random_int)
- ⚠️ La validación de fortaleza es determinística (no crítico)

---

## Mejores Prácticas

### Para Desarrolladores de la API

1. **Nunca registrar contraseñas generadas**
```php
// ❌ MAL
Log::info('Password generada: ' . $password);

// ✅ BIEN
Log::info('Password generada con longitud: ' . strlen($password));
```

2. **Siempre validar en el servidor**
```php
// No confiar solo en validación del cliente
// Form Requests validan de nuevo en el servidor
```

3. **Actualizar límites según carga**
```php
// Ajustar en .env según necesidad
PASSWORD_API_RATE_LIMIT=60
```

4. **Monitorear logs regularmente**
```bash
tail -f storage/logs/laravel.log
```

### Para Usuarios de la API

1. **Usar longitudes recomendadas**
```json
// ✅ RECOMENDADO
{
  "length": 16  // o más
}

// ⚠️ NO RECOMENDADO (aunque permitido)
{
  "length": 4
}
```

2. **Respetar rate limits**
```javascript
// Implementar retry con exponential backoff
async function generatePasswordWithRetry() {
  try {
    return await generatePassword();
  } catch (error) {
    if (error.status === 429) {
      const retryAfter = error.data.retry_after;
      await sleep(retryAfter * 1000);
      return generatePassword();
    }
    throw error;
  }
}
```

3. **No almacenar contraseñas en logs**
```javascript
// ❌ MAL
console.log('Password:', response.password);

// ✅ BIEN
console.log('Password length:', response.length);
```

4. **Usar HTTPS en producción**
```bash
# ✅ BIEN
https://api.example.com/api/password/generate

# ❌ MAL (HTTP sin cifrar)
http://api.example.com/api/password/generate
```

---

## Configuración de Seguridad

### Variables de Entorno

```env
# .env

# App
APP_DEBUG=false  # SIEMPRE false en producción
APP_ENV=production

# Rate Limiting
PASSWORD_API_RATE_LIMITING=true
PASSWORD_API_RATE_LIMIT=60

# Logs
LOG_CHANNEL=stack
LOG_LEVEL=error  # error, warning, info, debug
```

### Configuración de CORS

Si la API será consumida desde navegadores web:

```php
// config/cors.php
'paths' => ['api/*'],
'allowed_origins' => ['https://tudominio.com'],
'allowed_methods' => ['POST', 'GET'],
```

---

## Checklist de Seguridad

### Antes de Producción

- [ ] `APP_DEBUG=false` en `.env`
- [ ] `APP_ENV=production` en `.env`
- [ ] HTTPS configurado y funcionando
- [ ] Rate limiting activado
- [ ] Logs configurados y monitoreados
- [ ] Permisos de archivos correctos (`storage/` escribible)
- [ ] CORS configurado correctamente
- [ ] Firewall configurado (solo puertos 80/443)
- [ ] Backup de configuración
- [ ] Pruebas de carga realizadas

### Monitoreo Continuo

- [ ] Revisar logs diariamente
- [ ] Monitorear uso de CPU/memoria
- [ ] Alertas de rate limit excedido
- [ ] Auditoría de accesos
- [ ] Actualización de dependencias

---

## Respuesta a Incidentes

### Si detectas abuso:

1. **Identificar la IP**
```bash
grep "Rate limit exceeded" storage/logs/laravel.log | tail -20
```

2. **Bloquear a nivel de firewall**
```bash
# Linux: iptables
sudo iptables -A INPUT -s 192.168.1.100 -j DROP

# O usar fail2ban
```

3. **Revisar logs**
```bash
grep "192.168.1.100" storage/logs/laravel.log
```

### Si hay error 500 persistente:

1. **Ver logs**
```bash
tail -100 storage/logs/laravel.log
```

2. **Habilitar debug temporalmente**
```env
APP_DEBUG=true  # Solo temporalmente, en entorno controlado
```

3. **Revertir cambios recientes**
```bash
git log --oneline -10
git revert <commit>
```

---

**Versión:** 1.0.0  
**Última actualización:** Febrero 2026  
**Nivel de seguridad:** 🔒🔒🔒 Alto
