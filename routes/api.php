<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordController;

/**
 * Rutas de la API de generación de contraseñas
 * 
 * Todas las rutas tienen el prefijo /api automáticamente
 */

// Ruta para generar una contraseña
Route::post('/password/generate', [PasswordController::class, 'generate']);

// Ruta para generar múltiples contraseñas
Route::post('/password/generate-multiple', [PasswordController::class, 'generateMultiple']);
