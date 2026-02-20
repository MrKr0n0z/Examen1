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
}
