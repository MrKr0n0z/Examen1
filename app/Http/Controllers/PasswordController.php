<?php

namespace App\Http\Controllers;

use App\Services\PasswordService;
use App\Http\Requests\GeneratePasswordRequest;
use App\Http\Requests\GenerateMultiplePasswordsRequest;
use App\Http\Requests\ValidatePasswordRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class PasswordController extends Controller
{
    /**
     * @var PasswordService
     */
    private PasswordService $passwordService;

    /**
     * Constructor del controlador.
     *
     * @param PasswordService $passwordService
     */
    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    /**
     * Genera una contraseña segura.
     *
     * POST /api/password/generate
     * 
     * Body (JSON):
     * {
     *   "length": 16,
     *   "upper": true,
     *   "lower": true,
     *   "digits": true,
     *   "symbols": true,
     *   "avoid_ambiguous": true,
     *   "exclude": "abAB12",
     *   "require_each": true
     * }
     *
     * @param GeneratePasswordRequest $request
     * @return JsonResponse
     */
    public function generate(GeneratePasswordRequest $request): JsonResponse
    {
        try {
            $length = $request->input('length', PasswordService::LENGTH_DEFAULT);
            
            $opts = [
                'upper' => $request->input('upper', true),
                'lower' => $request->input('lower', true),
                'digits' => $request->input('digits', true),
                'symbols' => $request->input('symbols', true),
                'avoid_ambiguous' => $request->input('avoid_ambiguous', true),
                'exclude' => $request->input('exclude', ''),
                'require_each' => $request->input('require_each', true),
            ];

            $password = $this->passwordService->generate($length, $opts);

            return ApiResponse::success([
                'password' => $password,
                'length' => strlen($password),
                'options' => $opts
            ]);

        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Error al generar contraseña: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::serverError('Error al generar la contraseña', $e);
        }
    }

    /**
     * Genera múltiples contraseñas seguras.
     *
     * POST /api/password/generate-multiple
     * 
     * Body (JSON):
     * {
     *   "count": 5,
     *   "length": 16,
     *   "upper": true,
     *   "lower": true,
     *   "digits": true,
     *   "symbols": true,
     *   "avoid_ambiguous": true,
     *   "exclude": "",
     *   "require_each": true
     * }
     *
     * @param GenerateMultiplePasswordsRequest $request
     * @return JsonResponse
     */
    public function generateMultiple(GenerateMultiplePasswordsRequest $request): JsonResponse
    {
        try {
            $count = $request->input('count', PasswordService::COUNT_DEFAULT);
            $length = $request->input('length', PasswordService::LENGTH_DEFAULT);
            
            $opts = [
                'upper' => $request->input('upper', true),
                'lower' => $request->input('lower', true),
                'digits' => $request->input('digits', true),
                'symbols' => $request->input('symbols', true),
                'avoid_ambiguous' => $request->input('avoid_ambiguous', true),
                'exclude' => $request->input('exclude', ''),
                'require_each' => $request->input('require_each', true),
            ];

            $passwords = $this->passwordService->generateMany($count, $length, $opts);

            return ApiResponse::success([
                'passwords' => $passwords,
                'count' => count($passwords),
                'length' => $length,
                'options' => $opts
            ]);

        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Error al generar múltiples contraseñas: ' . $e->getMessage(), [
                'count' => $request->input('count'),
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::serverError('Error al generar las contraseñas', $e);
        }
    }

    /**
     * Valida la fortaleza de una contraseña.
     *
     * POST /api/password/validate
     * 
     * Body (JSON):
     * {
     *   "password": "MyP@ssw0rd123!"
     * }
     *
     * @param ValidatePasswordRequest $request
     * @return JsonResponse
     */
    public function validate(ValidatePasswordRequest $request): JsonResponse
    {
        try {
            $password = $request->validated()['password'];
            $validation = $this->passwordService->validate($password);

            return ApiResponse::success(['data' => $validation]);

        } catch (\Exception $e) {
            Log::error('Error al validar contraseña: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::serverError('Error al validar la contraseña', $e);
        }
    }

    /**
     * Obtiene la configuración de parámetros y límites de la API.
     *
     * GET /api/password/config
     *
     * @return JsonResponse
     */
    public function getConfiguration(): JsonResponse
    {
        try {
            $config = $this->passwordService->getConfiguration();

            return ApiResponse::success([
                'configuration' => $config,
                'version' => '1.0.0',
                'description' => 'API de Generación y Validación de Contraseñas Seguras'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener configuración: ' . $e->getMessage());
            return ApiResponse::serverError('Error al obtener la configuración', $e);
        }
    }

    /**
     * Genera una contraseña usando query parameters (GET).
     * 
     * Caso de uso 1:
     * GET /api/password?length=12&includeUppercase=true&includeLowercase=true&includeNumbers=true
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function generateWithQueryParams(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            // Validar manualmente los parámetros GET
            $length = (int) $request->query('length', PasswordService::LENGTH_DEFAULT);
            
            // Validaciones básicas
            if ($length < PasswordService::LENGTH_MIN) {
                return ApiResponse::error('La longitud debe ser >= ' . PasswordService::LENGTH_MIN, 400);
            }
            
            if ($length > PasswordService::LENGTH_MAX) {
                return ApiResponse::error('La longitud debe ser <= ' . PasswordService::LENGTH_MAX, 400);
            }
            
            // Mapear nombres de parámetros del caso de uso a los internos
            $opts = [
                'upper' => filter_var($request->query('includeUppercase', 'true'), FILTER_VALIDATE_BOOLEAN),
                'lower' => filter_var($request->query('includeLowercase', 'true'), FILTER_VALIDATE_BOOLEAN),
                'digits' => filter_var($request->query('includeNumbers', 'true'), FILTER_VALIDATE_BOOLEAN),
                'symbols' => filter_var($request->query('includeSymbols', 'false'), FILTER_VALIDATE_BOOLEAN),
                'avoid_ambiguous' => filter_var($request->query('excludeAmbiguous', 'true'), FILTER_VALIDATE_BOOLEAN),
                'exclude' => $request->query('exclude', ''),
                'require_each' => filter_var($request->query('requireEach', 'true'), FILTER_VALIDATE_BOOLEAN),
            ];

            // Validar que al menos una categoría esté activa
            if (!$opts['upper'] && !$opts['lower'] && !$opts['digits'] && !$opts['symbols']) {
                return ApiResponse::error('Debe incluir al menos un tipo de carácter', 400);
            }

            $password = $this->passwordService->generate($length, $opts);

            return ApiResponse::success([
                'password' => $password,
                'length' => strlen($password),
                'options' => $opts
            ]);

        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Error al generar contraseña (GET): ' . $e->getMessage());
            return ApiResponse::serverError('Error al generar la contraseña', $e);
        }
    }

    /**
     * Genera múltiples contraseñas (endpoint alternativo para caso de uso 2).
     * 
     * POST /api/passwords
     * {
     *   "count": 5,
     *   "length": 16,
     *   "includeSymbols": true,
     *   "excludeAmbiguous": true
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function generatePasswords(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            // Validar manualmente
            $count = (int) $request->input('count', PasswordService::COUNT_DEFAULT);
            $length = (int) $request->input('length', PasswordService::LENGTH_DEFAULT);
            
            // Validaciones
            if ($count < PasswordService::COUNT_MIN || $count > PasswordService::COUNT_MAX) {
                return ApiResponse::error(
                    'Count debe estar entre ' . PasswordService::COUNT_MIN . ' y ' . PasswordService::COUNT_MAX,
                    400
                );
            }
            
            if ($length < PasswordService::LENGTH_MIN || $length > PasswordService::LENGTH_MAX) {
                return ApiResponse::error(
                    'Length debe estar entre ' . PasswordService::LENGTH_MIN . ' y ' . PasswordService::LENGTH_MAX,
                    400
                );
            }
            
            // Mapear nombres de parámetros
            $opts = [
                'upper' => $request->input('includeUppercase', true),
                'lower' => $request->input('includeLowercase', true),
                'digits' => $request->input('includeNumbers', true),
                'symbols' => $request->input('includeSymbols', false),
                'avoid_ambiguous' => $request->input('excludeAmbiguous', true),
                'exclude' => $request->input('exclude', ''),
                'require_each' => $request->input('requireEach', true),
            ];

            $passwords = $this->passwordService->generateMany($count, $length, $opts);

            return ApiResponse::success([
                'passwords' => $passwords,
                'count' => count($passwords),
                'length' => $length
            ]);

        } catch (InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Error al generar múltiples contraseñas: ' . $e->getMessage());
            return ApiResponse::serverError('Error al generar las contraseñas', $e);
        }
    }

    /**
     * Valida una contraseña contra requisitos específicos (caso de uso 3).
     * 
     * POST /api/password/validate
     * {
     *   "password": "MiContraseña123!",
     *   "requirements": {
     *     "minLength": 8,
     *     "requireUppercase": true,
     *     "requireNumbers": true,
     *     "requireSymbols": true
     *   }
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function validateWithRequirements(\Illuminate\Http\Request $request): JsonResponse
    {
        try {
            $password = $request->input('password');
            
            if (empty($password)) {
                return ApiResponse::error('El campo password es requerido', 400);
            }
            
            // Obtener análisis completo
            $analysis = $this->passwordService->validate($password);
            
            // Si hay requirements específicos, validar contra ellos
            $requirements = $request->input('requirements', []);
            
            if (!empty($requirements)) {
                $meetsRequirements = true;
                $failedRequirements = [];
                
                // Validar minLength
                if (isset($requirements['minLength'])) {
                    if ($analysis['length'] < $requirements['minLength']) {
                        $meetsRequirements = false;
                        $failedRequirements[] = "Longitud mínima de {$requirements['minLength']} caracteres";
                    }
                }
                
                // Validar requireUppercase
                if (isset($requirements['requireUppercase']) && $requirements['requireUppercase']) {
                    if (!$analysis['composition']['has_uppercase']) {
                        $meetsRequirements = false;
                        $failedRequirements[] = 'Debe contener al menos una mayúscula';
                    }
                }
                
                // Validar requireLowercase
                if (isset($requirements['requireLowercase']) && $requirements['requireLowercase']) {
                    if (!$analysis['composition']['has_lowercase']) {
                        $meetsRequirements = false;
                        $failedRequirements[] = 'Debe contener al menos una minúscula';
                    }
                }
                
                // Validar requireNumbers
                if (isset($requirements['requireNumbers']) && $requirements['requireNumbers']) {
                    if (!$analysis['composition']['has_digits']) {
                        $meetsRequirements = false;
                        $failedRequirements[] = 'Debe contener al menos un número';
                    }
                }
                
                // Validar requireSymbols
                if (isset($requirements['requireSymbols']) && $requirements['requireSymbols']) {
                    if (!$analysis['composition']['has_symbols']) {
                        $meetsRequirements = false;
                        $failedRequirements[] = 'Debe contener al menos un símbolo';
                    }
                }
                
                return ApiResponse::success([
                    'valid' => $meetsRequirements,
                    'meetsRequirements' => $meetsRequirements,
                    'failedRequirements' => $failedRequirements,
                    'analysis' => $analysis
                ]);
            }
            
            // Sin requirements específicos, devolver solo el análisis
            return ApiResponse::success(['data' => $analysis]);

        } catch (\Exception $e) {
            Log::error('Error al validar contraseña: ' . $e->getMessage());
            return ApiResponse::serverError('Error al validar la contraseña', $e);
        }
    }
}
