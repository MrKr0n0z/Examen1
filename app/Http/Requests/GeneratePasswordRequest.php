<?php

namespace App\Http\Requests;

use App\Services\PasswordService;
use Illuminate\Foundation\Http\FormRequest;

class GeneratePasswordRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        return true; // Permitir todas las peticiones (cambiar según necesidades)
    }

    /**
     * Reglas de validación para la petición.
     */
    public function rules(): array
    {
        return [
            'length' => [
                'sometimes',
                'integer',
                'min:' . PasswordService::LENGTH_MIN,
                'max:' . PasswordService::LENGTH_MAX,
            ],
            'upper' => 'sometimes|boolean',
            'lower' => 'sometimes|boolean',
            'digits' => 'sometimes|boolean',
            'symbols' => 'sometimes|boolean',
            'avoid_ambiguous' => 'sometimes|boolean',
            'exclude' => [
                'sometimes',
                'string',
                'max:' . PasswordService::EXCLUDE_MAX_LENGTH,
            ],
            'require_each' => 'sometimes|boolean',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'length.integer' => 'La longitud debe ser un número entero',
            'length.min' => 'La longitud mínima es ' . PasswordService::LENGTH_MIN . ' caracteres',
            'length.max' => 'La longitud máxima es ' . PasswordService::LENGTH_MAX . ' caracteres',
            'upper.boolean' => 'El parámetro "upper" debe ser verdadero o falso',
            'lower.boolean' => 'El parámetro "lower" debe ser verdadero o falso',
            'digits.boolean' => 'El parámetro "digits" debe ser verdadero o falso',
            'symbols.boolean' => 'El parámetro "symbols" debe ser verdadero o falso',
            'avoid_ambiguous.boolean' => 'El parámetro "avoid_ambiguous" debe ser verdadero o falso',
            'exclude.string' => 'El parámetro "exclude" debe ser una cadena de texto',
            'exclude.max' => 'El parámetro "exclude" no puede exceder ' . PasswordService::EXCLUDE_MAX_LENGTH . ' caracteres',
            'require_each.boolean' => 'El parámetro "require_each" debe ser verdadero o falso',
        ];
    }

    /**
     * Validación adicional después de las reglas básicas.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que al menos una categoría esté activa
            $upper = $this->input('upper', true);
            $lower = $this->input('lower', true);
            $digits = $this->input('digits', true);
            $symbols = $this->input('symbols', true);

            if (!$upper && !$lower && !$digits && !$symbols) {
                $validator->errors()->add(
                    'categories',
                    'Debe activarse al menos una categoría (upper, lower, digits, symbols)'
                );
            }

            // Validar que si require_each está activo y hay categorías desactivadas,
            // la longitud sea suficiente
            $requireEach = $this->input('require_each', true);
            if ($requireEach) {
                $activeCategories = 0;
                if ($upper) $activeCategories++;
                if ($lower) $activeCategories++;
                if ($digits) $activeCategories++;
                if ($symbols) $activeCategories++;

                $length = $this->input('length', PasswordService::LENGTH_DEFAULT);
                if ($length < $activeCategories) {
                    $validator->errors()->add(
                        'length',
                        "La longitud debe ser al menos {$activeCategories} cuando 'require_each' está activo (una por cada categoría seleccionada)"
                    );
                }
            }
        });
    }
}
