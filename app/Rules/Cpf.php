<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Rule;

class Cpf implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid($value)) {
            $fail('Informe um CPF válido.');
        }
    }

    public static function normalize(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/D', $value)
            ? str_replace(['.', '-'], '', $value)
            : $value;
    }

    public static function isValid(mixed $value): bool
    {
        $cpf = self::normalize($value);

        if (! is_string($cpf) || ! preg_match('/^\d{11}$/D', $cpf) || preg_match('/^(\d)\1{10}$/D', $cpf)) {
            return false;
        }

        for ($position = 9; $position < 11; $position++) {
            $sum = 0;
            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $cpf[$index] * ($position + 1 - $index);
            }
            $digit = ($sum * 10) % 11;
            if ((int) $cpf[$position] !== ($digit === 10 ? 0 : $digit)) {
                return false;
            }
        }

        return true;
    }

    /** @return array<int, mixed> */
    public static function rules(?int $userId = null, bool $required = true): array
    {
        return ['bail', $required ? 'required' : 'nullable', 'string', new self, Rule::unique('users', 'cpf')->ignore($userId)];
    }

    /** @return array<string, string> */
    public static function messages(string $field = 'cpf'): array
    {
        return [
            $field.'.required' => 'O CPF é obrigatório.',
            $field.'.string' => 'Informe um CPF válido.',
            $field.'.unique' => 'Não foi possível salvar o CPF informado.',
        ];
    }
}
