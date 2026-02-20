# API de Generación y Validación de Contraseñas

## 📋 Índice
- [Información General](#información-general)
- [Endpoints](#endpoints)
  - [1. Generar Contraseña](#1-generar-contraseña)
  - [2. Generar Múltiples Contraseñas](#2-generar-múltiples-contraseñas)
  - [3. Validar Contraseña](#3-validar-contraseña)
- [Códigos de Estado HTTP](#códigos-de-estado-http)
- [Ejemplos de Uso](#ejemplos-de-uso)

---

## Información General

**URL Base:** `http://localhost/api` (o tu dominio configurado)

**Formato de Respuesta:** JSON

**Content-Type:** `application/json`

**Seguridad:** Las contraseñas se generan usando `random_int()` para entropía criptográfica y Fisher-Yates shuffle para mezclar caracteres de forma segura.

---

## Endpoints

### 1. Generar Contraseña

Genera una contraseña segura con opciones personalizables.

#### Especificación

- **Método HTTP:** `POST`
- **Ruta:** `/api/password/generate`
- **Content-Type:** `application/json`

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
