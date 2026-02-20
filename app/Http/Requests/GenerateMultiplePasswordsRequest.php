<?php

namespace App\Http\Requests;

use App\Services\PasswordService;
use Illuminate\Foundation\Http\FormRequest;

class GenerateMultiplePasswordsRequest extends FormRequest
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
            'count' => [
                'sometimes',
                'integer',
                'min:' . PasswordService::COUNT_MIN,
                'max:' . PasswordService::COUNT_MAX,
            ],
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
            'count.integer' => 'El número de contraseñas debe ser un número entero',
            'count.min' => 'Debe generar al menos ' . PasswordService::COUNT_MIN . ' contraseña(s)',
            'count.max' => 'No puede generar más de ' . PasswordService::COUNT_MAX . ' contraseñas a la vez',
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

            // Validar que si require_each está activo, la longitud sea suficiente
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
            
            // Validación de seguridad: Prevenir generación masiva abusiva
            $count = $this->input('count', PasswordService::COUNT_DEFAULT);
            $length = $this->input('length', PasswordService::LENGTH_DEFAULT);
            
            // Prevenir generación de más de 10,000 caracteres en total
            $totalChars = $count * $length;
            if ($totalChars > 10000) {
                $validator->errors()->add(
                    'count',
                    'El total de caracteres a generar (' . $totalChars . ') excede el límite de 10,000'
                );
            }
            
            // Validación de seguridad: Sanitizar exclude
            $exclude = $this->input('exclude', '');
            if (!empty($exclude) && !$this->isSafeString($exclude)) {
                $validator->errors()->add(
                    'exclude',
                    'El parámetro exclude contiene caracteres no permitidos'
                );
            }
        });
    }
    
    /**
     * Verifica que una cadena sea segura (solo caracteres imprimibles).
     */
    private function isSafeString(string $str): bool
    {
        return preg_match('/^[\x20-\x7E]*$/', $str) === 1;
    }
}
