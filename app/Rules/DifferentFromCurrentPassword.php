<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Translation\PotentiallyTranslatedString;

class DifferentFromCurrentPassword implements ValidationRule
{
    public function __construct(private readonly User $user) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && Hash::check($value, $this->user->getAuthPassword())) {
            $fail('A nova senha deve ser diferente da senha atual.');
        }
    }
}
