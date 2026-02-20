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
    // Ruta para generar una contraseña
    Route::post('/password/generate', [PasswordController::class, 'generate'])
        ->name('password.generate');

    // Ruta para generar múltiples contraseñas
    Route::post('/password/generate-multiple', [PasswordController::class, 'generateMultiple'])
        ->name('password.generate-multiple');

    // Ruta para validar la fortaleza de una contraseña
    Route::post('/password/validate', [PasswordController::class, 'validate'])
        ->name('password.validate');
});
