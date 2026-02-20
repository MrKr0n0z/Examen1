<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitPasswordGeneration
{
    /**
     * Límite de peticiones por minuto por IP
     */
    private const RATE_LIMIT_PER_MINUTE = 60;

    /**
     * Límite de contraseñas generadas por minuto por IP
     */
    private const PASSWORD_GENERATION_LIMIT = 500;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $now = now();
        $minute = $now->format('Y-m-d-H-i');

        // Clave para contar requests
        $requestKey = "password_api_requests:{$ip}:{$minute}";
        $requestCount = Cache::get($requestKey, 0);

        // Verificar límite de requests
        if ($requestCount >= self::RATE_LIMIT_PER_MINUTE) {
            return response()->json([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'message' => 'Demasiadas peticiones. Límite: ' . self::RATE_LIMIT_PER_MINUTE . ' por minuto.',
                'retry_after' => 60
            ], 429);
        }

        // Si es generación múltiple, verificar límite de contraseñas
        if ($request->routeIs('password.generate-multiple')) {
            $count = $request->input('count', 5);
            
            // Clave para contar contraseñas generadas
            $passwordKey = "password_api_generated:{$ip}:{$minute}";
            $passwordCount = Cache::get($passwordKey, 0);

            if (($passwordCount + $count) > self::PASSWORD_GENERATION_LIMIT) {
                return response()->json([
                    'success' => false,
                    'error' => 'Password generation limit exceeded',
                    'message' => 'Límite de generación de contraseñas excedido. Máximo: ' . self::PASSWORD_GENERATION_LIMIT . ' por minuto.',
                    'retry_after' => 60
                ], 429);
            }

            // Incrementar contador de contraseñas
            Cache::put($passwordKey, $passwordCount + $count, 60);
        }

        // Incrementar contador de requests
        Cache::put($requestKey, $requestCount + 1, 60);

        return $next($request);
    }
}
