<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class MinimumPasswordStrength implements ValidationRule
{
    public const MESSAGE = 'A senha não é forte o suficiente. Escolha outra senha de força média ou forte, com pelo menos 8 caracteres.';

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $score = (int) (mb_strlen($value) >= 8)
            + (int) (preg_match('/[a-z]/', $value) && preg_match('/[A-Z]/', $value))
            + (int) preg_match('/[0-9]/', $value)
            + (int) preg_match('/[^A-Za-z0-9]/', $value);

        if (mb_strlen($value) < 8 || ! preg_match('/[A-Za-z]/', $value) || $score < 2) {
            $fail(self::MESSAGE);
        }
    }
}
