<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use App\Http\Responses\ApiResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registrar middleware de rate limiting para rutas de password
        $middleware->alias([
            'password.ratelimit' => \App\Http\Middleware\RateLimitPasswordGeneration::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Manejo global de excepciones para la API
        
        // Errores de validación (422)
        $exceptions->render(function (ValidationException $e) {
            return ApiResponse::validationError(
                $e->errors(),
                'Los datos proporcionados no son válidos'
            );
        });
        
        // Ruta no encontrada (404)
        $exceptions->render(function (NotFoundHttpException $e) {
            return ApiResponse::notFound('El endpoint solicitado no existe');
        });
        
        // Método HTTP no permitido (405)
        $exceptions->render(function (MethodNotAllowedHttpException $e) {
            return ApiResponse::error('Método HTTP no permitido', 405);
        });
        
        // Errores generales (500)
        $exceptions->render(function (\Throwable $e) {
            // Solo mostrar detalles en modo debug
            if (config('app.debug')) {
                return ApiResponse::serverError(
                    'Error interno del servidor',
                    $e
                );
            }
            
            return ApiResponse::serverError('Error interno del servidor');
        });
    })->create();
