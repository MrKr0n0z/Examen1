<?php

namespace App\Services;

use InvalidArgumentException;

class PasswordService
{
    // Aquí pegas las funciones del profe convertidas a métodos de clase
    
    private function secureRandomInt(int $min, int $max): int 
    {
        return random_int($min, $max);
    }

    private function shuffleSecure(string $str): string 
    {
        $arr = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
        $n = count($arr);
        for ($i = $n - 1; $i > 0; $i--) {
            $j = $this->secureRandomInt(0, $i);
            $tmp = $arr[$i];
            $arr[$i] = $arr[$j];
            $arr[$j] = $tmp;
        }
        return implode('', $arr);
    }

    /**
     * Genera una contraseña segura.
     *
     * @param int $length Longitud deseada (>=1).
     * @param array $opts Opciones:
     *    - upper (bool)  : incluir mayúsculas [A-Z]
     *    - lower (bool)  : incluir minúsculas [a-z]
     *    - digits (bool) : incluir dígitos [0-9]
     *    - symbols (bool): incluir símbolos [!@#$...]
     *    - avoid_ambiguous (bool) : evitar caracteres ambiguos (Il1O0 etc.)
     *    - exclude (string) : caracteres a excluir explícitamente
     *    - require_each (bool) : garantizar al menos 1 carácter de cada categoría seleccionada
     *
     * @return string contraseña
     * @throws InvalidArgumentException
     */
    public function generate(int $length = 16, array $opts = []): string 
    {
        if ($length < 1) {
            throw new InvalidArgumentException("La longitud debe ser >= 1");
        }

        // Opciones por defecto
        $opts = array_merge([
            'upper' => true,
            'lower' => true,
            'digits' => true,
            'symbols' => true,
            'avoid_ambiguous' => true,
            'exclude' => '',
            'require_each' => true,
        ], $opts);

        // Conjuntos de caracteres
        $sets = [];

        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $digits = '0123456789';
        // símbolos comunes; puedes editar según tus políticas
        $symbols = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        // caracteres ambiguos que a veces se evitan
        $ambiguous = 'Il1O0o';

        if ($opts['upper']) $sets['upper'] = $upper;
        if ($opts['lower']) $sets['lower'] = $lower;
        if ($opts['digits']) $sets['digits'] = $digits;
        if ($opts['symbols']) $sets['symbols'] = $symbols;

        if (empty($sets)) {
            throw new InvalidArgumentException("Debe activarse al menos una categoría (upper/lower/digits/symbols).");
        }

        // construir pool total y aplicar exclusiones
        $exclude_chars = $opts['exclude'];
        if ($opts['avoid_ambiguous']) {
            $exclude_chars .= $ambiguous;
        }

        // normalizar exclusions a conjunto único
        $exclude_arr = array_unique(preg_split('//u', $exclude_chars, -1, PREG_SPLIT_NO_EMPTY));
        $exclude_map = array_flip($exclude_arr);

        // filtrar sets
        foreach ($sets as $k => $chars) {
            $arr = preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY);
            $filtered = array_values(array_filter($arr, function($c) use ($exclude_map) {
                return !isset($exclude_map[$c]);
            }));
            if (empty($filtered)) {
                // Si una categoría queda vacía tras exclusiones -> error
                throw new InvalidArgumentException("Después de aplicar exclusiones, la categoría '{$k}' no tiene caracteres disponibles.");
            }
            $sets[$k] = implode('', $filtered);
        }

        // crear pool total concatenado
        $pool = implode('', array_values($sets));
        if ($pool === '') {
            throw new InvalidArgumentException("No hay caracteres disponibles para generar la contraseña (pool vacío).");
        }

        $password_chars = [];

        // Si require_each: garantizar al menos un carácter de cada categoría seleccionada
        if ($opts['require_each']) {
            foreach ($sets as $chars) {
                $idx = $this->secureRandomInt(0, strlen($chars) - 1);
                $password_chars[] = $chars[$idx];
            }
        }

        // Rellenar el resto de la longitud con caracteres del pool
        $needed = $length - count($password_chars);
        for ($i = 0; $i < $needed; $i++) {
            $idx = $this->secureRandomInt(0, strlen($pool) - 1);
            $password_chars[] = $pool[$idx];
        }

        // Mezclar de forma segura y devolver
        $password = implode('', $password_chars);
        $password = $this->shuffleSecure($password);
        return $password;
    }

    public function generateMany(int $count = 5, int $length = 16, array $opts = []): array 
    {
        $passwords = [];
        for ($i = 0; $i < $count; $i++) {
            $passwords[] = $this->generate($length, $opts);
        }
        return $passwords;
    }

    /**
     * Valida y analiza la fortaleza de una contraseña.
     *
     * @param string $password Contraseña a validar
     * @return array Análisis detallado de la contraseña
     */
    public function validate(string $password): array
    {
        $length = strlen($password);
        
        // Análisis de composición
        $hasUpper = preg_match('/[A-Z]/', $password) === 1;
        $hasLower = preg_match('/[a-z]/', $password) === 1;
        $hasDigits = preg_match('/[0-9]/', $password) === 1;
        $hasSymbols = preg_match('/[^A-Za-z0-9]/', $password) === 1;
        
        // Contar caracteres de cada tipo
        $upperCount = preg_match_all('/[A-Z]/', $password);
        $lowerCount = preg_match_all('/[a-z]/', $password);
        $digitCount = preg_match_all('/[0-9]/', $password);
        $symbolCount = preg_match_all('/[^A-Za-z0-9]/', $password);
        
        // Verificar caracteres ambiguos
        $ambiguousChars = ['I', 'l', '1', 'O', '0', 'o'];
        $hasAmbiguous = false;
        foreach ($ambiguousChars as $char) {
            if (strpos($password, $char) !== false) {
                $hasAmbiguous = true;
                break;
            }
        }
        
        // Detectar patrones comunes débiles
        $weakPatterns = [
            'secuencial_numerico' => preg_match('/012|123|234|345|456|567|678|789|890/', $password) === 1,
            'secuencial_alfabetico' => preg_match('/abc|bcd|cde|def|efg|fgh|ghi|hij|ijk|jkl|klm|lmn|mno|nop|opq|pqr|qrs|rst|stu|tuv|uvw|vwx|wxy|xyz/i', $password) === 1,
            'repeticion' => preg_match('/(.)\1{2,}/', $password) === 1,
            'teclado' => preg_match('/qwerty|asdfgh|zxcvbn|12345/i', $password) === 1,
        ];
        
        // Calcular diversidad de caracteres (caracteres únicos)
        $uniqueChars = count(array_unique(str_split($password)));
        $diversity = $length > 0 ? round(($uniqueChars / $length) * 100, 2) : 0;
        
        // Calcular puntuación de fortaleza (0-100)
        $score = 0;
        
        // Longitud (máximo 30 puntos)
        if ($length >= 16) {
            $score += 30;
        } elseif ($length >= 12) {
            $score += 25;
        } elseif ($length >= 8) {
            $score += 15;
        } elseif ($length >= 6) {
            $score += 10;
        } else {
            $score += 5;
        }
        
        // Complejidad de caracteres (máximo 40 puntos)
        $typesUsed = 0;
        if ($hasUpper) {
            $score += 10;
            $typesUsed++;
        }
        if ($hasLower) {
            $score += 10;
            $typesUsed++;
        }
        if ($hasDigits) {
            $score += 10;
            $typesUsed++;
        }
        if ($hasSymbols) {
            $score += 10;
            $typesUsed++;
        }
        
        // Diversidad (máximo 20 puntos)
        if ($diversity >= 90) {
            $score += 20;
        } elseif ($diversity >= 75) {
            $score += 15;
        } elseif ($diversity >= 50) {
            $score += 10;
        } else {
            $score += 5;
        }
        
        // Penalizaciones
        foreach ($weakPatterns as $pattern => $found) {
            if ($found) {
                $score -= 10;
            }
        }
        
        if ($hasAmbiguous) {
            $score -= 5;
        }
        
        // Normalizar score entre 0 y 100
        $score = max(0, min(100, $score));
        
        // Determinar nivel de fortaleza
        if ($score >= 80) {
            $strength = 'muy_fuerte';
            $strengthLabel = 'Muy Fuerte';
        } elseif ($score >= 60) {
            $strength = 'fuerte';
            $strengthLabel = 'Fuerte';
        } elseif ($score >= 40) {
            $strength = 'moderada';
            $strengthLabel = 'Moderada';
        } elseif ($score >= 20) {
            $strength = 'debil';
            $strengthLabel = 'Débil';
        } else {
            $strength = 'muy_debil';
            $strengthLabel = 'Muy Débil';
        }
        
        // Calcular tiempo estimado de crackeo (simplificado)
        // Asumiendo ~1 billón de intentos por segundo con hardware moderno
        $possibleChars = 0;
        if ($hasUpper) $possibleChars += 26;
        if ($hasLower) $possibleChars += 26;
        if ($hasDigits) $possibleChars += 10;
        if ($hasSymbols) $possibleChars += 32;
        
        $combinations = pow($possibleChars, $length);
        $crackTimeSeconds = $combinations / 1000000000000; // 1 trillion attempts/sec
        
        // Convertir a formato legible
        if ($crackTimeSeconds < 1) {
            $crackTime = 'Instantáneo';
        } elseif ($crackTimeSeconds < 60) {
            $crackTime = round($crackTimeSeconds) . ' segundos';
        } elseif ($crackTimeSeconds < 3600) {
            $crackTime = round($crackTimeSeconds / 60) . ' minutos';
        } elseif ($crackTimeSeconds < 86400) {
            $crackTime = round($crackTimeSeconds / 3600) . ' horas';
        } elseif ($crackTimeSeconds < 31536000) {
            $crackTime = round($crackTimeSeconds / 86400) . ' días';
        } elseif ($crackTimeSeconds < 31536000 * 1000) {
            $crackTime = round($crackTimeSeconds / 31536000) . ' años';
        } else {
            $crackTime = 'Millones de años';
        }
        
        // Generar recomendaciones
        $recommendations = [];
        if ($length < 12) {
            $recommendations[] = 'Aumentar la longitud a al menos 12 caracteres';
        }
        if ($typesUsed < 4) {
            if (!$hasUpper) $recommendations[] = 'Agregar letras mayúsculas';
            if (!$hasLower) $recommendations[] = 'Agregar letras minúsculas';
            if (!$hasDigits) $recommendations[] = 'Agregar números';
            if (!$hasSymbols) $recommendations[] = 'Agregar símbolos especiales';
        }
        if ($diversity < 70) {
            $recommendations[] = 'Usar más caracteres diferentes (evitar repeticiones)';
        }
        if ($weakPatterns['secuencial_numerico']) {
            $recommendations[] = 'Evitar secuencias numéricas (123, 456, etc.)';
        }
        if ($weakPatterns['secuencial_alfabetico']) {
            $recommendations[] = 'Evitar secuencias alfabéticas (abc, def, etc.)';
        }
        if ($weakPatterns['repeticion']) {
            $recommendations[] = 'Evitar caracteres repetidos consecutivamente';
        }
        if ($weakPatterns['teclado']) {
            $recommendations[] = 'Evitar patrones de teclado (qwerty, asdfgh, etc.)';
        }
        
        return [
            'is_valid' => $score >= 40, // Consideramos válida si es moderada o mejor
            'strength' => $strength,
            'strength_label' => $strengthLabel,
            'score' => $score,
            'length' => $length,
            'composition' => [
                'has_uppercase' => $hasUpper,
                'has_lowercase' => $hasLower,
                'has_digits' => $hasDigits,
                'has_symbols' => $hasSymbols,
                'uppercase_count' => $upperCount,
                'lowercase_count' => $lowerCount,
                'digit_count' => $digitCount,
                'symbol_count' => $symbolCount,
            ],
            'analysis' => [
                'unique_characters' => $uniqueChars,
                'diversity_percentage' => $diversity,
                'has_ambiguous_chars' => $hasAmbiguous,
                'weak_patterns_detected' => array_keys(array_filter($weakPatterns)),
            ],
            'security' => [
                'estimated_crack_time' => $crackTime,
                'possible_combinations' => $possibleChars > 0 ? number_format($combinations, 0, '.', ',') : '0',
            ],
            'recommendations' => $recommendations,
        ];
    }
}