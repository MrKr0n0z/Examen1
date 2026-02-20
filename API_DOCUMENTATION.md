# API de Generación y Validación de Contraseñas

## 📋 Índice
- [Información General](#información-general)
- [Endpoints Principales (Casos de Uso)](#endpoints-principales-casos-de-uso)
  - [1. GET /api/password - Generar Contraseña](#1-get-apipassword---generar-contraseña)
  - [2. POST /api/passwords - Generar Múltiples](#2-post-apipasswords---generar-múltiples)
  - [3. POST /api/password/validate - Validar con Requisitos](#3-post-apipasswordvalidate---validar-con-requisitos)
  - [4. GET /api/password/config - Obtener Configuración](#4-get-apipasswordconfig---obtener-configuración)
- [Endpoints Heredados (Compatibilidad)](#endpoints-heredados-compatibilidad)
  - [POST /api/password/generate](#post-apipasswordgenerate)
  - [POST /api/password/generate-multiple](#post-apipasswordgenerate-multiple)
  - [POST /api/password/validate-strength](#post-apipasswordvalidate-strength)
- [Rate Limiting](#rate-limiting)
- [Códigos de Estado HTTP](#códigos-de-estado-http)
- [Ejemplos de Uso](#ejemplos-de-uso)

---

## Información General

**URL Base:** `http://password.test` 

**Formato de Respuesta:** JSON

**Content-Type:** `application/json`

**Seguridad:** Las contraseñas se generan usando `random_int()` para entropía criptográfica y Fisher-Yates shuffle para mezclar caracteres de forma segura.

---

## Endpoints Principales (Casos de Uso)

Estos son los endpoints recomendados para usar la API con la convención moderna de nombres de parámetros.

### 1. GET /api/password - Generar Contraseña

Genera una contraseña segura usando parámetros de query string.

#### Especificación

- **Método HTTP:** `GET`
- **Ruta:** `/api/password`
- **Parámetros:** Query string

#### Parámetros de Query

| Parámetro | Tipo | Requerido | Default | Rango | Descripción |
|-----------|------|-----------|---------|-------|-------------|
| `length` | integer | No | 16 | 4-128 | Longitud de la contraseña |
| `includeUppercase` | boolean | No | true | - | Incluir letras mayúsculas [A-Z] |
| `includeLowercase` | boolean | No | true | - | Incluir letras minúsculas [a-z] |
| `includeNumbers` | boolean | No | true | - | Incluir números [0-9] |
| `includeSymbols` | boolean | No | false | - | Incluir símbolos especiales |
| `excludeAmbiguous` | boolean | No | true | - | Excluir caracteres ambiguos (I, l, 1, O, 0) |
| `exclude` | string | No | "" | max 100 | Caracteres específicos a excluir |
| `requireEach` | boolean | No | true | - | Garantizar al menos 1 carácter de cada tipo |

#### Códigos de Respuesta HTTP

| Código | Descripción |
|--------|-------------|
| `200` | Contraseña generada exitosamente |
| `400` | Parámetros inválidos (ej: length > 128) |
| `429` | Rate limit excedido |
| `500` | Error interno del servidor |

#### Estructura de Respuesta JSON

**Éxito (200):**
```json
{
  "success": true,
  "password": "UpcRYMuXVzW4",
  "length": 12,
  "options": {
    "upper": true,
    "lower": true,
    "digits": true,
    "symbols": false,
    "avoid_ambiguous": true,
    "exclude": "",
    "require_each": true
  }
}
```

**Error (400):**
```json
{
  "success": false,
  "error": "La longitud debe ser <= 128"
}
```

#### Ejemplo de Solicitud

```bash
# Generar contraseña de 12 caracteres con mayúsculas, minúsculas y números
curl "http://localhost:8000/api/password?length=12&includeUppercase=true&includeLowercase=true&includeNumbers=true"

# Contraseña de 20 caracteres con símbolos
curl "http://localhost:8000/api/password?length=20&includeSymbols=true&excludeAmbiguous=true"
```

---

### 2. POST /api/passwords - Generar Múltiples

Genera múltiples contraseñas seguras en una sola solicitud.

#### Especificación

- **Método HTTP:** `POST`
- **Ruta:** `/api/passwords`
- **Content-Type:** `application/json`

#### Parámetros de Entrada (Body JSON)

| Parámetro | Tipo | Requerido | Default | Rango | Descripción |
|-----------|------|-----------|---------|-------|-------------|
| `count` | integer | No | 5 | 1-100 | Número de contraseñas a generar |
| `length` | integer | No | 16 | 4-128 | Longitud de cada contraseña |
| `includeUppercase` | boolean | No | true | - | Incluir letras mayúsculas [A-Z] |
| `includeLowercase` | boolean | No | true | - | Incluir letras minúsculas [a-z] |
| `includeNumbers` | boolean | No | true | - | Incluir números [0-9] |
| `includeSymbols` | boolean | No | false | - | Incluir símbolos especiales |
| `excludeAmbiguous` | boolean | No | true | - | Excluir caracteres ambiguos |
| `exclude` | string | No | "" | max 100 | Caracteres específicos a excluir |
| `requireEach` | boolean | No | true | - | Garantizar al menos 1 carácter de cada tipo |

#### Códigos de Respuesta HTTP

| Código | Descripción |
|--------|-------------|
| `200` | Contraseñas generadas exitosamente |
| `400` | Parámetros inválidos (ej: count > 100) |
| `429` | Rate limit excedido |
| `500` | Error interno del servidor |

#### Estructura de Respuesta JSON

**Éxito (200):**
```json
{
  "success": true,
  "passwords": [
    "5MRh$^g*u%b7M{.q",
    "5}s]a{cY)ua3MkS4",
    "9Sh{A_W?(pdMFb8@",
    "|%NK3-wq6M7)zSNi",
    ")zgWGMb&<!z8tab2"
  ],
  "count": 5,
  "length": 16
}
```

**Error (400):**
```json
{
  "success": false,
  "error": "Count debe estar entre 1 y 100"
}
```

#### Ejemplo de Solicitud

```bash
# Generar 5 contraseñas de 16 caracteres con símbolos
curl -X POST "http://localhost:8000/api/passwords" \
  -H "Content-Type: application/json" \
  -d '{
    "count": 5,
    "length": 16,
    "includeSymbols": true,
    "excludeAmbiguous": true
  }'

# Generar 10 contraseñas simples (solo letras y números)
curl -X POST "http://localhost:8000/api/passwords" \
  -H "Content-Type: application/json" \
  -d '{
    "count": 10,
    "length": 12,
    "includeSymbols": false
  }'
```

---

### 3. POST /api/password/validate - Validar con Requisitos

Valida una contraseña contra requisitos específicos y proporciona un análisis detallado.

#### Especificación

- **Método HTTP:** `POST`
- **Ruta:** `/api/password/validate`
- **Content-Type:** `application/json`

#### Parámetros de Entrada (Body JSON)

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `password` | string | **Sí** | Contraseña a validar |
| `requirements` | object | No | Requisitos específicos a cumplir |
| `requirements.minLength` | integer | No | Longitud mínima requerida |
| `requirements.requireUppercase` | boolean | No | Requerir al menos una mayúscula |
| `requirements.requireLowercase` | boolean | No | Requerir al menos una minúscula |
| `requirements.requireNumbers` | boolean | No | Requerir al menos un número |
| `requirements.requireSymbols` | boolean | No | Requerir al menos un símbolo |

#### Códigos de Respuesta HTTP

| Código | Descripción |
|--------|-------------|
| `200` | Validación completada exitosamente |
| `400` | Parámetro faltante o inválido |
| `500` | Error interno del servidor |

#### Estructura de Respuesta JSON

**Éxito - Con Requirements (200):**
```json
{
  "success": true,
  "valid": true,
  "meetsRequirements": true,
  "failedRequirements": [],
  "analysis": {
    "is_valid": true,
    "strength": "fuerte",
    "strength_label": "Fuerte",
    "score": 75,
    "length": 17,
    "composition": {
      "has_uppercase": true,
      "has_lowercase": true,
      "has_digits": true,
      "has_symbols": true,
      "uppercase_count": 2,
      "lowercase_count": 9,
      "digit_count": 3,
      "symbol_count": 3
    },
    "analysis": {
      "unique_characters": 16,
      "diversity_percentage": 94.12,
      "has_ambiguous_chars": true,
      "weak_patterns_detected": ["secuencial_numerico"]
    },
    "security": {
      "estimated_crack_time": "Millones de años",
      "possible_combinations": "3,492,798,333,840,548,741,149,884,181,118,976"
    },
    "recommendations": [
      "Evitar secuencias numéricas (123, 456, etc.)"
    ]
  }
}
```

**Contraseña que NO cumple requirements:**
```json
{
  "success": true,
  "valid": false,
  "meetsRequirements": false,
  "failedRequirements": [
    "Longitud mínima de 12 caracteres",
    "Debe contener al menos un símbolo"
  ],
  "analysis": {
    "is_valid": false,
    "strength": "moderada",
    "score": 45,
    "length": 8,
    ...
  }
}
```

**Sin Requirements (análisis básico):**
```json
{
  "success": true,
  "data": {
    "is_valid": true,
    "strength": "fuerte",
    "score": 70,
    ...
  }
}
```

#### Ejemplos de Solicitud

```bash
# Validar con requisitos específicos
curl -X POST "http://localhost:8000/api/password/validate" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "MiContraseña123!",
    "requirements": {
      "minLength": 8,
      "requireUppercase": true,
      "requireNumbers": true,
      "requireSymbols": true
    }
  }'

# Validar sin requisitos (solo análisis)
curl -X POST "http://localhost:8000/api/password/validate" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "TestPassword2024"
  }'
```

---

### 4. GET /api/password/config - Obtener Configuración

Obtiene los límites y valores por defecto configurados en la API.

#### Especificación

- **Método HTTP:** `GET`
- **Ruta:** `/api/password/config`
- **Autenticación:** No requerida
- **Rate Limiting:** No aplica

#### Estructura de Respuesta JSON

```json
{
  "success": true,
  "config": {
    "length": {
      "min": 4,
      "max": 128,
      "default": 16
    },
    "count": {
      "min": 1,
      "max": 100,
      "default": 5
    },
    "exclude_max_length": 100,
    "character_sets": {
      "uppercase": "ABCDEFGHJKMNPQRSTUVWXYZ",
      "lowercase": "abcdefghjkmnpqrstuvwxyz",
      "digits": "23456789",
      "symbols": "!@#$%^&*()_+-=[]{}|;:,.<>?"
    },
    "defaults": {
      "upper": true,
      "lower": true,
      "digits": true,
      "symbols": false,
      "avoid_ambiguous": true,
      "require_each": true
    }
  }
}
```

#### Ejemplo de Solicitud

```bash
curl "http://localhost:8000/api/password/config"
```

---

## Endpoints Heredados (Compatibilidad)

Estos endpoints mantienen la convención de nombres original para compatibilidad con implementaciones anteriores.

### POST /api/password/generate

Genera una contraseña usando nombres de parámetros originales (`upper`, `lower`, `digits`, `symbols`).

#### Parámetros principales

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `length` | integer | 16 | Longitud (4-128) |
| `upper` | boolean | true | Incluir mayúsculas |
| `lower` | boolean | true | Incluir minúsculas |
| `digits` | boolean | true | Incluir números |
| `symbols` | boolean | true | Incluir símbolos |
| `avoid_ambiguous` | boolean | true | Evitar ambiguos |
| `exclude` | string | "" | Caracteres a excluir |
| `require_each` | boolean | true | Requerir cada tipo |

**Ejemplo:**
```bash
curl -X POST "http://localhost:8000/api/password/generate" \
  -H "Content-Type: application/json" \
  -d '{
    "length": 20,
    "upper": true,
    "lower": true,
    "digits": true,
    "symbols": true,
    "avoid_ambiguous": true
  }'
```

**Respuesta:**
```json
{
  "success": true,
  "password": "aB3$xY9@zP2#qM5!",
  "length": 16,
  "options": {
    "upper": true,
    "lower": true,
    "digits": true,
    "symbols": true,
    "avoid_ambiguous": true,
    "exclude": "",
    "require_each": true
  }
}
```

---

### POST /api/password/generate-multiple

Genera múltiples contraseñas usando nombres de parámetros originales.

#### Parámetros principales

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `count` | integer | 5 | Cantidad (1-100) |
| `length` | integer | 16 | Longitud (4-128) |
| `upper` | boolean | true | Incluir mayúsculas |
| `lower` | boolean | true | Incluir minúsculas |
| `digits` | boolean | true | Incluir números |
| `symbols` | boolean | true | Incluir símbolos |

**Ejemplo:**
```bash
curl -X POST "http://localhost:8000/api/password/generate-multiple" \
  -H "Content-Type: application/json" \
  -d '{
    "count": 3,
    "length": 12,
    "upper": true,
    "lower": true,
    "digits": true,
    "symbols": false
  }'
```

**Respuesta:**
```json
{
  "success": true,
  "passwords": [
    "aB3xY9zP2qM5",
    "pQ7mN4wR8tL2",
    "vK9sD6fG3hJ5"
  ],
  "count": 3,
  "length": 12,
  "options": {...}
}
```

---

### POST /api/password/validate-strength

Valida una contraseña sin requisitos específicos (solo devuelve análisis de fortaleza).

**Ejemplo:**
```bash
curl -X POST "http://localhost:8000/api/password/validate-strength" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "MyP@ssw0rd2024!"
  }'
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "is_valid": true,
    "strength": "fuerte",
    "strength_label": "Fuerte",
    "score": 75,
    "length": 15,
    "composition": {...},
    "analysis": {...},
    "security": {...},
    "recommendations": []
  }
}
```

---

## Rate Limiting

Todos los endpoints de generación y validación están protegidos con rate limiting para prevenir abuso:

### Límites

- **60 solicitudes por minuto** por dirección IP
- **500 contraseñas generadas por minuto** por dirección IP

### Respuesta cuando se excede el límite (429)

```json
{
  "success": false,
  "error": "Límite de solicitudes excedido. Intente nuevamente en unos momentos."
}
```

### Headers de Rate Limit

La API incluye headers informativos en cada respuesta:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1645372800
```

### Endpoints sin Rate Limit

- `GET /api/password/config` - No tiene rate limiting

---

## Análisis de Fortaleza de Contraseñas

La API proporciona un análisis detallado de la fortaleza de contraseñas en el endpoint `POST /api/password/validate`.

### Niveles de Fortaleza

| Nivel | Score | Descripción |
|-------|-------|-------------|
| `muy_debil` | 0-20 | Contraseña extremadamente vulnerable |
| `debil` | 21-40 | Contraseña con protección insuficiente |
| `moderada` | 41-60 | Contraseña aceptable pero mejorable |
| `fuerte` | 61-80 | Contraseña robusta y segura |
| `muy_fuerte` | 81-100 | Contraseña excepcionalmente segura |

### Factores de Análisis

El análisis considera múltiples factores:

1. **Longitud:** Mayor longitud aumenta la seguridad exponencialmente
2. **Diversidad de caracteres:** Uso de mayúsculas, minúsculas, números y símbolos
3. **Entropía:** Aleatoriedad y variedad de caracteres
4. **Patrones débiles detectados:**
   - Secuencias numéricas (123, 456, 789)
   - Secuencias de teclado (qwerty, asdf)
   - Repeticiones (aaa, 111)
   - Palabras comunes del diccionario
5. **Caracteres únicos:** Porcentaje de diversidad
6. **Tiempo estimado de craqueo:** Basado en fuerza bruta moderna

### Recomendaciones Automáticas

El sistema proporciona recomendaciones específicas basadas en el análisis:

- Aumentar longitud si es menor a 12 caracteres
- Agregar mayúsculas/minúsculas/números/símbolos faltantes
- Evitar secuencias predecibles
- Eliminar repeticiones excesivas
- Evitar caracteres ambiguos para mejor usabilidad

---

## Códigos de Estado HTTP

La API utiliza códigos de estado HTTP estándar:

| Código | Nombre | Descripción |
|--------|--------|-------------|
| `200` | OK | Solicitud exitosa |
| `400` | Bad Request | Parámetros inválidos o faltantes |
| `422` | Unprocessable Entity | Error de validación de campos |
| `429` | Too Many Requests | Rate limit excedido |
| `500` | Internal Server Error | Error interno del servidor |

---

## Ejemplos de Uso

### Ejemplo 1: Generar Contraseña Simple (GET)

```bash
# Generar una contraseña básica de 12 caracteres
curl "http://localhost:8000/api/password?length=12"
```

**Respuesta:**
```json
{
  "success": true,
  "password": "aBc3DeF9GhJ2",
  "length": 12,
  "options": {
    "upper": true,
    "lower": true,
    "digits": true,
    "symbols": false,
    "avoid_ambiguous": true,
    "exclude": "",
    "require_each": true
  }
}
```

### Ejemplo 2: Generar Contraseña Ultra Segura

```bash
# Contraseña de 24 caracteres con todos los tipos
curl "http://localhost:8000/api/password?length=24&includeSymbols=true"
```

### Ejemplo 3: Generar Múltiples Contraseñas para Usuarios

```bash
# Generar 10 contraseñas de 16 caracteres
curl -X POST "http://localhost:8000/api/passwords" \
  -H "Content-Type: application/json" \
  -d '{
    "count": 10,
    "length": 16,
    "includeSymbols": true,
    "excludeAmbiguous": true
  }'
```

### Ejemplo 4: Validar Política de Contraseñas Corporativa

```bash
# Validar que cumpla: 10+ caracteres, mayúsculas, números y símbolos
curl -X POST "http://localhost:8000/api/password/validate" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "Corp2024$ecure",
    "requirements": {
      "minLength": 10,
      "requireUppercase": true,
      "requireNumbers": true,
      "requireSymbols": true
    }
  }'
```

### Ejemplo 5: Obtener Configuración del Sistema

```bash
# Ver límites y valores por defecto
curl "http://localhost:8000/api/password/config"
```

### Ejemplo 6 (Python): Integración Completa

```python
import requests

# Configuración base
API_URL = "http://localhost:8000/api"

# 1. Generar contraseña
response = requests.get(
    f"{API_URL}/password",
    params={
        "length": 16,
        "includeUppercase": True,
        "includeNumbers": True,
        "includeSymbols": True
    }
)
password = response.json()["password"]
print(f"Contraseña generada: {password}")

# 2. Validar contraseña
response = requests.post(
    f"{API_URL}/password/validate",
    json={
        "password": password,
        "requirements": {
            "minLength": 12,
            "requireUppercase": True,
            "requireNumbers": True,
            "requireSymbols": True
        }
    }
)
validation = response.json()
print(f"Válida: {validation['valid']}")
print(f"Fortaleza: {validation['analysis']['strength_label']}")
print(f"Score: {validation['analysis']['score']}")
```

---

## Mejores Prácticas

### Para Generar Contraseñas

1. **Longitud mínima recomendada:** 12 caracteres
2. **Longitud óptima:** 16+ caracteres
3. **Activar todas las categorías:** upper, lower, digits, symbols
4. **Evitar caracteres ambiguos:** Especialmente para contraseñas que se escribirán manualmente
5. **Usar `require_each`: true** para garantizar diversidad

### Para Validar Contraseñas

1. **Score mínimo aceptable:** 40 (moderada)
2. **Score recomendado:** 60+ (fuerte)
3. **Score ideal:** 80+ (muy fuerte)
4. **Atender todas las recomendaciones** del análisis
5. **Evitar patrones débiles** detectados por el sistema

---

## Soporte

Para reportar problemas o sugerencias, por favor contacta al equipo de desarrollo.

**Versión:** 2.0.0  
**Última actualización:** Febrero 2026

#### Parámetros de Entrada (Body JSON)

| Parámetro | Tipo | Requerido | Default | Descripción |
|-----------|------|-----------|---------|-------------|
| `length` | integer | No | 16 | Longitud de la contraseña (mínimo 1) |
| `upper` | boolean | No | true | Incluir letras mayúsculas [A-Z] |
| `lower` | boolean | No | true | Incluir letras minúsculas [a-z] |
| `digits` | boolean | No | true | Incluir números [0-9] |
| `symbols` | boolean | No | true | Incluir símbolos especiales |
| `avoid_ambiguous` | boolean | No | true | Evitar caracteres ambiguos (I, l, 1, O, 0, o) |
| `exclude` | string | No | "" | Caracteres específicos a excluir |
| `require_each` | boolean | No | true | Garantizar al menos 1 carácter de cada categoría seleccionada |

#### Códigos de Respuesta HTTP

| Código | Descripción |
|--------|-------------|
| `200` | Contraseña generada exitosamente |
| `400` | Parámetros inválidos o configuración incorrecta |
| `500` | Error interno del servidor |

#### Estructura de Respuesta JSON

**Éxito (200):**
```json
{
  "success": true,
  "password": "aB3$xY9@zP2#qM5!",
  "length": 16,
  "options": {
    "upper": true,
    "lower": true,
    "digits": true,
    "symbols": true,
    "avoid_ambiguous": true,
    "exclude": "",
    "require_each": true
  }
}
```

**Error (400):**
```json
{
  "success": false,
  "error": "La longitud debe ser >= 1"
}
```

**Error (500):**
```json
{
  "success": false,
  "error": "Error al generar la contraseña"
}
```

#### Ejemplo de Solicitud

```bash
curl -X POST http://localhost/api/password/generate \
  -H "Content-Type: application/json" \
  -d '{
    "length": 20,
    "upper": true,
    "lower": true,
    "digits": true,
    "symbols": true,
    "avoid_ambiguous": true,
    "exclude": "",
    "require_each": true
  }'
```

---

### 2. Generar Múltiples Contraseñas

Genera múltiples contraseñas seguras de una sola vez.

#### Especificación

- **Método HTTP:** `POST`
- **Ruta:** `/api/password/generate-multiple`
- **Content-Type:** `application/json`

#### Parámetros de Entrada (Body JSON)

| Parámetro | Tipo | Requerido | Default | Descripción |
|-----------|------|-----------|---------|-------------|
| `count` | integer | No | 5 | Número de contraseñas a generar |
| `length` | integer | No | 16 | Longitud de cada contraseña |
| `upper` | boolean | No | true | Incluir letras mayúsculas [A-Z] |
| `lower` | boolean | No | true | Incluir letras minúsculas [a-z] |
| `digits` | boolean | No | true | Incluir números [0-9] |
| `symbols` | boolean | No | true | Incluir símbolos especiales |
| `avoid_ambiguous` | boolean | No | true | Evitar caracteres ambiguos |
| `exclude` | string | No | "" | Caracteres específicos a excluir |
| `require_each` | boolean | No | true | Garantizar al menos 1 carácter de cada categoría |

#### Códigos de Respuesta HTTP

| Código | Descripción |
|--------|-------------|
| `200` | Contraseñas generadas exitosamente |
| `400` | Parámetros inválidos o configuración incorrecta |
| `500` | Error interno del servidor |

#### Estructura de Respuesta JSON

**Éxito (200):**
```json
{
  "success": true,
  "passwords": [
    "aB3$xY9@zP2#qM5!",
    "pQ7*mN4&wR8^tL2%",
    "vK9#sD6@fG3$hJ5!",
    "cX2&bV8*nM4#zQ7@",
    "tY5$gH9!jK3^lP6&"
  ],
  "count": 5,
  "length": 16,
  "options": {
    "upper": true,
    "lower": true,
    "digits": true,
    "symbols": true,
    "avoid_ambiguous": true,
    "exclude": "",
    "require_each": true
  }
}
```

**Error (400):**
```json
{
  "success": false,
  "error": "Debe activarse al menos una categoría (upper/lower/digits/symbols)."
}
```

**Error (500):**
```json
{
  "success": false,
  "error": "Error al generar las contraseñas"
}
```

#### Ejemplo de Solicitud

```bash
curl -X POST http://localhost/api/password/generate-multiple \
  -H "Content-Type: application/json" \
  -d '{
    "count": 3,
    "length": 12,
    "upper": true,
    "lower": true,
    "digits": true,
    "symbols": false
  }'
```

---

### 3. Validar Contraseña

Verifica la fortaleza de una contraseña existente y proporciona un análisis detallado.

#### Especificación

- **Método HTTP:** `POST`
- **Ruta:** `/api/password/validate`
- **Content-Type:** `application/json`

#### Parámetros de Entrada (Body JSON)

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| `password` | string | **Sí** | Contraseña a validar |

#### Códigos de Respuesta HTTP

| Código | Descripción |
|--------|-------------|
| `200` | Validación completada exitosamente |
| `400` | Parámetro faltante o inválido |
| `500` | Error interno del servidor |

#### Estructura de Respuesta JSON

**Éxito (200):**
```json
{
  "success": true,
  "data": {
    "is_valid": true,
    "strength": "fuerte",
    "strength_label": "Fuerte",
    "score": 75,
    "length": 16,
    "composition": {
      "has_uppercase": true,
      "has_lowercase": true,
      "has_digits": true,
      "has_symbols": true,
      "uppercase_count": 4,
      "lowercase_count": 6,
      "digit_count": 3,
      "symbol_count": 3
    },
    "analysis": {
      "unique_characters": 15,
      "diversity_percentage": 93.75,
      "has_ambiguous_chars": false,
      "weak_patterns_detected": []
    },
    "security": {
      "estimated_crack_time": "Millones de años",
      "possible_combinations": "7,213,895,789,838,336"
    },
    "recommendations": []
  }
}
```

#### Niveles de Fortaleza

| Score | Nivel | Etiqueta |
|-------|-------|----------|
| 80-100 | `muy_fuerte` | Muy Fuerte |
| 60-79 | `fuerte` | Fuerte |
| 40-59 | `moderada` | Moderada |
| 20-39 | `debil` | Débil |
| 0-19 | `muy_debil` | Muy Débil |

#### Criterios de Evaluación

**Puntuación Base (0-100 puntos):**

1. **Longitud (máx. 30 pts):**
   - ≥16 caracteres: 30 pts
   - ≥12 caracteres: 25 pts
   - ≥8 caracteres: 15 pts
   - ≥6 caracteres: 10 pts
   - <6 caracteres: 5 pts

2. **Complejidad (máx. 40 pts):**
   - Mayúsculas: +10 pts
   - Minúsculas: +10 pts
   - Números: +10 pts
   - Símbolos: +10 pts

3. **Diversidad (máx. 20 pts):**
   - ≥90% caracteres únicos: 20 pts
   - ≥75%: 15 pts
   - ≥50%: 10 pts
   - <50%: 5 pts

4. **Penalizaciones:**
   - Secuencias numéricas (123, 456): -10 pts
   - Secuencias alfabéticas (abc, def): -10 pts
   - Caracteres repetidos (aaa, 111): -10 pts
   - Patrones de teclado (qwerty, asdf): -10 pts
   - Caracteres ambiguos: -5 pts

#### Patrones Débiles Detectados

- `secuencial_numerico`: Secuencias como 123, 456, 789
- `secuencial_alfabetico`: Secuencias como abc, def, xyz
- `repeticion`: Caracteres repetidos consecutivamente (3 o más)
- `teclado`: Patrones de teclado como qwerty, asdfgh, 12345

#### Ejemplos de Respuesta

**Contraseña Débil:**
```json
{
  "success": true,
  "data": {
    "is_valid": false,
    "strength": "debil",
    "strength_label": "Débil",
    "score": 35,
    "length": 8,
    "composition": {
      "has_uppercase": false,
      "has_lowercase": true,
      "has_digits": true,
      "has_symbols": false,
      "uppercase_count": 0,
      "lowercase_count": 5,
      "digit_count": 3,
      "symbol_count": 0
    },
    "analysis": {
      "unique_characters": 7,
      "diversity_percentage": 87.5,
      "has_ambiguous_chars": false,
      "weak_patterns_detected": ["secuencial_numerico"]
    },
    "security": {
      "estimated_crack_time": "3 minutos",
      "possible_combinations": "2,821,109,907,456"
    },
    "recommendations": [
      "Aumentar la longitud a al menos 12 caracteres",
      "Agregar letras mayúsculas",
      "Agregar símbolos especiales",
      "Evitar secuencias numéricas (123, 456, etc.)"
    ]
  }
}
```

**Error (400):**
```json
{
  "success": false,
  "error": "El campo \"password\" es requerido"
}
```

**Error (500):**
```json
{
  "success": false,
  "error": "Error al validar la contraseña"
}
```

#### Ejemplo de Solicitud

```bash
curl -X POST http://localhost/api/password/validate \
  -H "Content-Type: application/json" \
  -d '{
    "password": "MyP@ssw0rd2024!"
  }'
```

---

## Códigos de Estado HTTP

| Código | Significado | Cuándo se usa |
|--------|-------------|---------------|
| `200 OK` | Éxito | Operación completada correctamente |
| `400 Bad Request` | Solicitud inválida | Parámetros faltantes, inválidos o configuración incorrecta |
| `500 Internal Server Error` | Error del servidor | Error inesperado en el procesamiento |

---

## Ejemplos de Uso

### PHP (cURL)

```php
<?php
// Generar contraseña
$ch = curl_init('http://localhost/api/password/generate');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'length' => 16,
    'symbols' => true
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo $data['password'];

// Validar contraseña
$ch = curl_init('http://localhost/api/password/validate');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'password' => 'TestP@ssw0rd!'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo "Fortaleza: " . $data['data']['strength_label'];
?>
```

### JavaScript (Fetch API)

```javascript
// Generar contraseña
fetch('http://localhost/api/password/generate', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    length: 20,
    symbols: true,
    avoid_ambiguous: true
  })
})
.then(response => response.json())
.then(data => console.log('Contraseña:', data.password));

// Validar contraseña
fetch('http://localhost/api/password/validate', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    password: 'MySecureP@ss123!'
  })
})
.then(response => response.json())
.then(data => console.log('Score:', data.data.score));
```

### Python (requests)

```python
import requests
import json

# Generar contraseña
response = requests.post(
    'http://localhost/api/password/generate',
    json={
        'length': 16,
        'symbols': True,
        'digits': True
    }
)
data = response.json()
print(f"Contraseña: {data['password']}")

# Validar contraseña
response = requests.post(
    'http://localhost/api/password/validate',
    json={
        'password': 'Test123!@#Password'
    }
)
data = response.json()
print(f"Fortaleza: {data['data']['strength_label']}")
print(f"Score: {data['data']['score']}")
```

---

## Mejores Prácticas

### Para Generar Contraseñas

1. **Longitud mínima recomendada:** 12 caracteres
2. **Longitud óptima:** 16+ caracteres
3. **Activar todas las categorías:** upper, lower, digits, symbols
4. **Evitar caracteres ambiguos:** Especialmente para contraseñas que se escribirán manualmente
5. **Usar `require_each`: true** para garantizar diversidad

### Para Validar Contraseñas

1. **Score mínimo aceptable:** 40 (moderada)
2. **Score recomendado:** 60+ (fuerte)
3. **Score ideal:** 80+ (muy fuerte)
4. **Atender todas las recomendaciones** del análisis
5. **Evitar patrones débiles** detectados por el sistema

---

## Soporte

Para reportar problemas o sugerencias, por favor contacta al equipo de desarrollo.

**Versión:** 1.0.0  
**Última actualización:** Febrero 2026
