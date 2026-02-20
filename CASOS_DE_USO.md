# Casos de Uso - API de Generación de Contraseñas

Este documento describe los casos de uso implementados en la API de generación de contraseñas.

## Casos de Uso Implementados

### Caso 1: Generar Contraseña con Parámetros de Query (GET)

**Endpoint:** `GET /api/password`

**Descripción:** Genera una contraseña utilizando parámetros de query string.

**Parámetros de Query:**
- `length` (integer, opcional): Longitud de la contraseña (4-128, default: 16)
- `includeUppercase` (boolean, opcional): Incluir mayúsculas (default: true)
- `includeLowercase` (boolean, opcional): Incluir minúsculas (default: true)
- `includeNumbers` (boolean, opcional): Incluir números (default: true)
- `includeSymbols` (boolean, opcional): Incluir símbolos (default: false)
- `excludeAmbiguous` (boolean, opcional): Excluir caracteres ambiguos (default: true)
- `exclude` (string, opcional): Caracteres a excluir (default: "")
- `requireEach` (boolean, opcional): Requerir al menos uno de cada tipo (default: true)

**Ejemplo de Solicitud:**
```bash
curl -X GET "http://localhost:8000/api/password?length=12&includeUppercase=true&includeLowercase=true&includeNumbers=true"
```

**Ejemplo de Respuesta (200):**
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

---

### Caso 2: Generar Múltiples Contraseñas (POST)

**Endpoint:** `POST /api/passwords`

**Descripción:** Genera múltiples contraseñas en una sola solicitud.

**Parámetros del Body (JSON):**
- `count` (integer, requerido): Cantidad de contraseñas a generar (1-100)
- `length` (integer, opcional): Longitud de cada contraseña (4-128, default: 16)
- `includeUppercase` (boolean, opcional): Incluir mayúsculas (default: true)
- `includeLowercase` (boolean, opcional): Incluir minúsculas (default: true)
- `includeNumbers` (boolean, opcional): Incluir números (default: true)
- `includeSymbols` (boolean, opcional): Incluir símbolos (default: false)
- `excludeAmbiguous` (boolean, opcional): Excluir caracteres ambiguos (default: true)
- `exclude` (string, opcional): Caracteres a excluir (default: "")
- `requireEach` (boolean, opcional): Requerir al menos uno de cada tipo (default: true)

**Ejemplo de Solicitud:**
```bash
curl -X POST "http://localhost:8000/api/passwords" \
  -H "Content-Type: application/json" \
  -d '{
    "count": 5,
    "length": 16,
    "includeSymbols": true,
    "excludeAmbiguous": true
  }'
```

**Ejemplo de Respuesta (200):**
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

---

### Caso 3: Validar Contraseña con Requisitos Específicos (POST)

**Endpoint:** `POST /api/password/validate`

**Descripción:** Valida una contraseña contra requisitos específicos.

**Parámetros del Body (JSON):**
- `password` (string, requerido): La contraseña a validar
- `requirements` (object, opcional): Requisitos de validación
  - `minLength` (integer): Longitud mínima requerida
  - `requireUppercase` (boolean): Requerir al menos una mayúscula
  - `requireLowercase` (boolean): Requerir al menos una minúscula
  - `requireNumbers` (boolean): Requerir al menos un número
  - `requireSymbols` (boolean): Requerir al menos un símbolo

**Ejemplo de Solicitud:**
```bash
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
```

**Ejemplo de Respuesta (200) - Válida:**
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

**Ejemplo de Respuesta (200) - Inválida:**
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
        "strength": "debil",
        "length": 8,
        ...
    }
}
```

---

### Caso 4: Manejo de Errores - Longitud Excesiva

**Endpoint:** `GET /api/password` o `POST /api/passwords`

**Descripción:** Cuando se solicita una longitud mayor al máximo permitido (128), la API devuelve un error 400.

**Ejemplo de Solicitud:**
```bash
curl -X GET "http://localhost:8000/api/password?length=1000"
```

**Ejemplo de Respuesta (400):**
```json
{
    "success": false,
    "error": "La longitud debe ser <= 128"
}
```

---

## Endpoints Heredados (Compatibilidad)

La API mantiene los endpoints originales para compatibilidad con versiones anteriores:

### POST /api/password/generate
Genera una contraseña única usando parámetros en el body (nombres de parámetros: `upper`, `lower`, `digits`, `symbols`, etc.)

### POST /api/password/generate-multiple
Genera múltiples contraseñas usando parámetros en el body (nombres de parámetros originales)

### POST /api/password/validate-strength
Valida una contraseña sin requisitos específicos (solo análisis de fortaleza)

### GET /api/password/config
Obtiene la configuración de parámetros permitidos y valores por defecto

---

## Rate Limiting

Todos los endpoints de generación y validación están protegidos con rate limiting:
- **60 solicitudes por minuto** por dirección IP
- **500 contraseñas generadas por minuto** por dirección IP

Cuando se excede el límite, la API devuelve un error 429:

```json
{
    "success": false,
    "error": "Límite de solicitudes excedido. Intente nuevamente en unos momentos."
}
```

---

## Códigos de Estado HTTP

- **200**: Solicitud exitosa
- **400**: Error de validación (parámetros inválidos)
- **422**: Error de validación de campos
- **429**: Límite de tasa excedido (rate limit)
- **500**: Error interno del servidor

---

## Notas Importantes

1. **Caracteres Ambiguos**: Por defecto, se excluyen caracteres ambiguos como `0`, `O`, `1`, `l`, `I` para mejorar la legibilidad
2. **Seguridad Criptográfica**: La API utiliza `random_int()` de PHP para generar números aleatorios criptográficamente seguros
3. **Algoritmo de Mezcla**: Se utiliza el algoritmo Fisher-Yates para garantizar una distribución uniforme de caracteres
4. **Validación**: Todos los parámetros son validados antes de procesar la solicitud

---

## Pruebas Rápidas

Para probar rápidamente todos los casos de uso:

```bash
# Caso 1: GET con query params
curl "http://localhost:8000/api/password?length=12&includeUppercase=true&includeNumbers=true"

# Caso 2: POST múltiples contraseñas
curl -X POST "http://localhost:8000/api/passwords" \
  -H "Content-Type: application/json" \
  -d '{"count": 5, "length": 16}'

# Caso 3: Validar con requirements
curl -X POST "http://localhost:8000/api/password/validate" \
  -H "Content-Type: application/json" \
  -d '{"password": "Test123!", "requirements": {"minLength": 8, "requireUppercase": true}}'

# Caso 4: Error por longitud excesiva
curl "http://localhost:8000/api/password?length=1000"
```
