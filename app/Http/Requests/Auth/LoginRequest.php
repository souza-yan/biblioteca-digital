<?php

namespace App\Http\Requests\Auth;

use App\Rules\Cpf;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['login' => self::identifier($this)]);
    }

    public static function identifier(Request $request): string
    {
        $value = $request->input('login');

        if ($value === null && ! config('accounts.cpf_login_enabled')) {
            $value = $request->input('email');
        }

        if (! is_string($value)) {
            return '';
        }

        return str_contains($value, '@')
            ? mb_strtolower(trim($value))
            : (string) Cpf::normalize($value);
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes'],
        ];
    }
}
