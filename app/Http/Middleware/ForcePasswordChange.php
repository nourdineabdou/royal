<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Tant que le mot de passe provisoire n'est pas changé, seule la page « Mon mot de passe » est accessible. */
class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && !$request->routeIs('password.edit', 'password.change', 'account.username', 'logout')) {
            if ($request->expectsJson()) {
                abort(403, 'Vous devez d\'abord changer votre mot de passe.');
            }

            return redirect()->route('password.edit');
        }

        return $next($request);
    }
}
