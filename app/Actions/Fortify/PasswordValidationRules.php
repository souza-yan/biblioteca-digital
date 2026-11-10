<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Rules\DifferentFromCurrentPassword;
use App\Rules\MinimumPasswordStrength;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array<int, Rule|ValidationRule|array<mixed>|string>
     */
    protected function passwordRules(User $user): array
    {
        return ['required', 'string', Password::default(), new MinimumPasswordStrength, new DifferentFromCurrentPassword($user), 'confirmed'];
    }
}
