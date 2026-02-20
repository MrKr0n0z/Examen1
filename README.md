<p align="center">
  <h1 align="center">🔐 Password Generation & Validation API</h1>
  <p align="center">API segura para generación y validación de contraseñas con entropía criptográfica</p>
</p>

## 📚 Documentación

- **[API Documentation](API_DOCUMENTATION.md)** - Documentación completa de endpoints con ejemplos
- **[Parameters Specification](PARAMETERS_SPECIFICATION.md)** - Especificación detallada de parámetros y límites
- **[Security Documentation](SECURITY.md)** - Guía de seguridad y validaciones
- **[Quick Reference](QUICK_REFERENCE.md)** - Referencia rápida de la API
- **[API Tests](api-tests.http)** - Colección de pruebas HTTP para REST Client

## 🚀 Características

- ✅ Generación de contraseñas con entropía criptográfica (`random_int()`)
- ✅ Validación y análisis de fortaleza de contraseñas
- ✅ Fisher-Yates shuffle para mezcla segura de caracteres
- ✅ Parámetros personalizables (longitud, tipos de caracteres, exclusiones)
- ✅ Detección de patrones débiles (secuencias, repeticiones, teclado)
- ✅ Validaciones robustas con Form Requests
- ✅ Configuración flexible mediante variables de entorno
- ✅ API RESTful con respuestas JSON

## 📡 Endpoints

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/password/config` | Obtener configuración de parámetros |
| `POST` | `/api/password/generate` | Generar una contraseña |
| `POST` | `/api/password/generate-multiple` | Generar múltiples contraseñas |
| `POST` | `/api/password/validate` | Validar fortaleza de contraseña |

## 🎯 Especificación de Parámetros

### Longitud (length)
- **Mínimo:** 4 caracteres
- **Máximo:** 128 caracteres
- **Por defecto:** 16 caracteres
- **Recomendado:** 12+ caracteres

### Cantidad (count)
- **Mínimo:** 1 contraseña
- **Máximo:** 100 contraseñas
- **Por defecto:** 5 contraseñas

### Tipos de Caracteres
- **upper** (boolean): Mayúsculas A-Z
- **lower** (boolean): Minúsculas a-z
- **digits** (boolean): Números 0-9
- **symbols** (boolean): Símbolos especiales !@#$%^&*...

### Exclusiones
- **exclude** (string): Caracteres a excluir (máx. 100)
- **avoid_ambiguous** (boolean): Evitar I, l, 1, O, 0, o

### Patrones
- **require_each** (boolean): Garantizar al menos 1 carácter de cada categoría activa

## 💻 Instalación y Uso

### Requisitos
- PHP 8.1+
- Laravel 11.x
- Composer

### Instalación

```bash
# Clonar repositorio
git clone <repository-url>
cd password-api

# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Iniciar servidor
php artisan serve
```

### Ejemplo de Uso

```bash
# Obtener configuración
curl http://localhost:8000/api/password/config

# Generar contraseña
curl -X POST http://localhost:8000/api/password/generate \
  -H "Content-Type: application/json" \
  -d '{"length": 20, "symbols": true}'

# Validar contraseña
curl -X POST http://localhost:8000/api/password/validate \
  -H "Content-Type: application/json" \
  -d '{"password": "MyP@ssw0rd2024!"}'
```

## 🔒 Seguridad

- **Entropía criptográfica:** Usa `random_int()` de PHP 7+
- **Mezcla segura:** Implementa Fisher-Yates shuffle
- **Validaciones robustas:** Form Requests con sanitización
- **Rate limiting:** 60 requests/minuto, 500 contraseñas/minuto por IP
- **Límites estrictos:** Prevención de abuso con límites configurables
- **Manejo de errores:** Respuestas consistentes sin exponer detalles
- **Logging:** Registro de errores para auditoría (sin guardar contraseñas)
- **Sin almacenamiento:** No se guardan las contraseñas generadas

### Límites de Seguridad

| Parámetro | Límite | Razón |
|-----------|--------|-------|
| length | 4-128 | Balance entre usabilidad y prevención de abuso |
| count | 1-100 | Prevención de DoS |
| exclude | 0-100 chars | Límite razonable para exclusiones |
| Total chars | 10,000/request | Protección de memoria y CPU |
| Rate limit | 60/min/IP | Anti-abuso |
| Passwords | 500/min/IP | Límite de generación masiva |

Ver [SECURITY.md](SECURITY.md) para detalles completos.

## ⚙️ Configuración

Personaliza los límites en tu archivo `.env`:

```env
PASSWORD_LENGTH_MIN=4
PASSWORD_LENGTH_MAX=128
PASSWORD_LENGTH_DEFAULT=16
PASSWORD_COUNT_MAX=100
PASSWORD_EXCLUDE_MAX_LENGTH=100
```

Ver [config/password.php](config/password.php) para todas las opciones.

## 📊 Validación de Contraseñas

El endpoint de validación analiza:
- ✅ Fortaleza (score 0-100)
- ✅ Composición (mayúsculas, minúsculas, dígitos, símbolos)
- ✅ Diversidad de caracteres
- ✅ Patrones débiles detectados
- ✅ Tiempo estimado de crackeo
- ✅ Recomendaciones de mejora

### Niveles de Fortaleza

| Score | Nivel | Etiqueta |
|-------|-------|----------|
| 80-100 | `muy_fuerte` | 🟢 Muy Fuerte |
| 60-79 | `fuerte` | 🔵 Fuerte |
| 40-59 | `moderada` | 🟡 Moderada |
| 20-39 | `debil` | 🟠 Débil |
| 0-19 | `muy_debil` | 🔴 Muy Débil |

## 🧪 Pruebas

Usa el archivo [api-tests.http](api-tests.http) con la extensión REST Client de VS Code para probar todos los endpoints.

```bash
# O con PHPUnit
php artisan test
```

## 📝 Licencia

Este proyecto usa Laravel, que es un framework open-source bajo la licencia MIT.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor, abre un issue o pull request para mejoras o correcciones.

---

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

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

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
