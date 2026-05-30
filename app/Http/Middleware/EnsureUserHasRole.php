<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Ensure the authenticated user holds one of the given roles.
     *
     * Usage: ->middleware('role:admin') or ->middleware('role:admin,customer').
     * Role names are validated against {@see RoleEnum} so typos fail fast
     * instead of silently granting or denying access.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        foreach ($roles as $role) {
            if ($user->hasRole(RoleEnum::from($role))) {
                return $next($request);
            }
        }

        abort(Response::HTTP_FORBIDDEN);
    }
}

