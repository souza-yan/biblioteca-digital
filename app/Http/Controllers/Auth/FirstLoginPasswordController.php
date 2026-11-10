<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\SetFirstLoginPassword;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FirstLoginPasswordController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        if (! $request->user()->requiresPasswordChange()) {
            return redirect()->route('dashboard');
        }

        return view('auth.first-login-password');
    }

    public function update(Request $request, SetFirstLoginPassword $setFirstLoginPassword): RedirectResponse
    {
        $setFirstLoginPassword->handle($request->user(), $request->only('password', 'password_confirmation'));
        $request->user()->refresh();
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Senha alterada com sucesso.');
    }
}
