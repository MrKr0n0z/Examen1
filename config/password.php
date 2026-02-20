<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de Generación de Contraseñas
    |--------------------------------------------------------------------------
    |
    | Esta configuración define los límites y parámetros por defecto para
    | la generación y validación de contraseñas en la API.
    |
    */

    'length' => [
        // Longitud mínima permitida para contraseñas
        'min' => env('PASSWORD_LENGTH_MIN', 4),
        
        // Longitud máxima permitida para contraseñas
        'max' => env('PASSWORD_LENGTH_MAX', 128),
        
        // Longitud por defecto
        'default' => env('PASSWORD_LENGTH_DEFAULT', 16),
        
        // Longitud mínima recomendada para seguridad
        'recommended_min' => env('PASSWORD_LENGTH_RECOMMENDED_MIN', 12),
        
        // Longitud óptima para máxima seguridad
        'optimal' => env('PASSWORD_LENGTH_OPTIMAL', 16),
    ],

    'count' => [
        // Número mínimo de contraseñas a generar
        'min' => env('PASSWORD_COUNT_MIN', 1),
        
        // Número máximo de contraseñas a generar (para prevenir abuso)
        'max' => env('PASSWORD_COUNT_MAX', 100),
        
        // Número por defecto de contraseñas a generar
        'default' => env('PASSWORD_COUNT_DEFAULT', 5),
    ],

    'exclude' => [
        // Longitud máxima permitida para el parámetro exclude
        'max_length' => env('PASSWORD_EXCLUDE_MAX_LENGTH', 100),
    ],

    'charsets' => [
        // Letras mayúsculas disponibles
        'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        
        // Letras minúsculas disponibles
        'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
        
        // Dígitos disponibles
        'digits' => '0123456789',
        
        // Símbolos especiales disponibles
        'symbols' => '!@#$%^&*()-_=+[]{}|;:,.<>?',
        
        // Caracteres ambiguos que pueden confundirse
        'ambiguous' => 'Il1O0o',
    ],

    'defaults' => [
        // Incluir mayúsculas por defecto
        'upper' => env('PASSWORD_DEFAULT_UPPER', true),
        
        // Incluir minúsculas por defecto
        'lower' => env('PASSWORD_DEFAULT_LOWER', true),
        
        // Incluir dígitos por defecto
        'digits' => env('PASSWORD_DEFAULT_DIGITS', true),
        
        // Incluir símbolos por defecto
        'symbols' => env('PASSWORD_DEFAULT_SYMBOLS', true),
        
        // Evitar caracteres ambiguos por defecto
        'avoid_ambiguous' => env('PASSWORD_DEFAULT_AVOID_AMBIGUOUS', true),
        
        // Garantizar al menos un carácter de cada categoría por defecto
        'require_each' => env('PASSWORD_DEFAULT_REQUIRE_EACH', true),
    ],

    'validation' => [
        // Score mínimo para considerar una contraseña válida (0-100)
        'min_valid_score' => env('PASSWORD_VALIDATION_MIN_SCORE', 40),
        
        // Score recomendado para contraseñas (0-100)
        'recommended_score' => env('PASSWORD_VALIDATION_RECOMMENDED_SCORE', 60),
        
        // Score ideal para contraseñas (0-100)
        'ideal_score' => env('PASSWORD_VALIDATION_IDEAL_SCORE', 80),
        
        // Longitud máxima de contraseña a validar (prevención DoS)
        'max_password_length' => env('PASSWORD_VALIDATION_MAX_LENGTH', 1000),
    ],

    'security' => [
        // Algoritmo de generación de números aleatorios
        'random_algorithm' => 'random_int', // PHP 7+ criptográficamente seguro
        
        // Método de mezcla de caracteres
        'shuffle_method' => 'fisher_yates', // Fisher-Yates shuffle seguro
        
        // Habilitar/deshabilitar rate limiting (requiere configuración adicional)
        'rate_limiting' => env('PASSWORD_API_RATE_LIMITING', false),
        
        // Requests por minuto permitidos (si rate limiting está habilitado)
        'rate_limit_per_minute' => env('PASSWORD_API_RATE_LIMIT', 60),
    ],

    'api' => [
        // Versión de la API
        'version' => '1.0.0',
        
        // Nombre de la API
        'name' => 'Password Generation & Validation API',
        
        // Descripción
        'description' => 'API segura para generación y validación de contraseñas con entropía criptográfica',
        
        // Prefijo de rutas (definido en routes/api.php)
        'prefix' => 'api',
        
        // Habilitar CORS
        'cors_enabled' => env('PASSWORD_API_CORS', true),
    ],

];
