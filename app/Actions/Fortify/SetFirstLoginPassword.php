<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SetFirstLoginPassword
{
    use PasswordValidationRules;

    /** @param array<string, mixed> $input */
    public function handle(User $user, array $input): User
    {
        return DB::transaction(function () use ($user, $input): User {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            abort_unless($lockedUser->is_active && $lockedUser->requiresPasswordChange(), 403);

            $validator = Validator::make($input, [
                'password' => [...$this->passwordRules($lockedUser), Rule::notIn([config('accounts.default_password')])],
            ], ['password.not_in' => 'Escolha uma senha diferente da senha inicial do sistema.']);
            $attributes = $validator->validateWithBag('updatePassword');

            $lockedUser->forceFill([
                'password' => Hash::make($attributes['password']),
                'must_change_password' => false,
            ])->save();

            return $lockedUser;
        });
    }
}
