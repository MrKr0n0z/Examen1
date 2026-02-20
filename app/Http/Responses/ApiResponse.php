<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Respuesta de éxito estándar.
     *
     * @param mixed $data Datos a retornar
     * @param string|null $message Mensaje opcional
     * @param int $code Código HTTP (por defecto 200)
     * @return JsonResponse
     */
    public static function success($data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            if (is_array($data)) {
                $response = array_merge($response, $data);
            } else {
                $response['data'] = $data;
            }
        }

        return response()->json($response, $code);
    }

    /**
     * Respuesta de error estándar.
     *
     * @param string $error Mensaje de error
     * @param int $code Código HTTP (por defecto 400)
     * @param array $details Detalles adicionales del error
     * @return JsonResponse
     */
    public static function error(string $error, int $code = 400, array $details = []): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $error,
        ];

        if (!empty($details)) {
            $response['details'] = $details;
        }

        return response()->json($response, $code);
    }

    /**
     * Respuesta de error de validación.
     *
     * @param array $errors Errores de validación
     * @param string $message Mensaje principal
     * @return JsonResponse
     */
    public static function validationError(array $errors, string $message = 'Error de validación'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $message,
            'validation_errors' => $errors,
        ], 422);
    }

    /**
     * Respuesta de error de autenticación.
     *
     * @param string $message Mensaje de error
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'No autorizado'): JsonResponse
    {
        return self::error($message, 401);
    }

    /**
     * Respuesta de error de recurso no encontrado.
     *
     * @param string $message Mensaje de error
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Recurso no encontrado'): JsonResponse
    {
        return self::error($message, 404);
    }

    /**
     * Respuesta de error del servidor.
     *
     * @param string $message Mensaje de error
     * @param \Throwable|null $exception Excepción original (solo en debug)
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Error interno del servidor', ?\Throwable $exception = null): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $message,
        ];

        // Solo incluir detalles de la excepción en modo debug
        if ($exception && config('app.debug')) {
            $response['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->take(5)->toArray(),
            ];
        }

        return response()->json($response, 500);
    }

    /**
     * Respuesta de límite de tasa excedido.
     *
     * @param string $message Mensaje de error
     * @param int $retryAfter Segundos para reintentar
     * @return JsonResponse
     */
    public static function rateLimitExceeded(string $message = 'Límite de peticiones excedido', int $retryAfter = 60): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $message,
            'retry_after' => $retryAfter,
        ], 429);
    }
}
