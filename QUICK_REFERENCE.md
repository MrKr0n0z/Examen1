# 📌 Resumen Rápido - API de Contraseñas

## 🎯 Endpoints Disponibles

### 0. **GET** `/api/password/config` 🆕
Obtiene la configuración de parámetros y límites

**Respuesta:**
```json
{
  "success": true,
  "configuration": {
    "length": {"min": 4, "max": 128, "default": 16},
    "count": {"min": 1, "max": 100, "default": 5},
    ...
  }
}
```

---

### 1. **POST** `/api/password/generate`
Genera una contraseña segura

**Body mínimo:**
```json
{
  "length": 16
}
```

**Respuesta:**
```json
{
  "success": true,
  "password": "aB3$xY9@zP2#qM5!"
}
```

---

### 2. **POST** `/api/password/generate-multiple`
Genera múltiples contraseñas

**Body mínimo:**
```json
{
  "count": 5,
  "length": 16
}
```

**Respuesta:**
```json
{
  "success": true,
  "passwords": ["pass1", "pass2", "pass3", "pass4", "pass5"],
  "count": 5
}
```

---

### 3. **POST** `/api/password/validate`
Valida la fortaleza de una contraseña

**Body:**
```json
{
  "password": "MyP@ssw0rd123!"
}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "is_valid": true,
    "strength": "fuerte",
    "score": 75,
    "recommendations": []
  }
}
```

---

## ⚙️ Parámetros Comunes

| Parámetro | Tipo | Default | Mín | Máx | Descripción |
|-----------|------|---------|-----|-----|-------------|
| `length` | int | 16 | 4 | 128 | Longitud de la contraseña |
| `count` | int | 5 | 1 | 100 | Número de contraseñas (solo multiple) |
| `upper` | bool | true | - | - | Incluir mayúsculas (A-Z) |
| `lower` | bool | true | - | - | Incluir minúsculas (a-z) |
| `digits` | bool | true | - | - | Incluir números (0-9) |
| `symbols` | bool | true | - | - | Incluir símbolos (!@#$...) |
| `avoid_ambiguous` | bool | true | - | - | Evitar (I,l,1,O,0,o) |
| `exclude` | string | "" | 0 | 100 | Caracteres a excluir |
| `require_each` | bool | true | - | - | Garantizar 1 de cada tipo |

---

## 📊 Niveles de Fortaleza

| Score | Nivel | Color |
|-------|-------|-------|
| 80-100 | 🟢 Muy Fuerte | Verde |
| 60-79 | 🔵 Fuerte | Azul |
| 40-59 | 🟡 Moderada | Amarillo |
| 20-39 | 🟠 Débil | Naranja |
| 0-19 | 🔴 Muy Débil | Rojo |

---

## 🚀 Prueba Rápida con cURL

```bash
# Generar contraseña
curl -X POST http://localhost/api/password/generate \
  -H "Content-Type: application/json" \
  -d '{"length": 16}'

# Validar contraseña
curl -X POST http://localhost/api/password/validate \
  -H "Content-Type: application/json" \
  -d '{"password": "Test123!@#"}'
```

---

## 📝 Códigos HTTP

- **200** ✅ Éxito
- **400** ❌ Error en parámetros
- **500** ⚠️ Error del servidor

---

Para más detalles, consulta [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
