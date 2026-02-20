# 📋 Especificación Completa de Parámetros

## Tabla de Contenidos
- [Parámetro: length (Longitud)](#parámetro-length-longitud)
- [Parámetro: count (Cantidad)](#parámetro-count-cantidad)
- [Parámetros de Tipos de Caracteres](#parámetros-de-tipos-de-caracteres)
- [Parámetro: exclude (Exclusiones)](#parámetro-exclude-exclusiones)
- [Parámetro: avoid_ambiguous](#parámetro-avoid_ambiguous)
- [Parámetro: require_each (Patrones)](#parámetro-require_each-patrones)
- [Endpoint de Configuración](#endpoint-de-configuración)
- [Validaciones y Errores](#validaciones-y-errores)

---

## Parámetro: length (Longitud)

### Especificación

| Aspecto | Valor |
|---------|-------|
| **Tipo** | `integer` |
| **Mínimo** | `4` caracteres |
| **Máximo** | `128` caracteres |
| **Por defecto** | `16` caracteres |
| **Recomendado mínimo** | `12` caracteres |
| **Óptimo** | `16` caracteres o más |

### Descripción
Define la longitud de la contraseña a generar. Una longitud mayor proporciona más seguridad.

### Ejemplos

```json
// Contraseña corta (mínimo permitido)
{
  "length": 4
}

// Contraseña estándar (por defecto)
{
  "length": 16
}

// Contraseña larga (alta seguridad)
{
  "length": 32
}

// Contraseña muy larga (máximo permitido)
{
  "length": 128
}
```

### Validaciones

- ✅ **Válido:** `4 <= length <= 128`
- ❌ **Error:** `length < 4` → "La longitud debe ser >= 4"
- ❌ **Error:** `length > 128` → "La longitud debe ser <= 128"
- ❌ **Error:** `length no es entero` → "La longitud debe ser un número entero"

### Consideraciones de Seguridad

| Longitud | Nivel de Seguridad | Uso Recomendado |
|----------|-------------------|-----------------|
| 4-7 | ⚠️ Muy Bajo | No recomendado (solo para testing) |
| 8-11 | 🟡 Bajo | Aplicaciones de bajo riesgo |
| 12-15 | 🟢 Moderado | Uso general, cuentas estándar |
| 16-23 | 🔵 Alto | Cuentas importantes, datos sensibles |
| 24+ | 🟣 Muy Alto | Máxima seguridad, sistemas críticos |

---

## Parámetro: count (Cantidad)

### Especificación

| Aspecto | Valor |
|---------|-------|
| **Tipo** | `integer` |
| **Mínimo** | `1` contraseña |
| **Máximo** | `100` contraseñas |
| **Por defecto** | `5` contraseñas |
| **Endpoint** | Solo `/api/password/generate-multiple` |

### Descripción
Define cuántas contraseñas se generarán en una sola petición. Útil para generar múltiples contraseñas de forma eficiente.

### Ejemplos

```json
// Generar una sola contraseña
{
  "count": 1,
  "length": 16
}

// Generar 5 contraseñas (por defecto)
{
  "count": 5,
  "length": 12
}

// Generar 50 contraseñas
{
  "count": 50,
  "length": 16
}

// Máximo permitido
{
  "count": 100,
  "length": 20
}
```

### Validaciones

- ✅ **Válido:** `1 <= count <= 100`
- ❌ **Error:** `count < 1` → "Debe generar al menos 1 contraseña"
- ❌ **Error:** `count > 100` → "No puede generar más de 100 contraseñas a la vez"
- ❌ **Error:** `count no es entero` → "El número de contraseñas debe ser un número entero"

### Límite y Razón

El máximo de 100 contraseñas por petición previene:
- ⏱️ Timeouts del servidor
- 💾 Uso excesivo de memoria
- 🚫 Abuso del servicio (DoS)
- 📊 Cargas pesadas innecesarias

---

## Parámetros de Tipos de Caracteres

### upper (Mayúsculas)

| Aspecto | Valor |
|---------|-------|
| **Tipo** | `boolean` |
| **Por defecto** | `true` |
| **Charset** | `ABCDEFGHIJKLMNOPQRSTUVWXYZ` |
| **Total** | 26 caracteres |

**Ejemplo:**
```json
{
  "length": 12,
  "upper": true  // Incluye A-Z
}
```

### lower (Minúsculas)

| Aspecto | Valor |
|---------|-------|
| **Tipo** | `boolean` |
| **Por defecto** | `true` |
| **Charset** | `abcdefghijklmnopqrstuvwxyz` |
| **Total** | 26 caracteres |

**Ejemplo:**
```json
{
  "length": 12,
  "lower": true  // Incluye a-z
}
```

### digits (Números)

| Aspecto | Valor |
|---------|-------|
| **Tipo** | `boolean` |
| **Por defecto** | `true` |
| **Charset** | `0123456789` |
| **Total** | 10 caracteres |

**Ejemplo:**
```json
{
  "length": 12,
  "digits": true  // Incluye 0-9
}
```

### symbols (Símbolos)

| Aspecto | Valor |
|---------|-------|
| **Tipo** | `boolean` |
| **Por defecto** | `true` |
| **Charset** | `!@#$%^&*()-_=+[]{}|;:,.<>?` |
| **Total** | 29 caracteres |

**Ejemplo:**
```json
{
  "length": 12,
  "symbols": true  // Incluye símbolos especiales
}
```

### Combinaciones de Tipos

```json
// Solo letras (sin números ni símbolos)
{
  "length": 16,
  "upper": true,
  "lower": true,
  "digits": false,
  "symbols": false
}

// Solo alfanumérico (sin símbolos)
{
  "length": 16,
  "upper": true,
  "lower": true,
  "digits": true,
  "symbols": false
}

// Máxima complejidad (todos los tipos)
{
  "length": 20,
  "upper": true,
  "lower": true,
  "digits": true,
  "symbols": true
}
```

### Validación Importante

⚠️ **Al menos una categoría debe estar activa** (`true`)

```json
// ❌ ERROR: Ninguna categoría activa
{
  "length": 12,
  "upper": false,
  "lower": false,
  "digits": false,
  "symbols": false
}
// Error: "Debe activarse al menos una categoría (upper, lower, digits, symbols)"
```

---

## Parámetro: exclude (Exclusiones)

### Especificación

| Aspecto | Valor |
|---------|-------|
| **Tipo** | `string` |
| **Por defecto** | `""` (vacío) |
| **Longitud máxima** | `100` caracteres |

### Descripción
Permite excluir caracteres específicos de la generación de contraseñas. Útil para cumplir con políticas específicas o evitar caracteres problemáticos.

### Ejemplos

```json
// Excluir vocales
{
  "length": 16,
  "exclude": "aeiouAEIOU"
}

// Excluir caracteres que pueden causar confusión
{
  "length": 16,
  "exclude": "Il1O0o"
}

// Excluir símbolos problemáticos en ciertos sistemas
{
  "length": 16,
  "exclude": "'\"\\`"
}

// Excluir números específicos
{
  "length": 16,
  "exclude": "0123"
}

// Sin exclusiones (por defecto)
{
  "length": 16,
  "exclude": ""
}
```

### Casos de Uso

| Caso | Exclusión | Razón |
|------|-----------|-------|
| **URLs** | `&=?#` | Caracteres especiales en URLs |
| **SQL** | `';--` | Prevención de SQL injection visual |
| **CSV** | `,;"` | Delimitadores de CSV |
| **XML** | `<>&` | Caracteres especiales XML |
| **Bash** | `$\`|` | Caracteres especiales de shell |

### Validaciones

- ✅ **Válido:** `0 <= strlen(exclude) <= 100`
- ❌ **Error:** `strlen(exclude) > 100` → "El parámetro 'exclude' no puede exceder 100 caracteres"

### Comportamiento con Exclusiones

```json
// Si excluyes TODOS los caracteres de una categoría activa, se produce error
{
  "length": 16,
  "digits": true,
  "exclude": "0123456789"
}
// ❌ Error: "Después de aplicar exclusiones, la categoría 'digits' no tiene caracteres disponibles"
```

---

## Parámetro: avoid_ambiguous

### Especificación

| Aspecto | Valor |
|---------|-------|
| **Tipo** | `boolean` |
| **Por defecto** | `true` |
| **Caracteres excluidos** | `Il1O0o` |

### Descripción
Cuando está activo (`true`), excluye automáticamente caracteres que pueden ser confundidos visualmente entre sí.

### Caracteres Ambiguos

| Carácter | Puede confundirse con |
|----------|----------------------|
| `I` (i mayúscula) | `l` (L minúscula), `1` (uno) |
| `l` (L minúscula) | `I` (i mayúscula), `1` (uno) |
| `1` (uno) | `I` (i mayúscula), `l` (L minúscula) |
| `O` (o mayúscula) | `0` (cero) |
| `0` (cero) | `O` (o mayúscula) |
| `o` (o minúscula) | `0` (cero), `O` (o mayúscula) |

### Ejemplos

```json
// Con prevención de caracteres ambiguos (por defecto)
{
  "length": 16,
  "avoid_ambiguous": true
}
// No incluirá: I, l, 1, O, 0, o

// Sin prevención (incluye todos los caracteres)
{
  "length": 16,
  "avoid_ambiguous": false
}
// Puede incluir: I, l, 1, O, 0, o
```

### Cuándo Usar

| Situación | Recomendación |
|-----------|---------------|
| **Escritura manual** | ✅ `avoid_ambiguous: true` |
| **Copy/paste únicamente** | ⚠️ `avoid_ambiguous: false` |
| **Fuentes sans-serif** | ✅ `avoid_ambiguous: true` |
| **Fuentes monospace** | ⚠️ `avoid_ambiguous: false` |
| **Lectura por teléfono** | ✅ `avoid_ambiguous: true` |
| **Máxima entropía** | ⚠️ `avoid_ambiguous: false` |

---

## Parámetro: require_each (Patrones)

### Especificación

| Aspecto | Valor |
|---------|-------|
| **Tipo** | `boolean` |
| **Por defecto** | `true` |

### Descripción
Cuando está activo (`true`), garantiza que la contraseña contenga **al menos un carácter de cada categoría** que esté habilitada (upper, lower, digits, symbols).

### Comportamiento

```json
// CON require_each: true (por defecto)
{
  "length": 12,
  "upper": true,
  "lower": true,
  "digits": true,
  "symbols": true,
  "require_each": true
}
// Garantizado:
// - Al menos 1 mayúscula
// - Al menos 1 minúscula
// - Al menos 1 número
// - Al menos 1 símbolo
// Ejemplo: "aB3$xY9@zP2#"

// SIN require_each: false
{
  "length": 12,
  "upper": true,
  "lower": true,
  "digits": true,
  "symbols": true,
  "require_each": false
}
// NO garantizado, podría ser:
// "abcdefghijkl" (solo minúsculas, aunque otras estén habilitadas)
```

### Validación Importante

⚠️ **La longitud debe ser suficiente para cumplir el requisito**

```json
// ❌ ERROR: Longitud insuficiente
{
  "length": 3,
  "upper": true,
  "lower": true,
  "digits": true,
  "symbols": true,
  "require_each": true
}
// Error: "La longitud debe ser al menos 4 cuando 'require_each' está activo"
// (Necesita: 1 upper + 1 lower + 1 digit + 1 symbol = 4 mínimo)

// ✅ VÁLIDO: Longitud suficiente
{
  "length": 4,
  "upper": true,
  "lower": true,
  "digits": true,
  "symbols": true,
  "require_each": true
}
```

### Casos de Uso

| Caso | require_each | Razón |
|------|-------------|-------|
| **Políticas corporativas** | `true` | Cumplimiento de requisitos de complejidad |
| **Registros web** | `true` | Forzar contraseñas seguras |
| **Generación rápida** | `false` | Mayor flexibilidad |
| **Longitudes cortas** | `false` | Evitar conflictos de validación |

---

## Endpoint de Configuración

### GET /api/password/config

Obtiene la configuración completa de parámetros y límites de la API.

#### Solicitud

```bash
curl -X GET http://localhost/api/password/config
```

#### Respuesta

```json
{
  "success": true,
  "configuration": {
    "length": {
      "min": 4,
      "max": 128,
      "default": 16,
      "recommended_min": 12,
      "optimal": 16
    },
    "count": {
      "min": 1,
      "max": 100,
      "default": 5
    },
    "exclude": {
      "max_length": 100
    },
    "charsets": {
      "uppercase": "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
      "lowercase": "abcdefghijklmnopqrstuvwxyz",
      "digits": "0123456789",
      "symbols": "!@#$%^&*()-_=+[]{}|;:,.<>?",
      "ambiguous": "Il1O0o"
    },
    "options": {
      "upper": {
        "type": "boolean",
        "default": true,
        "description": "Incluir letras mayúsculas [A-Z]"
      },
      "lower": {
        "type": "boolean",
        "default": true,
        "description": "Incluir letras minúsculas [a-z]"
      },
      "digits": {
        "type": "boolean",
        "default": true,
        "description": "Incluir números [0-9]"
      },
      "symbols": {
        "type": "boolean",
        "default": true,
        "description": "Incluir símbolos especiales"
      },
      "avoid_ambiguous": {
        "type": "boolean",
        "default": true,
        "description": "Evitar caracteres ambiguos (I, l, 1, O, 0, o)"
      },
      "exclude": {
        "type": "string",
        "default": "",
        "max_length": 100,
        "description": "Caracteres específicos a excluir"
      },
      "require_each": {
        "type": "boolean",
        "default": true,
        "description": "Garantizar al menos 1 carácter de cada categoría seleccionada"
      }
    }
  },
  "version": "1.0.0",
  "description": "API de Generación y Validación de Contraseñas Seguras"
}
```

---

## Validaciones y Errores

### Tabla de Errores Comunes

| Error | Código HTTP | Causa | Solución |
|-------|-------------|-------|----------|
| Longitud < 4 | 400 | `length` muy corto | Aumentar `length` a mínimo 4 |
| Longitud > 128 | 400 | `length` muy largo | Reducir `length` a máximo 128 |
| Count < 1 | 400 | `count` inválido | Establecer `count` mínimo en 1 |
| Count > 100 | 400 | `count` excesivo | Reducir `count` a máximo 100 |
| Exclude > 100 chars | 400 | `exclude` muy largo | Reducir caracteres en `exclude` |
| Ninguna categoría | 400 | Todas las opciones en `false` | Activar al menos una categoría |
| Categoría vacía | 400 | Exclusiones eliminan toda categoría | Reducir exclusiones |
| Length insuficiente | 400 | `length` < categorías activas con `require_each` | Aumentar `length` |

### Ejemplo de Respuesta de Error

```json
{
  "success": false,
  "error": "La longitud debe ser >= 4"
}
```

### Validación en el Cliente

Recomendaciones para validar en el cliente antes de enviar la petición:

```javascript
function validatePasswordRequest(params) {
  const errors = [];
  
  // Validar length
  if (params.length < 4 || params.length > 128) {
    errors.push('Longitud debe estar entre 4 y 128');
  }
  
  // Validar count (si existe)
  if (params.count && (params.count < 1 || params.count > 100)) {
    errors.push('Count debe estar entre 1 y 100');
  }
  
  // Validar al menos una categoría
  if (!params.upper && !params.lower && !params.digits && !params.symbols) {
    errors.push('Debe habilitar al menos una categoría');
  }
  
  // Validar exclude
  if (params.exclude && params.exclude.length > 100) {
    errors.push('Exclude no puede exceder 100 caracteres');
  }
  
  return errors;
}
```

---

## Resumen de Límites

| Parámetro | Mínimo | Máximo | Por Defecto |
|-----------|--------|--------|-------------|
| **length** | 4 | 128 | 16 |
| **count** | 1 | 100 | 5 |
| **exclude.length** | 0 | 100 | 0 |
| **upper** | false | true | true |
| **lower** | false | true | true |
| **digits** | false | true | true |
| **symbols** | false | true | true |
| **avoid_ambiguous** | false | true | true |
| **require_each** | false | true | true |

---

## Variables de Entorno

Puedes personalizar los límites usando variables de entorno en tu archivo `.env`:

```env
# Longitud
PASSWORD_LENGTH_MIN=4
PASSWORD_LENGTH_MAX=128
PASSWORD_LENGTH_DEFAULT=16
PASSWORD_LENGTH_RECOMMENDED_MIN=12
PASSWORD_LENGTH_OPTIMAL=16

# Cantidad
PASSWORD_COUNT_MIN=1
PASSWORD_COUNT_MAX=100
PASSWORD_COUNT_DEFAULT=5

# Exclusiones
PASSWORD_EXCLUDE_MAX_LENGTH=100

# Defaults
PASSWORD_DEFAULT_UPPER=true
PASSWORD_DEFAULT_LOWER=true
PASSWORD_DEFAULT_DIGITS=true
PASSWORD_DEFAULT_SYMBOLS=true
PASSWORD_DEFAULT_AVOID_AMBIGUOUS=true
PASSWORD_DEFAULT_REQUIRE_EACH=true
```

---

**Versión:** 1.0.0  
**Última actualización:** Febrero 2026
