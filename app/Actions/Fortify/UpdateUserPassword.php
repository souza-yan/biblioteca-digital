<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        Validator::make(
            $input,
            [
                'current_password' => ['required', 'string', 'current_password:web'],
                'password' => [
                    ...$this->passwordRules($user),
                    ...(! $user->isAdmin() ? [Rule::notIn([config('accounts.default_password')])] : []),
                ],
            ],
            [
                'current_password.current_password' => __('A senha atual informada está incorreta.'),
                'password.different' => 'A nova senha deve ser diferente da senha atual.',
                'password.not_in' => 'Escolha uma senha diferente da senha inicial do sistema.',
            ],
        )->validateWithBag('updatePassword');

        $user
            ->forceFill([
                'password' => Hash::make($input['password']),
                'must_change_password' => false,
            ])
            ->save();
    }
}
