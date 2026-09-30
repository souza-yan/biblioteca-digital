<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $allowed = collect($roles)->map(fn ($r) => Role::from($r));

        if (! $user || ! $allowed->contains($user->role)) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        return $next($request);
    }
}
