<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidatePasswordRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la petición.
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'max:1000', // Límite razonable para evitar ataques DoS
            ],
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'password.required' => 'El campo "password" es requerido',
            'password.string' => 'El campo "password" debe ser una cadena de texto',
            'password.max' => 'La contraseña a validar no puede exceder 1000 caracteres',
        ];
    }
}
