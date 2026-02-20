<?php

namespace App\Http\Controllers;

use App\Services\PasswordService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

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
     * @param Request $request
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $length = $request->input('length', 16);
            
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

            return response()->json([
                'success' => true,
                'password' => $password,
                'length' => strlen($password),
                'options' => $opts
            ], 200);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al generar la contraseña'
            ], 500);
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
     * @param Request $request
     * @return JsonResponse
     */
    public function generateMultiple(Request $request): JsonResponse
    {
        try {
            $count = $request->input('count', 5);
            $length = $request->input('length', 16);
            
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

            return response()->json([
                'success' => true,
                'passwords' => $passwords,
                'count' => count($passwords),
                'length' => $length,
                'options' => $opts
            ], 200);

        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al generar las contraseñas'
            ], 500);
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
     * @param Request $request
     * @return JsonResponse
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $password = $request->input('password');

            // Validar que se envió una contraseña
            if (empty($password)) {
                return response()->json([
                    'success' => false,
                    'error' => 'El campo "password" es requerido'
                ], 400);
            }

            // Validar que sea un string
            if (!is_string($password)) {
                return response()->json([
                    'success' => false,
                    'error' => 'El campo "password" debe ser una cadena de texto'
                ], 400);
            }

            $validation = $this->passwordService->validate($password);

            return response()->json([
                'success' => true,
                'data' => $validation
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al validar la contraseña'
            ], 500);
        }
    }
}
