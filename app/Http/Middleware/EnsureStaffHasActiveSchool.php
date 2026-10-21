<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffHasActiveSchool
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            ! $user instanceof User || ! $user->isStaff() || $user->hasExactlyOneActiveSchool(),
            403,
            'Sua conta de gestão precisa estar vinculada a exatamente uma escola.',
        );

        return $next($request);
    }
}
