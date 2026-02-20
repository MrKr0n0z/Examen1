<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordController;

/**
 * Rutas de la API de generación de contraseñas
 * 
 * Todas las rutas tienen el prefijo /api automáticamente
 */

// Ruta para obtener la configuración de parámetros (sin rate limit)
Route::get('/password/config', [PasswordController::class, 'getConfiguration']);

// Rutas con rate limiting para prevenir abuso
Route::middleware('password.ratelimit')->group(function () {
    // === Rutas originales (mantener compatibilidad) ===
    // Ruta para generar una contraseña (POST)
    Route::post('/password/generate', [PasswordController::class, 'generate'])
        ->name('password.generate');

    // Ruta para generar múltiples contraseñas (POST)
    Route::post('/password/generate-multiple', [PasswordController::class, 'generateMultiple'])
        ->name('password.generate-multiple');

    // Ruta para validar la fortaleza de una contraseña (original)
    Route::post('/password/validate-strength', [PasswordController::class, 'validate'])
        ->name('password.validate-strength');

    // === Rutas para casos de uso específicos ===
    // Caso 1: GET /api/password?length=12&includeUppercase=true...
    Route::get('/password', [PasswordController::class, 'generateWithQueryParams'])
        ->name('password.generate-get');

    // Caso 2: POST /api/passwords (generar múltiples)
    Route::post('/passwords', [PasswordController::class, 'generatePasswords'])
        ->name('passwords.generate');

    // Caso 3: POST /api/password/validate (con requirements)
    Route::post('/password/validate', [PasswordController::class, 'validateWithRequirements'])
        ->name('password.validate');
});
