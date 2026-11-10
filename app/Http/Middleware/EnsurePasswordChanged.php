<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->is_active && $user->requiresPasswordChange()
            && ! $request->routeIs('password.first-login', 'password.first-login.update', 'logout')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Altere sua senha inicial para continuar.',
                    'redirect' => route('password.first-login'),
                ], 403);
            }

            return redirect()->route('password.first-login');
        }

        return $next($request);
    }
}
