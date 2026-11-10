<?php

namespace App\Http\Responses;

use App\Models\User;
use Laravel\Fortify\Http\Responses\LoginResponse as FortifyLoginResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse extends FortifyLoginResponse
{
    public function toResponse(mixed $request): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->requiresPasswordChange()) {
            return $request->wantsJson()
                ? response()->json(['two_factor' => false, 'redirect' => route('password.first-login')])
                : redirect()->route('password.first-login');
        }

        return parent::toResponse($request);
    }
}
